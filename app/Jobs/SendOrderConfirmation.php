<?php

namespace App\Jobs;

use App\Mail\OrderConfirmationMail;
use App\Models\Tenant\Order;
use App\Models\Tenant\Setting;
use App\Models\Tenant;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Mail;

class SendOrderConfirmation implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public function __construct(
        public Order $order,
        public Tenant $tenant,
    ) {}

    public function handle(): void
    {
        tenancy()->initialize($this->tenant);

        $settings = [
            'store_name'  => Setting::get('store_name', 'SimpleOrder'),
            'order_email' => Setting::get('order_email'),
            'store_address' => Setting::get('store_address'),
        ];

        $order = $this->order->load(['items.options.values']);

        // Email to customer
        Mail::to($order->contact_email, $order->contactName())
            ->send(new OrderConfirmationMail($order, $this->tenant, $settings));

        // Email to store owner/manager
        if ($settings['order_email']) {
            Mail::to($settings['order_email'])
                ->send(new OrderConfirmationMail($order, $this->tenant, $settings));
        }
    }
}
