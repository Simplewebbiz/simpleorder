<?php

namespace App\Models\Tenant;

use Illuminate\Database\Eloquent\Model;

class CartItem extends Model
{
    protected $fillable = ['cart_id', 'item_id', 'qty', 'comments'];

    public function item()
    {
        return $this->belongsTo(Item::class);
    }

    public function options()
    {
        return $this->hasMany(CartItemOption::class);
    }
}
