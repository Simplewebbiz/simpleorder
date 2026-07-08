<?php

namespace App\Models\Tenant;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Str;

class Coupon extends Model
{
    use SoftDeletes;

    protected $connection = 'tenant';

    protected $fillable = [
        'name',
        'code',
        'type',
        'value',
        'minimum_subtotal',
        'max_redemptions',
        'used_count',
        'starts_at',
        'ends_at',
        'is_active',
    ];

    protected $casts = [
        'value' => 'decimal:2',
        'minimum_subtotal' => 'decimal:2',
        'starts_at' => 'datetime',
        'ends_at' => 'datetime',
        'is_active' => 'boolean',
    ];

    public static function normalizeCode(?string $code): ?string
    {
        $code = trim((string) $code);
        return $code === '' ? null : Str::upper($code);
    }

    public function isUsableFor(float $subtotal): bool
    {
        if (! $this->is_active) {
            return false;
        }

        if ($this->minimum_subtotal > 0 && $subtotal < (float) $this->minimum_subtotal) {
            return false;
        }

        if ($this->max_redemptions !== null && $this->used_count >= $this->max_redemptions) {
            return false;
        }

        if ($this->starts_at && now()->lt($this->starts_at)) {
            return false;
        }

        if ($this->ends_at && now()->gt($this->ends_at)) {
            return false;
        }

        return true;
    }

    public function discountFor(float $subtotal, float $delivery): float
    {
        $discount = match ($this->type) {
            'percent' => $subtotal * ((float) $this->value / 100),
            'fixed' => (float) $this->value,
            'free_delivery' => $delivery,
            default => 0,
        };

        return round(min(max($discount, 0), $subtotal + $delivery), 2);
    }
}