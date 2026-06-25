<?php

namespace App\Models\Tenant;

use Illuminate\Database\Eloquent\Model;

class CartItemOption extends Model
{
    public $timestamps = false;

    protected $fillable = ['cart_item_id', 'item_option_id'];

    public function cartItem()
    {
        return $this->belongsTo(CartItem::class);
    }

    public function option()
    {
        return $this->belongsTo(ItemOption::class, 'item_option_id');
    }

    public function values()
    {
        return $this->hasMany(CartItemOptionValue::class);
    }
}