<?php

namespace App\Events;

use App\Models\Tenant\Order;
use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class OrderUpdated implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public function __construct(public Order $order) {}

    public function broadcastOn(): Channel
    {
        return new Channel('order.' . $this->order->key);
    }

    public function broadcastAs(): string
    {
        return 'order-updated';
    }

    public function broadcastWith(): array
    {
        return [
            'status'       => $this->order->status,
            'increment_id' => $this->order->increment_id,
        ];
    }
}
