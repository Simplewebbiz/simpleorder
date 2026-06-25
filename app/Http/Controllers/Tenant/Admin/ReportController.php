<?php

namespace App\Http\Controllers\Tenant\Admin;

use App\Http\Controllers\Controller;
use App\Models\Tenant\Order;
use Illuminate\Http\Request;
use Inertia\Inertia;

class ReportController extends Controller
{
    public function index(Request $request)
    {
        $from = $request->get('from', now()->startOfMonth()->toDateString());
        $to   = $request->get('to', now()->toDateString());

        $orders = Order::forDateRange($from, $to)
            ->whereNotIn('status', ['cancelled'])
            ->with('items')
            ->get();

        // Summary stats
        $summary = [
            'order_count'    => $orders->count(),
            'total_revenue'  => $orders->sum('total'),
            'total_subtotal' => $orders->sum('subtotal'),
            'total_tax'      => $orders->sum('tax'),
            'total_delivery' => $orders->sum('delivery'),
            'total_tips'     => $orders->sum('tip'),
            'total_discount' => $orders->sum('discount'),
            'avg_order'      => $orders->count() ? round($orders->sum('total') / $orders->count(), 2) : 0,
            'pickup_count'   => $orders->where('method', 'pickup')->count(),
            'delivery_count' => $orders->where('method', 'delivery')->count(),
        ];

        // Revenue by day for chart
        $byDay = $orders->groupBy(fn($o) => $o->created_at->toDateString())
            ->map(fn($group) => [
                'date'    => $group->first()->created_at->toDateString(),
                'revenue' => round($group->sum('total'), 2),
                'orders'  => $group->count(),
            ])->values();

        // Top items
        $itemTotals = [];
        foreach ($orders as $order) {
            foreach ($order->items as $item) {
                $key = $item->name;
                $itemTotals[$key] = ($itemTotals[$key] ?? 0) + $item->qty;
            }
        }
        arsort($itemTotals);
        $topItems = collect($itemTotals)->take(10)->map(fn($qty, $name) => compact('name', 'qty'))->values();

        return Inertia::render('Admin/Reports/Index', [
            'summary'  => $summary,
            'by_day'   => $byDay,
            'top_items' => $topItems,
            'filters'  => ['from' => $from, 'to' => $to],
        ]);
    }

    public function export(Request $request)
    {
        $from = $request->get('from', now()->startOfMonth()->toDateString());
        $to   = $request->get('to', now()->toDateString());

        $orders = Order::forDateRange($from, $to)
            ->with('items.options.values')
            ->orderBy('created_at')
            ->get();

        $filename = 'orders-' . $from . '-to-' . $to . '.csv';

        $headers = [
            'Content-Type'        => 'text/csv',
            'Content-Disposition' => 'attachment; filename="' . $filename . '"',
        ];

        $callback = function () use ($orders) {
            $handle = fopen('php://output', 'w');

            fputcsv($handle, [
                'Order #', 'Date', 'Status', 'Method',
                'Customer Name', 'Email', 'Phone',
                'Items', 'Subtotal', 'Tax', 'Delivery', 'Tip', 'Discount', 'Coupon', 'Total',
                'Card', 'Notes',
            ]);

            foreach ($orders as $order) {
                $itemsList = $order->items->map(fn($i) => $i->qty . 'x ' . $i->name)->implode('; ');
                fputcsv($handle, [
                    $order->increment_id,
                    $order->created_at->format('Y-m-d H:i'),
                    $order->status,
                    $order->method,
                    $order->contactName(),
                    $order->contact_email,
                    $order->contact_phone,
                    $itemsList,
                    number_format($order->subtotal, 2),
                    number_format($order->tax, 2),
                    number_format($order->delivery, 2),
                    number_format($order->tip, 2),
                    number_format($order->discount, 2),
                    $order->coupon_code,
                    number_format($order->total, 2),
                    strtoupper($order->card_brand ?? '') . ' ****' . $order->card_last4,
                    $order->comments,
                ]);
            }

            fclose($handle);
        };

        return response()->stream($callback, 200, $headers);
    }
}
