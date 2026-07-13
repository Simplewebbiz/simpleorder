<?php

namespace App\Models;

use Stancl\Tenancy\Database\Models\Tenant as BaseTenant;
use Stancl\Tenancy\Contracts\TenantWithDatabase;
use Stancl\Tenancy\Database\Concerns\HasDatabase;
use Stancl\Tenancy\Database\Concerns\HasDomains;
use Laravel\Cashier\Billable;

class Tenant extends BaseTenant implements TenantWithDatabase
{
    use HasDatabase, HasDomains, Billable;

    protected $fillable = [
        'id',
        'name',
        'plan_id',
        'stripe_connect_id',
        'stripe_connect_access_token',
        'stripe_connect_active',
        'stripe_customer_id',
        'stripe_id',
        'pm_type',
        'pm_last_four',
        'stripe_subscription_id',
        'subscription_status',
        'trial_ends_at',
        'subscription_ends_at',
        'is_active',
        'data',
    ];

    protected $casts = [
        'data' => 'array',
        'stripe_connect_active' => 'boolean',
        'is_active' => 'boolean',
        'trial_ends_at' => 'datetime',
        'subscription_ends_at' => 'datetime',
    ];

    public static function getCustomColumns(): array
    {
        return [
            'id',
            'name',
            'plan_id',
            'stripe_connect_id',
            'stripe_connect_access_token',
            'stripe_connect_active',
            'stripe_customer_id',
            'stripe_id',
            'pm_type',
            'pm_last_four',
            'stripe_subscription_id',
            'subscription_status',
            'trial_ends_at',
            'subscription_ends_at',
            'is_active',
        ];
    }

    public function getInternal($key)
    {
        $value = parent::getInternal($key);

        if ($value === '') {
            $value = null;
        }

        if (in_array($key, ['tenancy_db_connection', 'db_connection'], true) && $value === null) {
            return 'tenant';
        }

        return $value;
    }

    public function plan()
    {
        return $this->belongsTo(Plan::class);
    }

    public function isSubscriptionActive(): bool
    {
        return in_array($this->subscription_status, ['active', 'trialing']);
    }

    public function isPastDue(): bool
    {
        return $this->subscription_status === 'past_due';
    }

    public function stripeKey(): ?string
    {
        return config('cashier.key');
    }

    public function stripeOptions(): array
    {
        return ['stripe_account' => null]; // platform billing, not connect
    }
}

