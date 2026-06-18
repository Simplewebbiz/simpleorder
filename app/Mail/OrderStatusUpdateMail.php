<?php

namespace App\Mail;

use App\Models\Tenant\Order;
use App\Models\Tenant;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class OrderStatusUpdateMail extends Mailable
{
    use Queueable, SerializesModels;

    private array $subjectMap = [
        'received'  => 'We received your order!',
        'ready'     => 'Your order is ready!',
        'complete'  => 'Order complete — thank you!',
        'cancelled' => 'Your order has been cancelled',
    ];

    public function __construct(
        public Order $order,
        public Tenant $tenant,
        public array $settings,
    ) {}

    public function build()
    {
        $subject = ($this->subjectMap[$this->order->status] ?? 'Order Update')
            . ' — Order #' . $this->order->increment_id;

        return $this
            ->subject($subject)
            ->from(config('mail.from.address'), $this->settings['store_name'])
            ->view('emails.order-status-update')
            ->with([
                'order'      => $this->order,
                'settings'   => $this->settings,
                'tenant'     => $this->tenant,
                'trackingUrl' => 'https://' . $this->tenant->domains->first()?->domain . '/thank-you/' . $this->order->key,
            ]);
    }
}
