<?php

namespace App\Jobs;

use App\Mail\OrderStatusUpdateMail;
use App\Models\Tenant;
use App\Models\Tenant\Order;
use App\Models\Tenant\Setting;
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

        try {
            $settings = [];
            foreach (Setting::defaults() as $key => $value) {
                $settings[$key] = Setting::get($key, $value);
            }

            Mail::to($this->order->contact_email, $this->order->contactName())
                ->send(new OrderStatusUpdateMail($this->order, $this->tenant, $settings));
        } finally {
            tenancy()->end();
        }
    }
}