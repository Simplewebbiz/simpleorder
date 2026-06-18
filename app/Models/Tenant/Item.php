<?php

namespace App\Models\Tenant;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Item extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'name', 'sku', 'description', 'price', 'taxable',
        'type', 'image_id', 'sort', 'is_active',
    ];

    protected $casts = [
        'taxable' => 'boolean',
        'is_active' => 'boolean',
        'price' => 'decimal:2',
    ];

    public function image()
    {
        return $this->belongsTo(Media::class, 'image_id');
    }

    public function categories()
    {
        return $this->belongsToMany(Category::class, 'category_item')
            ->withPivot('sort')
            ->orderBy('category_item.sort');
    }

    public function options()
    {
        return $this->hasMany(ItemOption::class)->orderBy('sort');
    }
}
