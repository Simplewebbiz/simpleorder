<?php

namespace App\Models\Tenant;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class ItemOptionValue extends Model
{
    use SoftDeletes;

    protected $connection = 'tenant';

    protected $fillable = ['item_option_id', 'label', 'price', 'price_type', 'sort'];

    protected $casts = ['price' => 'decimal:2'];

    public function option()
    {
        return $this->belongsTo(ItemOption::class, 'item_option_id');
    }
}
