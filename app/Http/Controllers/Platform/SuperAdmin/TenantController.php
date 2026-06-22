<?php

namespace App\Http\Controllers\Platform\SuperAdmin;

use App\Http\Controllers\Controller;
use App\Models\Tenant;
use Illuminate\Http\Request;
use Inertia\Inertia;

class TenantController extends Controller
{
    public function index()
    {
        return Inertia::render('Platform/SuperAdmin/Tenants', [
            'tenants' => Tenant::with(['plan', 'domains'])->latest()->get(),
        ]);
    }

    public function show(Tenant $tenant)
    {
        tenancy()->initialize($tenant);
        $orders = \App\Models\Tenant\Order::with('items')->latest()->limit(50)->get();
        tenancy()->end();

        return Inertia::render('Platform/SuperAdmin/TenantShow', [
            'tenant' => $tenant->load(['plan', 'domains']),
            'orders' => $orders,
        ]);
    }

    public function destroy(Tenant $tenant)
    {
        $tenant->delete();
        return redirect()->route('platform.superadmin.tenants.index')->with('success', 'Tenant deleted.');
    }

    public function impersonate(Tenant $tenant)
    {
        session(['impersonating_tenant' => $tenant->id]);
        return redirect()->route('dashboard.index');
    }
}
