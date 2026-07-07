<?php

namespace App\Events;

use App\Models\Tenant\Order;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class OrderPlaced implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public function __construct(public Order $order) {}

    public function broadcastOn(): PrivateChannel
    {
        // Broadcast to the admin dashboard channel for this tenant
        return new PrivateChannel('admin.' . tenant()->id . '.orders');
    }

    public function broadcastAs(): string
    {
        return 'order-placed';
    }

    public function broadcastWith(): array
    {
        return [
            'order' => [
                'id' => $this->order->id,
                'increment_id' => $this->order->increment_id,
                'method' => $this->order->method,
                'contact_firstname' => $this->order->contact_firstname,
                'contact_lastname' => $this->order->contact_lastname,
                'total' => (float) $this->order->total,
                'status' => $this->order->status,
            ],
        ];
    }
}
