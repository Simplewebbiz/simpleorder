<?php

namespace App\Http\Controllers\Platform\SuperAdmin;

use App\Http\Controllers\Controller;
use App\Models\Tenant;
use App\Models\Plan;
use Inertia\Inertia;

class DashboardController extends Controller
{
    public function index()
    {
        return Inertia::render('Platform/SuperAdmin/Dashboard', [
            'stats' => [
                'total_tenants' => Tenant::count(),
                'active_tenants' => Tenant::where('subscription_status', 'active')->count(),
                'trialing_tenants' => Tenant::where('subscription_status', 'trialing')->count(),
                'total_plans' => Plan::count(),
            ],
            'recent_tenants' => Tenant::with('plan')->latest()->limit(10)->get(),
        ]);
    }
}
