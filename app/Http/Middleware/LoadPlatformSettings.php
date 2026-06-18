<?php

namespace App\Http\Middleware;

use App\Models\PlatformSetting;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class LoadPlatformSettings
{
    public function handle(Request $request, Closure $next): Response
    {
        try {
            // Stripe — override config so Cashier picks up DB values
            if ($key = PlatformSetting::get('stripe_key', env('STRIPE_KEY'))) {
                config(['cashier.key' => $key, 'stripe.key' => $key]);
            }
            if ($secret = PlatformSetting::get('stripe_secret', env('STRIPE_SECRET'))) {
                config(['cashier.secret' => $secret, 'stripe.secret' => $secret]);
            }
            if ($whsec = PlatformSetting::get('stripe_webhook_secret', env('STRIPE_WEBHOOK_SECRET'))) {
                config(['cashier.webhook.secret' => $whsec, 'stripe.webhook_secret' => $whsec]);
            }
            if ($fee = PlatformSetting::get('stripe_platform_fee', env('STRIPE_PLATFORM_FEE', '0.02'))) {
                config(['stripe.platform_fee' => (float) $fee]);
            }

            // Resend / Mail
            if ($apiKey = PlatformSetting::get('resend_api_key', env('RESEND_API_KEY'))) {
                config(['resend.api_key' => $apiKey]);
            }
            if ($from = PlatformSetting::get('mail_from_address', env('MAIL_FROM_ADDRESS'))) {
                config(['mail.from.address' => $from]);
            }
            if ($name = PlatformSetting::get('mail_from_name', env('MAIL_FROM_NAME', 'SimpleOrder'))) {
                config(['mail.from.name' => $name]);
            }

            // Google Maps
            if ($mapsKey = PlatformSetting::get('google_maps_api_key', env('GOOGLE_MAPS_API_KEY'))) {
                config(['services.google_maps.key' => $mapsKey]);
            }

            // Twilio
            config([
                'services.twilio.enabled' => (bool) PlatformSetting::get('twilio_enabled', env('TWILIO_ENABLED', false)),
                'services.twilio.sid'     => PlatformSetting::get('twilio_sid', env('TWILIO_SID')),
                'services.twilio.token'   => PlatformSetting::get('twilio_token', env('TWILIO_TOKEN')),
                'services.twilio.from'    => PlatformSetting::get('twilio_from', env('TWILIO_FROM')),
            ]);
        } catch (\Throwable) {
            // DB may not exist yet (during install) — fail silently
        }

        return $next($request);
    }
}
