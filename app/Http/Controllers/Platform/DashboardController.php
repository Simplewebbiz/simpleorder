<?php

namespace App\Http\Controllers\Platform;

use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;

class DashboardController extends Controller
{
    public function index()
    {
        $user = Auth::guard('platform')->user();
        $tenant = $user->tenant;

        $tenantUrl = $tenant ? $this->tenantUrl($tenant) : null;

        $stats = [
            'total_orders' => 0,
            'total_revenue' => 0,
            'menu_items' => 0,
            'is_stripe_connected' => (bool) ($tenant?->stripe_connect_active),
        ];

        return Inertia::render('Platform/Dashboard/Index', [
            'user' => $user,
            'tenant' => $tenant,
            'stats' => $stats,
            'tenantUrl' => $tenantUrl,
            'tenantAdminUrl' => $tenantUrl ? $tenantUrl . '/admin' : null,
        ]);
    }

    private function tenantUrl($tenant): string
    {
        $domain = $tenant->domains()->where('is_primary', true)->value('domain')
            ?: $tenant->domains()->value('domain')
            ?: $tenant->id . '.' . parse_url(config('app.url'), PHP_URL_HOST);

        $scheme = parse_url(config('app.url'), PHP_URL_SCHEME) ?: 'https';

        return $scheme . '://' . $domain;
    }
}