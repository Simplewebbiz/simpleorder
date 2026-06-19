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
        return Inertia::render('Platform/SuperAdmin/Settings', [
            'tenants' => Tenant::with('plan')->latest()->get(),
            'plans' => Plan::all(),
        ]);
    }
}
