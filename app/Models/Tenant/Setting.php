<?php

namespace App\Models\Tenant;

use Illuminate\Database\Eloquent\Model;

class Setting extends Model
{
    protected $primaryKey = 'key';
    public $incrementing = false;
    protected $keyType = 'string';

    protected $fillable = ['key', 'value'];

    public static function get(string $key, mixed $default = null): mixed
    {
        $setting = static::find($key);
        if (!$setting) return $default;

        $decoded = json_decode($setting->value, true);
        return json_last_error() === JSON_ERROR_NONE ? $decoded : $setting->value;
    }

    public static function set(string $key, mixed $value): void
    {
        static::updateOrCreate(
            ['key' => $key],
            ['value' => is_string($value) ? $value : json_encode($value)]
        );
    }

    public static function defaults(): array
    {
        return [
            'store_name'       => 'My Store',
            'store_address'    => ['address' => '', 'city' => '', 'state' => '', 'zip' => ''],
            'store_phone'      => '',
            'store_hours'      => [
                'mo' => ['from' => '09:00AM', 'to' => '09:00PM', 'closed' => false],
                'tu' => ['from' => '09:00AM', 'to' => '09:00PM', 'closed' => false],
                'we' => ['from' => '09:00AM', 'to' => '09:00PM', 'closed' => false],
                'th' => ['from' => '09:00AM', 'to' => '09:00PM', 'closed' => false],
                'fr' => ['from' => '09:00AM', 'to' => '09:00PM', 'closed' => false],
                'sa' => ['from' => '10:00AM', 'to' => '08:00PM', 'closed' => false],
                'su' => ['from' => '10:00AM', 'to' => '06:00PM', 'closed' => false],
            ],
            'tax_rate'         => '0',
            'delivery_radius'  => '10',
            'food_charge'      => '0.00',
            'grocery_charge'   => '0.00',
            'order_email'      => '',
            'timezone'         => 'America/Chicago',
            'allow_pickup'     => true,
            'allow_delivery'   => true,
            'stripe_connect_id'          => null,
            'stripe_publishable_key'     => null,
            'stripe_secret_key'          => null,
        ];
    }
}
