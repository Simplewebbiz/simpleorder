<?php

namespace App\Http\Controllers\Platform\SuperAdmin;

use App\Http\Controllers\Controller;
use App\Models\PlatformSetting;
use Illuminate\Http\Request;
use Inertia\Inertia;

class PlatformSettingsController extends Controller
{
    // Keys that are sensitive — masked in the response unless empty
    protected const SENSITIVE = [
        'stripe_secret',
        'stripe_webhook_secret',
        'resend_api_key',
        'twilio_token',
    ];

    public function index()
    {
        $keys = [
            'stripe_key', 'stripe_secret', 'stripe_webhook_secret', 'stripe_platform_fee',
            'resend_api_key', 'mail_from_address', 'mail_from_name',
            'google_maps_api_key',
            'twilio_enabled', 'twilio_sid', 'twilio_token', 'twilio_from',
        ];

        $settings = [];
        foreach ($keys as $key) {
            $raw = PlatformSetting::get($key, '');
            // Send a masked placeholder for secrets so the page shows they're set
            $settings[$key] = (in_array($key, self::SENSITIVE) && $raw !== '')
                ? '••••••••'
                : $raw;
        }

        return Inertia::render('Platform/SuperAdmin/Settings', [
            'settings' => $settings,
        ]);
    }

    public function update(Request $request)
    {
        $data = $request->validate([
            'stripe_key'              => 'nullable|string|max:255',
            'stripe_secret'           => 'nullable|string|max:255',
            'stripe_webhook_secret'   => 'nullable|string|max:255',
            'stripe_platform_fee'     => 'nullable|numeric|min:0|max:1',
            'resend_api_key'          => 'nullable|string|max:255',
            'mail_from_address'       => 'nullable|email|max:255',
            'mail_from_name'          => 'nullable|string|max:100',
            'google_maps_api_key'     => 'nullable|string|max:255',
            'twilio_enabled'          => 'boolean',
            'twilio_sid'              => 'nullable|string|max:255',
            'twilio_token'            => 'nullable|string|max:255',
            'twilio_from'             => 'nullable|string|max:20',
        ]);

        // Don't overwrite secrets if they were submitted as the masked placeholder
        foreach (self::SENSITIVE as $key) {
            if (($data[$key] ?? '') === '••••••••') {
                unset($data[$key]);
            }
        }

        PlatformSetting::setMany(array_filter($data, fn($v) => $v !== null && $v !== ''));

        return back()->with('success', 'Platform settings saved.');
    }

    public function testConnection(Request $request)
    {
        $service = $request->validate(['service' => 'required|in:stripe,resend,twilio,google_maps'])['service'];

        $result = match ($service) {
            'stripe'      => $this->testStripe(),
            'resend'      => $this->testResend(),
            'twilio'      => $this->testTwilio($request->string('phone', '')),
            'google_maps' => $this->testGoogleMaps(),
        };

        return response()->json($result);
    }

    // -------------------------------------------------------------------------

    private function testStripe(): array
    {
        try {
            $stripe = new \Stripe\StripeClient(PlatformSetting::get('stripe_secret', env('STRIPE_SECRET')));
            $stripe->accounts->retrieve();
            return ['ok' => true, 'message' => 'Stripe connection successful.'];
        } catch (\Throwable $e) {
            return ['ok' => false, 'message' => 'Stripe error: ' . $e->getMessage()];
        }
    }

    private function testResend(): array
    {
        try {
            $client = \Resend::client(PlatformSetting::get('resend_api_key', env('RESEND_API_KEY')));
            $client->apiKeys()->list();
            return ['ok' => true, 'message' => 'Resend API key is valid.'];
        } catch (\Throwable $e) {
            return ['ok' => false, 'message' => 'Resend error: ' . $e->getMessage()];
        }
    }

    private function testTwilio(string $phone): array
    {
        if (! $phone) {
            return ['ok' => false, 'message' => 'Provide a phone number to test.'];
        }
        try {
            $client = new \Twilio\Rest\Client(
                PlatformSetting::get('twilio_sid', env('TWILIO_SID')),
                PlatformSetting::get('twilio_token', env('TWILIO_TOKEN')),
            );
            $client->messages->create($phone, [
                'from' => PlatformSetting::get('twilio_from', env('TWILIO_FROM')),
                'body' => 'SimpleOrder: Twilio test message. If you see this, SMS is working!',
            ]);
            return ['ok' => true, 'message' => "Test SMS sent to {$phone}."];
        } catch (\Throwable $e) {
            return ['ok' => false, 'message' => 'Twilio error: ' . $e->getMessage()];
        }
    }

    private function testGoogleMaps(): array
    {
        try {
            $key      = PlatformSetting::get('google_maps_api_key', env('GOOGLE_MAPS_API_KEY'));
            $response = \Illuminate\Support\Facades\Http::get('https://maps.googleapis.com/maps/api/geocode/json', [
                'address' => '1600 Amphitheatre Parkway, Mountain View, CA',
                'key'     => $key,
            ]);
            $status = $response->json('status');
            if ($status === 'OK') {
                return ['ok' => true, 'message' => 'Google Maps API key is valid.'];
            }
            return ['ok' => false, 'message' => "Google Maps returned status: {$status}"];
        } catch (\Throwable $e) {
            return ['ok' => false, 'message' => 'Google Maps error: ' . $e->getMessage()];
        }
    }
}
