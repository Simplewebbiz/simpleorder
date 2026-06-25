<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Cache;

class PlatformSetting extends Model
{
    protected $primaryKey = 'key';
    public $incrementing  = false;
    protected $keyType    = 'string';

    protected $fillable = ['key', 'value'];

    // Cache TTL in seconds
    protected const CACHE_TTL = 300;

    /**
     * Retrieve a platform setting, falling back to an env value if not set in DB.
     */
    public static function get(string $key, mixed $default = null): mixed
    {
        $cached = Cache::remember("platform_setting:{$key}", self::CACHE_TTL, function () use ($key) {
            return static::where('key', $key)->value('value') ?? '__NOT_SET__';
        });

        return $cached === '__NOT_SET__' ? $default : $cached;
    }

    /**
     * Persist a platform setting and bust its cache entry.
     */
    public static function set(string $key, mixed $value): void
    {
        static::updateOrCreate(['key' => $key], ['value' => $value]);
        Cache::forget("platform_setting:{$key}");
    }

    /**
     * Bulk-set many keys at once (used by the super-admin settings save).
     */
    public static function setMany(array $map): void
    {
        foreach ($map as $key => $value) {
            static::set($key, $value);
        }
    }

    /**
     * Apply database-backed platform settings to runtime config.
     */
    public static function applyToConfig(): void
    {
        try {
            if ($key = static::get('stripe_key', env('STRIPE_KEY'))) {
                config(['cashier.key' => $key, 'stripe.key' => $key]);
            }

            if ($secret = static::get('stripe_secret', env('STRIPE_SECRET'))) {
                config(['cashier.secret' => $secret, 'stripe.secret' => $secret]);
            }

            if ($webhookSecret = static::get('stripe_webhook_secret', env('STRIPE_WEBHOOK_SECRET'))) {
                config(['cashier.webhook.secret' => $webhookSecret, 'stripe.webhook_secret' => $webhookSecret]);
            }

            if ($fee = static::get('stripe_platform_fee', env('STRIPE_PLATFORM_FEE', '0.02'))) {
                config(['stripe.platform_fee' => (float) $fee]);
            }

            if ($apiKey = static::get('resend_api_key', env('RESEND_API_KEY'))) {
                config(['resend.api_key' => $apiKey]);
            }

            if ($from = static::get('mail_from_address', env('MAIL_FROM_ADDRESS'))) {
                config(['mail.from.address' => $from]);
            }

            if ($name = static::get('mail_from_name', env('MAIL_FROM_NAME', 'SimpleOrder'))) {
                config(['mail.from.name' => $name]);
            }

            if ($mapsKey = static::get('google_maps_api_key', env('GOOGLE_MAPS_API_KEY'))) {
                config(['services.google_maps.key' => $mapsKey]);
            }

            config([
                'services.twilio.enabled' => filter_var(static::get('twilio_enabled', env('TWILIO_ENABLED', false)), FILTER_VALIDATE_BOOLEAN),
                'services.twilio.sid' => static::get('twilio_sid', env('TWILIO_SID')),
                'services.twilio.token' => static::get('twilio_token', env('TWILIO_TOKEN')),
                'services.twilio.from' => static::get('twilio_from', env('TWILIO_FROM')),
            ]);
        } catch (\Throwable) {
            // The settings table may not exist during install, migrations, or first deploy.
        }
    }
}
