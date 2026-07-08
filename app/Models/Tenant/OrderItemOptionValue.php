<?php

namespace App\Models\Tenant;

use Illuminate\Database\Eloquent\Model;

class OrderItemOptionValue extends Model
{
    protected $connection = 'tenant';

    public $timestamps = false;
    protected $fillable = ['order_item_option_id', 'label', 'price', 'price_type'];
    protected $casts = ['price' => 'decimal:2'];
}
