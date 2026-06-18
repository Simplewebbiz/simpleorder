<?php

namespace App\Models\Tenant;

use Illuminate\Database\Eloquent\Model;

class Media extends Model
{
    protected $fillable = ['name', 'src', 'mime', 'size'];

    public function getPermalinkAttribute(): string
    {
        return '/tenant-media/' . $this->src;
    }

    public function getUrlAttribute(): string
    {
        return $this->permalink;
    }
}
