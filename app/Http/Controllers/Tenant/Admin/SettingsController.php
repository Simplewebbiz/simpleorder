<?php

namespace App\Http\Controllers\Tenant\Admin;

use App\Http\Controllers\Controller;
use App\Models\Tenant\Setting;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;
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

        $settings['store_hours'] = $this->normalizeStoreHours($settings['store_hours'] ?? Setting::defaults()['store_hours']);

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

        if (! ($data['allow_pickup'] ?? false) && ! ($data['allow_delivery'] ?? false)) {
            throw ValidationException::withMessages([
                'ordering' => ['Turn on pickup or delivery before saving settings.'],
            ]);
        }

        foreach ($data as $key => $value) {
            Setting::set($key, $value);
        }

        return back()->with('success', 'Settings saved.');
    }

    private function normalizeStoreHours(array $hours): array
    {
        foreach ($hours as $day => $range) {
            $hours[$day]['from'] = $this->normalizeTime($range['from'] ?? null) ?? '10:00';
            $hours[$day]['to'] = $this->normalizeTime($range['to'] ?? null) ?? '22:00';
            $hours[$day]['closed'] = (bool) ($range['closed'] ?? false);
        }

        return $hours;
    }

    private function normalizeTime(?string $time): ?string
    {
        if (! $time || ! preg_match('/^(\d{1,2}):(\d{2})\s*(AM|PM)?$/i', trim($time), $matches)) {
            return null;
        }

        $hour = (int) $matches[1];
        $minute = (int) $matches[2];
        $period = strtoupper($matches[3] ?? '');

        if ($hour > 23 || $minute > 59) {
            return null;
        }

        if ($period === 'PM' && $hour !== 12) {
            $hour += 12;
        }

        if ($period === 'AM' && $hour === 12) {
            $hour = 0;
        }

        return sprintf('%02d:%02d', $hour, $minute);
    }

    public function stripe()
    {
        return Inertia::render('Admin/Settings/Stripe', [
            'stripeConnected' => !empty(Setting::get('stripe_connect_id')),
            'connectUrl'      => rtrim(config('app.url'), '/') . '/dashboard/stripe/connect',
        ]);
    }
}
