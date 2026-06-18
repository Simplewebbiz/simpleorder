<?php

namespace App\Models\Tenant;

use Illuminate\Database\Eloquent\Model;

class OrderItemOption extends Model
{
    public $timestamps = false;
    protected $fillable = ['order_item_id', 'label'];

    public function values()
    {
        return $this->hasMany(OrderItemOptionValue::class);
    }
}
