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
        return Inertia::render('Platform/SuperAdmin/Settings', [
            'tenants' => Tenant::with(['plan', 'domains'])->latest()->get(),
        ]);
    }

    public function show(Tenant $tenant)
    {
        return Inertia::render('Platform/SuperAdmin/Settings', [
            'tenant' => $tenant->load(['plan', 'domains']),
        ]);
    }

    public function destroy(Tenant $tenant)
    {
        $tenant->delete();
        return redirect()->back()->with('success', 'Tenant deleted.');
    }

    public function impersonate(Tenant $tenant)
    {
        $user = $tenant->users()->first();
        if ($user) {
            auth('platform')->login($user);
        }
        return redirect()->route('dashboard.index');
    }
}
