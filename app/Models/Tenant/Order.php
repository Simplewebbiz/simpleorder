<?php

namespace App\Models\Tenant;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Order extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'increment_id', 'key', 'user_id', 'cart_id', 'method', 'status',
        'contact_firstname', 'contact_lastname', 'contact_email', 'contact_phone',
        'delivery_address', 'delivery_city', 'delivery_state', 'delivery_zip',
        'billing_firstname', 'billing_lastname', 'billing_address',
        'billing_city', 'billing_state', 'billing_zip',
        'stripe_payment_intent', 'stripe_charge_id', 'card_brand', 'card_last4',
        'subtotal', 'tax', 'delivery', 'tip', 'coupon_code', 'discount', 'total', 'comments',
    ];

    protected $casts = [
        'subtotal' => 'decimal:2',
        'tax' => 'decimal:2',
        'delivery' => 'decimal:2',
        'tip' => 'decimal:2',
        'discount' => 'decimal:2',
        'total' => 'decimal:2',
    ];

    const STATUSES = ['placed', 'received', 'ready', 'complete', 'cancelled'];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function items()
    {
        return $this->hasMany(OrderItem::class);
    }

    public function contactName(): string
    {
        return $this->contact_firstname . ' ' . $this->contact_lastname;
    }

    public function deliveryAddress(): string
    {
        return $this->delivery_address . ', ' . $this->delivery_city . ', ' . $this->delivery_state . ' ' . $this->delivery_zip;
    }

    public function scopeForDateRange($query, ?string $from, ?string $to)
    {
        if ($from) $query->whereDate('created_at', '>=', $from);
        if ($to) $query->whereDate('created_at', '<=', $to);
        return $query;
    }

    public function scopeCompleted($query)
    {
        return $query->whereIn('status', ['complete', 'ready', 'received', 'placed']);
    }
}
