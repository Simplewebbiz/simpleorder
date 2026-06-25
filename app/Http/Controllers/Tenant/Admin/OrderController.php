<?php

namespace App\Http\Controllers\Tenant\Admin;

use App\Http\Controllers\Controller;
use App\Models\Tenant\Order;
use App\Events\OrderUpdated;
use App\Jobs\SendOrderStatusSms;
use App\Jobs\SendOrderStatusUpdate;
use Illuminate\Http\Request;
use Inertia\Inertia;

class OrderController extends Controller
{
    public function index(Request $request)
    {
        $query = Order::query()
            ->withCount('items')
            ->orderByDesc('created_at');

        if ($request->status) {
            $query->where('status', $request->status);
        }

        if ($request->search) {
            $query->where(function ($q) use ($request) {
                $q->where('increment_id', $request->search)
                  ->orWhere('contact_email', 'like', '%' . $request->search . '%')
                  ->orWhere('contact_lastname', 'like', '%' . $request->search . '%')
                  ->orWhere('contact_phone', 'like', '%' . $request->search . '%');
            });
        }

        if ($request->date_from) {
            $query->whereDate('created_at', '>=', $request->date_from);
        }

        if ($request->date_to) {
            $query->whereDate('created_at', '<=', $request->date_to);
        }

        $orders = $query->paginate(25)->withQueryString()->through(fn (Order $order) => [
            'id' => $order->id,
            'increment_id' => $order->increment_id,
            'contact_firstname' => $order->contact_firstname,
            'contact_lastname' => $order->contact_lastname,
            'contact_email' => $order->contact_email,
            'contact_phone' => $order->contact_phone,
            'method' => $order->method,
            'status' => $order->status,
            'items_count' => $order->items_count,
            'total' => $order->total,
            'created_at' => $order->created_at,
            'next_status' => $this->nextStatus($order->status),
        ]);

        $statusCounts = Order::query()
            ->selectRaw('status, count(*) as total')
            ->groupBy('status')
            ->pluck('total', 'status');

        return Inertia::render('Admin/Orders/Index', [
            'orders'  => $orders,
            'filters' => $request->only(['status', 'search', 'date_from', 'date_to']),
            'statusCounts' => [
                'placed' => (int) ($statusCounts['placed'] ?? 0),
                'received' => (int) ($statusCounts['received'] ?? 0),
                'ready' => (int) ($statusCounts['ready'] ?? 0),
                'complete' => (int) ($statusCounts['complete'] ?? 0),
                'cancelled' => (int) ($statusCounts['cancelled'] ?? 0),
            ],
            'pendingCount' => (int) (($statusCounts['placed'] ?? 0) + ($statusCounts['received'] ?? 0) + ($statusCounts['ready'] ?? 0)),
        ]);
    }

    public function show(Order $order)
    {
        $order->load(['items.options.values']);

        return Inertia::render('Admin/Orders/Show', [
            'order' => $order,
        ]);
    }

    public function updateStatus(Request $request, Order $order)
    {
        $request->validate([
            'status' => 'required|in:' . implode(',', Order::STATUSES),
        ]);

        $order->update(['status' => $request->status]);

        // Push real-time update to customer
        broadcast(new OrderUpdated($order));

        // Email + SMS customer on key status changes
        if (in_array($request->status, ['received', 'ready', 'complete', 'cancelled'])) {
            SendOrderStatusUpdate::dispatch($order, tenant());
            SendOrderStatusSms::dispatch($order, tenant());
        }

        return back()->with('success', 'Order status updated.');
    }

    private function nextStatus(string $status): ?string
    {
        return match ($status) {
            'placed' => 'received',
            'received' => 'ready',
            'ready' => 'complete',
            default => null,
        };
    }
}
