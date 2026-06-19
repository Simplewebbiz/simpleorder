<?php

namespace App\Http\Controllers\Tenant;

use App\Http\Controllers\Controller;
use App\Models\Tenant\Order;
use Illuminate\Http\Request;
use Inertia\Inertia;

class OrderStatusController extends Controller
{
    public function show(Request $request, string $key)
    {
        $order = Order::where('tracking_key', $key)->firstOrFail();

        return response()->json([
            'order' => $order,
            'status' => $order->status,
        ]);
    }
}
