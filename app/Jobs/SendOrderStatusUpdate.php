<?php

namespace App\Jobs;

use App\Mail\OrderStatusUpdateMail;
use App\Models\Tenant\Order;
use App\Models\Tenant\Setting;
use App\Models\Tenant;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Mail;

class SendOrderStatusUpdate implements ShouldQueue
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
            'store_name' => Setting::get('store_name', 'SimpleOrder'),
            'order_email' => Setting::get('order_email'),
        ];

        Mail::to($this->order->contact_email, $this->order->contactName())
            ->send(new OrderStatusUpdateMail($this->order, $this->tenant, $settings));
    }
}
