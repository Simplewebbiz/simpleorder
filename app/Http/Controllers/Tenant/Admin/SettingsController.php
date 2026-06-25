<?php

namespace App\Http\Controllers\Tenant\Admin;

use App\Http\Controllers\Controller;
use App\Models\Tenant\Setting;
use Illuminate\Http\Request;
use Inertia\Inertia;

class SettingsController extends Controller
{
    public function index()
    {
        $settings = [];
        $keys = array_keys(Setting::defaults());
        foreach ($keys as $key) {
            $settings[$key] = Setting::get($key, Setting::defaults()[$key]);
        }

        return Inertia::render('Admin/Settings/Index', [
            'settings'    => $settings,
            'stripeConnected' => !empty($settings['stripe_connect_id']),
            'connectUrl'  => config('app.url') . '/dashboard/stripe/connect',
        ]);
    }

    public function update(Request $request)
    {
        $data = $request->validate([
            'store_name'       => 'required|string|max:100',
            'store_phone'      => 'nullable|string|max:20',
            'store_address'    => 'required|array',
            'store_address.address' => 'required|string',
            'store_address.city'    => 'required|string',
            'store_address.state'   => 'required|string|size:2',
            'store_address.zip'     => 'required|string|max:10',
            'store_hours'      => 'required|array',
            'tax_rate'         => 'required|numeric|min:0|max:100',
            'delivery_radius'  => 'required|numeric|min:0',
            'food_charge'      => 'required|numeric|min:0',
            'grocery_charge'   => 'required|numeric|min:0',
            'order_email'      => 'nullable|email',
            'review_url'       => 'nullable|url|max:500',
            'review_request_enabled' => 'boolean',
            'review_request_message' => 'nullable|string|max:300',
            'timezone'         => 'required|string',
            'allow_pickup'     => 'boolean',
            'allow_delivery'   => 'boolean',
        ]);

        foreach ($data as $key => $value) {
            Setting::set($key, $value);
        }

        return back()->with('success', 'Settings saved.');
    }

    public function stripe()
    {
        return Inertia::render('Admin/Settings/Stripe', [
            'stripeConnected' => !empty(Setting::get('stripe_connect_id')),
            'connectUrl'      => rtrim(config('app.url'), '/') . '/dashboard/stripe/connect',
        ]);
    }
}
