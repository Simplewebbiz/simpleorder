<?php

namespace App\Jobs;

use App\Models\Tenant;
use App\Models\Tenant\Order;
use App\Models\Tenant\Setting;
use App\Services\SmsService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class SendOrderStatusSms implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public function __construct(
        public Order $order,
        public Tenant $tenant,
    ) {}

    public function handle(SmsService $sms): void
    {
        tenancy()->initialize($this->tenant);

        try {
            $phone = $this->order->contact_phone;
            if (! $phone) {
                return;
            }

            $storeName = Setting::get('store_name', 'SimpleOrder');
            $reviewUrl = Setting::get('review_url');
            $reviewEnabled = (bool) Setting::get('review_request_enabled', true);
            $orderNum = '#' . $this->order->increment_id;

            $message = match ($this->order->status) {
                'received' => "{$storeName}: Your order {$orderNum} has been received and we're preparing it now.",
                'ready' => $this->order->method === 'delivery'
                    ? "{$storeName}: Great news! Order {$orderNum} is packed and on its way to you."
                    : "{$storeName}: Your order {$orderNum} is ready for pickup. See you soon!",
                'complete' => $reviewEnabled && $reviewUrl
                    ? "{$storeName}: Thanks for your order {$orderNum}! If you enjoyed it, please leave us a review: {$reviewUrl}"
                    : "{$storeName}: Order {$orderNum} is complete. Thanks for your order!",
                'cancelled' => "{$storeName}: Unfortunately, order {$orderNum} has been cancelled. Please contact us with any questions.",
                default => null,
            };

            if ($message) {
                $sms->send($phone, $message);
            }
        } finally {
            tenancy()->end();
        }
    }
}