<?php

namespace App\Http\Controllers\Tenant;

use App\Http\Controllers\Controller;
use App\Models\Tenant\Order;
use App\Services\OrderService;
use Illuminate\Http\Request;

class OrderStatusController extends Controller
{
    public function show(Request $request, string $key, OrderService $orders)
    {
        $order = Order::with('items.options.values')
            ->where('key', $key)
            ->firstOrFail();

        return response()->json($orders->formatForResponse($order));
    }
}
