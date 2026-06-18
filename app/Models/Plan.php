<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Plan extends Model
{
    protected $fillable = [
        'name',
        'slug',
        'description',
        'price_monthly',
        'price_yearly',
        'stripe_monthly_price_id',
        'stripe_yearly_price_id',
        'max_items',
        'max_categories',
        'custom_domain',
        'order_reports',
        'is_active',
        'sort',
    ];

    protected $casts = [
        'custom_domain' => 'boolean',
        'order_reports' => 'boolean',
        'is_active' => 'boolean',
    ];

    public function tenants()
    {
        return $this->hasMany(Tenant::class);
    }

    public function monthlyPriceFormatted(): string
    {
        return '$' . number_format($this->price_monthly / 100, 2);
    }

    public function yearlyPriceFormatted(): string
    {
        return '$' . number_format($this->price_yearly / 100, 2);
    }
}
