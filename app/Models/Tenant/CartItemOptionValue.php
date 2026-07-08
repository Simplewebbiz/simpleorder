<?php

namespace App\Models\Tenant;

use Illuminate\Database\Eloquent\Model;

class CartItemOptionValue extends Model
{
    protected $connection = 'tenant';

    public $timestamps = false;

    protected $fillable = ['cart_item_option_id', 'item_option_value_id'];

    public function cartItemOption()
    {
        return $this->belongsTo(CartItemOption::class);
    }

    public function optionValue()
    {
        return $this->belongsTo(ItemOptionValue::class, 'item_option_value_id');
    }
}