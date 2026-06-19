<?php

namespace App\Http\Controllers\Tenant\Admin;

use App\Http\Controllers\Controller;
use App\Models\Tenant\Order;
use Inertia\Inertia;

class DashboardController extends Controller
{
    public function index()
    {
        $pendingCount = Order::whereIn('status', ['pending', 'confirmed'])->count();
        $todayRevenue = Order::whereDate('created_at', today())->where('status', '!=', 'cancelled')->sum('total');
        $recentOrders = Order::latest()->take(10)->get();

        return Inertia::render('Admin/Dashboard/Index', [
            'pending_count' => $pendingCount,
            'today_revenue' => $todayRevenue,
            'recent_orders' => $recentOrders,
        ]);
    }
}
