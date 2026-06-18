<?php

namespace App\Mail;

use App\Models\Tenant\Order;
use App\Models\Tenant\Setting;
use App\Models\Tenant;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class OrderConfirmationMail extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(
        public Order $order,
        public Tenant $tenant,
        public array $settings,
    ) {}

    public function build()
    {
        return $this
            ->subject('Order #' . $this->order->increment_id . ' Confirmed — ' . $this->settings['store_name'])
            ->from(config('mail.from.address'), $this->settings['store_name'])
            ->replyTo($this->settings['order_email'] ?? config('mail.from.address'))
            ->view('emails.order-confirmation')
            ->with([
                'order'     => $this->order,
                'settings'  => $this->settings,
                'tenant'    => $this->tenant,
                'trackingUrl' => 'https://' . $this->tenant->domains->first()?->domain . '/thank-you/' . $this->order->key,
            ]);
    }
}
