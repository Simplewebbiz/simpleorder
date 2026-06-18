<?php

namespace App\Events;

use App\Models\Tenant\Order;
use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class OrderPlaced implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public function __construct(public Order $order) {}

    public function broadcastOn(): Channel
    {
        // Broadcast to the admin dashboard channel for this tenant
        return new Channel('admin.' . tenant()->id . '.orders');
    }

    public function broadcastAs(): string
    {
        return 'order-placed';
    }

    public function broadcastWith(): array
    {
        return [
            'increment_id'     => $this->order->increment_id,
            'method'           => $this->order->method,
            'contact_lastname' => $this->order->contact_lastname,
            'total'            => $this->order->total,
            'status'           => $this->order->status,
        ];
    }
}
