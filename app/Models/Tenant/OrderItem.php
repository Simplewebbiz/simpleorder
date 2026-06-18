<?php

namespace App\Models\Tenant;

use Illuminate\Database\Eloquent\Model;

class OrderItem extends Model
{
    public $timestamps = false;

    protected $fillable = [
        'order_id', 'item_id', 'name', 'sku', 'taxable', 'type', 'qty', 'price', 'comments',
    ];

    protected $casts = [
        'taxable' => 'boolean',
        'price' => 'decimal:2',
    ];

    public function order()
    {
        return $this->belongsTo(Order::class);
    }

    public function options()
    {
        return $this->hasMany(OrderItemOption::class);
    }

    public function lineTotal(): float
    {
        $base = (float) $this->price;
        $percent = 1.0;
        foreach ($this->options as $option) {
            foreach ($option->values as $value) {
                if ($value->price_type === 'percent') {
                    $percent += (float) $value->price / 100;
                } else {
                    $base += (float) $value->price;
                }
            }
        }
        return round($base * $percent * $this->qty, 2);
    }
}
