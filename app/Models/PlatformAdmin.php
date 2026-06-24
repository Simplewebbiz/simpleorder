<?php

namespace App\Models;

use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class PlatformAdmin extends Authenticatable
{
    use Notifiable;

    protected $fillable = ['tenant_id', 'name', 'email', 'password', 'is_super'];

    protected $hidden = ['password', 'remember_token'];

    protected $casts = [
        'is_super' => 'boolean',
        'password' => 'hashed',
    ];

    public function tenant()
    {
        return $this->belongsTo(Tenant::class);
    }
}
