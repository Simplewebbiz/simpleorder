<?php

namespace App\Http\Controllers\Platform;

use App\Http\Controllers\Controller;
use App\Models\{PlatformAdmin, Plan, Tenant};
use App\Models\Tenant\{User, Setting};
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Inertia\Inertia;

class AuthController extends Controller
{
    public function showLogin()
    {
        return Inertia::render('Platform/Auth/Login');
    }

    public function login(Request $request)
    {
        $credentials = $request->validate([
            'email'    => 'required|email',
            'password' => 'required',
        ]);

        if (!Auth::guard('platform')->attempt($credentials, $request->boolean('remember'))) {
            return back()->withErrors(['email' => 'Invalid credentials.']);
        }

        $admin = Auth::guard('platform')->user();

        if ($admin->is_super) {
            return redirect()->route('platform.superadmin.index');
        }

        return redirect()->route('dashboard.index');
    }

    public function showRegister()
    {
        $plans = Plan::where('is_active', true)->orderBy('sort')->get();
        return Inertia::render('Platform/Auth/Register', ['plans' => $plans]);
    }

    public function register(Request $request)
    {
        $data = $request->validate([
            'store_name'   => 'required|string|max:100',
            'subdomain'    => 'required|string|max:63|regex:/^[a-z0-9\-]+$/|unique:tenants,id',
            'name'         => 'required|string|max:100',
            'email'        => 'required|email|unique:platform_admins,email',
            'password'     => 'required|min:8|confirmed',
            'plan_id'      => 'required|exists:plans,id',
        ]);

        // Create platform admin user
        $admin = PlatformAdmin::create([
            'name'     => $data['name'],
            'email'    => $data['email'],
            'password' => Hash::make($data['password']),
        ]);

        // Create tenant
        $tenant = Tenant::create([
            'id'        => $data['subdomain'],
            'name'      => $data['store_name'],
            'plan_id'   => $data['plan_id'],
            'subscription_status' => 'trialing',
            'trial_ends_at' => now()->addDays(14),
        ]);

        // Attach subdomain
        $tenant->domains()->create([
            'domain'     => $data['subdomain'] . '.' . parse_url(config('app.url'), PHP_URL_HOST),
            'is_primary' => true,
            'is_custom'  => false,
            'verified'   => true,
        ]);

        // Seed tenant database with defaults
        tenancy()->initialize($tenant);

        User::create([
            'name'     => $data['name'],
            'email'    => $data['email'],
            'password' => Hash::make($data['password']),
            'role'     => 'owner',
        ]);

        foreach (Setting::defaults() as $key => $value) {
            Setting::set($key, $key === 'store_name' ? $data['store_name'] : $value);
        }

        // Seed order_sequences table
        \DB::table('order_sequences')->insert([]);

        tenancy()->end();

        Auth::guard('platform')->login($admin);

        return redirect()->route('dashboard.billing');
    }

    public function logout(Request $request)
    {
        Auth::guard('platform')->logout();
        $request->session()->invalidate();
        return redirect()->route('home');
    }
}
