<?php

namespace App\Models\Tenant;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class ItemOption extends Model
{
    use SoftDeletes;

    protected $fillable = ['item_id', 'label', 'required', 'input_type', 'sort'];

    protected $casts = ['required' => 'boolean'];

    public function item()
    {
        return $this->belongsTo(Item::class);
    }

    public function values()
    {
        return $this->hasMany(ItemOptionValue::class)->orderBy('sort');
    }
}
