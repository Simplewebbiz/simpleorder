<?php

namespace App\Http\Controllers\Platform\SuperAdmin;

use App\Http\Controllers\Controller;
use App\Models\Plan;
use App\Models\Tenant;
use App\Models\Tenant\Setting;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Schema;
use Inertia\Inertia;
use Throwable;

class TenantController extends Controller
{
    public function index()
    {
        return Inertia::render('Platform/SuperAdmin/Tenants', [
            'tenants' => Tenant::with(['plan', 'domains'])->latest()->get(),
            'plans'   => Plan::where('is_active', true)->orderBy('sort')->get(),
        ]);
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'store_name' => 'required|string|max:100',
            'subdomain'  => 'required|string|max:63|regex:/^[a-z0-9\-]+$/|unique:tenants,id',
            'name'       => 'required|string|max:100',
            'email'      => 'required|email',
            'password'   => 'required|min:8',
            'plan_id'    => 'required|exists:plans,id',
        ]);

        $tenant = Tenant::create([
            'id'                  => $data['subdomain'],
            'name'                => $data['store_name'],
            'plan_id'             => $data['plan_id'],
            'subscription_status' => 'trialing',
            'trial_ends_at'       => now()->addDays(14),
        ]);

        $tenant->domains()->create([
            'domain'     => $data['subdomain'] . '.' . parse_url(config('app.url'), PHP_URL_HOST),
            'is_primary' => true,
            'is_custom'  => false,
            'verified'   => true,
        ]);

        tenancy()->initialize($tenant);

        \App\Models\Tenant\User::create([
            'name'     => $data['name'],
            'email'    => $data['email'],
            'password' => Hash::make($data['password']),
            'role'     => 'owner',
        ]);

        foreach (Setting::defaults() as $key => $value) {
            Setting::set($key, $key === 'store_name' ? $data['store_name'] : $value);
        }

        tenancy()->end();

        return redirect()->route('platform.superadmin.tenants.index')->with('success', 'Tenant created.');
    }

    public function show(Tenant $tenant)
    {
        $orders = [];
        $tenantHealth = [
            'database_ready' => true,
            'message' => null,
        ];

        try {
            tenancy()->initialize($tenant);

            if (Schema::hasTable('orders')) {
                $orders = \App\Models\Tenant\Order::with('items')
                    ->latest()
                    ->limit(50)
                    ->get()
                    ->map(fn ($order) => [
                        'id' => $order->id,
                        'order_number' => $order->increment_id ?: $order->id,
                        'customer_name' => trim($order->contact_firstname . ' ' . $order->contact_lastname) ?: null,
                        'customer_email' => $order->contact_email,
                        'status' => $order->status,
                        'total' => (float) $order->total,
                        'items' => $order->items->map(fn ($item) => [
                            'id' => $item->id,
                            'name' => $item->name,
                            'qty' => $item->qty,
                        ])->values()->all(),
                        'created_at' => optional($order->created_at)->toISOString(),
                    ])
                    ->values()
                    ->all();
            } else {
                $tenantHealth = [
                    'database_ready' => false,
                    'message' => 'The tenant database exists, but the order tables are not available yet.',
                ];
            }
        } catch (Throwable $e) {
            $tenantHealth = [
                'database_ready' => false,
                'message' => 'The tenant database could not be opened. This usually means the tenant setup did not finish or its database is missing.',
            ];
        } finally {
            try {
                tenancy()->end();
            } catch (Throwable) {
                // Tenancy was not initialized.
            }
        }

        return Inertia::render('Platform/SuperAdmin/TenantShow', [
            'tenant' => $tenant->load(['plan', 'domains']),
            'orders' => $orders,
            'tenantHealth' => $tenantHealth,
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
        return redirect()->route('platform.dashboard');
    }
}

