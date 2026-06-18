<?php

namespace App\Models\Tenant;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Category extends Model
{
    use SoftDeletes;

    protected $fillable = ['name', 'slug', 'description', 'image_id', 'sort', 'is_active'];

    protected $casts = ['is_active' => 'boolean'];

    public function image()
    {
        return $this->belongsTo(Media::class, 'image_id');
    }

    public function items()
    {
        return $this->belongsToMany(Item::class, 'category_item')
            ->withPivot('sort')
            ->orderBy('category_item.sort')
            ->where('items.is_active', true)
            ->whereNull('items.deleted_at');
    }
}
