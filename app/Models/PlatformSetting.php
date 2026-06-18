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
}
