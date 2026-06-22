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

        // Generate tenant storefront URL
        $tenantUrl = $tenant 
            ? 'https://' . $tenant->slug . '.simpleorder.com' 
            : null;

        // Basic stats (you can expand this later)
        $stats = [
            'total_orders'     => 0,
            'total_revenue'    => 0,
            'menu_items'       => 0,
            'is_stripe_connected' => false,
        ];

        // TODO: Calculate real stats here when you have the relationships set up
        // Example (uncomment when ready):
        // if ($tenant) {
        //     $stats['total_orders'] = $tenant->orders()->count();
        //     $stats['total_revenue'] = $tenant->orders()->sum('total');
        //     $stats['menu_items'] = $tenant->items()->count();
        //     $stats['is_stripe_connected'] = !empty($tenant->stripe_account_id);
        // }

        return Inertia::render('Platform/Dashboard/Index', [
            'user'       => $user,
            'tenant'     => $tenant,
            'stats'      => $stats,
            'tenantUrl'  => $tenantUrl,
        ]);
    }
}
