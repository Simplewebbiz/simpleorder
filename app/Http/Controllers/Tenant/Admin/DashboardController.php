<?php

namespace App\Http\Controllers\Tenant\Admin;

use App\Http\Controllers\Controller;
use App\Models\Tenant\Category;
use App\Models\Tenant\Item;
use App\Models\Tenant\Order;
use App\Models\Tenant\Page;
use App\Models\Tenant\Setting;
use Inertia\Inertia;

class DashboardController extends Controller
{
    public function index()
    {
        $openStatuses = ['placed', 'received', 'ready'];

        $stats = [
            'today_orders' => Order::whereDate('created_at', today())->count(),
            'today_revenue' => (float) Order::whereDate('created_at', today())->where('status', '!=', 'cancelled')->sum('total'),
            'pending' => Order::whereIn('status', $openStatuses)->count(),
            'month_revenue' => (float) Order::whereBetween('created_at', [now()->startOfMonth(), now()->endOfMonth()])->where('status', '!=', 'cancelled')->sum('total'),
        ];

        $recentOrders = Order::latest()->take(10)->get();
        $pendingOrders = Order::whereIn('status', $openStatuses)->latest()->take(10)->get();

        return Inertia::render('Admin/Dashboard', [
            'stats' => $stats,
            'recentOrders' => $recentOrders,
            'pendingOrders' => $pendingOrders,
            'onboarding' => $this->onboardingChecklist(),
        ]);
    }

    private function onboardingChecklist(): array
    {
        $settings = [];
        foreach (Setting::defaults() as $key => $value) {
            $settings[$key] = Setting::get($key, $value);
        }

        $storeAddress = $settings['store_address'] ?? [];

        return [
            [
                'key' => 'store_info',
                'label' => 'Add restaurant contact details',
                'done' => filled($settings['store_name'] ?? null)
                    && filled($settings['store_phone'] ?? null)
                    && filled($settings['order_email'] ?? null)
                    && filled($storeAddress['address'] ?? null),
                'href' => route('tenant.admin.settings.index'),
            ],
            [
                'key' => 'pages',
                'label' => 'Publish website pages',
                'done' => Page::whereIn('slug', ['home', 'about', 'contact'])->where('is_published', true)->count() >= 3,
                'href' => route('tenant.admin.pages.index'),
            ],
            [
                'key' => 'categories',
                'label' => 'Create menu categories',
                'done' => Category::where('is_active', true)->exists(),
                'href' => route('tenant.admin.categories.index'),
            ],
            [
                'key' => 'items',
                'label' => 'Add menu items',
                'done' => Item::where('is_active', true)->exists(),
                'href' => route('tenant.admin.items.index'),
            ],
            [
                'key' => 'payments',
                'label' => 'Connect Stripe payments',
                'done' => filled($settings['stripe_connect_id'] ?? null) || filled($settings['stripe_secret_key'] ?? null),
                'href' => route('tenant.admin.settings.stripe'),
            ],
            [
                'key' => 'reviews',
                'label' => 'Add Google review link',
                'done' => filled($settings['review_url'] ?? null),
                'href' => route('tenant.admin.settings.index'),
            ],
        ];
    }
}
