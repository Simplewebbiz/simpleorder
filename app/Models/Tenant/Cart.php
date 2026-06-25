<?php

namespace App\Models\Tenant;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Cart extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'user_id', 'session_key', 'method',
        'delivery_address', 'delivery_city', 'delivery_state', 'delivery_zip',
        'tip', 'coupon_code', 'stripe_intent',
    ];

    protected $casts = ['tip' => 'decimal:2'];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function items()
    {
        return $this->hasMany(CartItem::class);
    }
}
