<?php

namespace App\Models\Tenant;

use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Database\Eloquent\SoftDeletes;

class User extends Authenticatable
{
    use Notifiable, SoftDeletes;

    protected $fillable = ['name', 'email', 'password', 'role', 'phone'];

    protected $hidden = ['password', 'remember_token'];

    protected $casts = [
        'password' => 'hashed',
        'email_verified_at' => 'datetime',
    ];

    const ROLE_OWNER = 'owner';
    const ROLE_MANAGER = 'manager';
    const ROLE_FULFILLMENT = 'fulfillment';
    const ROLE_CUSTOMER = 'customer';

    public function isOwner(): bool { return $this->role === self::ROLE_OWNER; }
    public function isManager(): bool { return in_array($this->role, [self::ROLE_OWNER, self::ROLE_MANAGER]); }
    public function isFulfillment(): bool { return in_array($this->role, [self::ROLE_OWNER, self::ROLE_MANAGER, self::ROLE_FULFILLMENT]); }
    public function isCustomer(): bool { return $this->role === self::ROLE_CUSTOMER; }

    public function orders()
    {
        return $this->hasMany(Order::class);
    }
}
