<?php

namespace App\Models\Tenant;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;

class Page extends Model
{
    protected $fillable = [
        'name',
        'title',
        'slug',
        'menu_label',
        'summary',
        'content',
        'hero_media_id',
        'is_active',
        'is_published',
        'sort',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'is_published' => 'boolean',
    ];

    public function hero()
    {
        return $this->belongsTo(Media::class, 'hero_media_id');
    }

    public static function seedDefaults(): void
    {
        $defaults = [
            [
                'title' => 'Home',
                'slug' => 'home',
                'menu_label' => 'Home',
                'summary' => 'Welcome guests with your restaurant story, favorite dishes, and ordering details.',
                'content' => '<h2>Welcome to our table</h2><p>Share what makes your kitchen special, from family recipes to fresh local ingredients.</p>',
                'sort' => 1,
            ],
            [
                'title' => 'About Us',
                'slug' => 'about',
                'menu_label' => 'About',
                'summary' => 'Tell customers who you are and why your food matters.',
                'content' => '<h2>Our story</h2><p>Add a warm note about your team, your food, and the experience guests can expect.</p>',
                'sort' => 2,
            ],
            [
                'title' => 'Contact Us',
                'slug' => 'contact',
                'menu_label' => 'Contact',
                'summary' => 'Help customers find you, call you, and ask questions.',
                'content' => '<h2>Come visit us</h2><p>Add your address, phone number, hours, and any notes customers should know.</p>',
                'sort' => 3,
            ],
        ];

        foreach ($defaults as $page) {
            static::updateOrCreate(['slug' => $page['slug']], static::withLegacyColumns($page));
        }
    }

    public static function withLegacyColumns(array $page): array
    {
        if (Schema::hasColumn('pages', 'name')) {
            $page['name'] = $page['title'] ?? $page['name'] ?? $page['slug'];
        }

        if (Schema::hasColumn('pages', 'is_active')) {
            $page['is_active'] = $page['is_published'] ?? true;
        }

        return $page;
    }

    public static function uniqueSlug(string $title, ?int $ignoreId = null): string
    {
        $base = Str::slug($title) ?: 'page';
        $slug = $base;
        $counter = 2;

        while (static::query()
            ->where('slug', $slug)
            ->when($ignoreId, fn ($query) => $query->where('id', '!=', $ignoreId))
            ->exists()) {
            $slug = $base . '-' . $counter++;
        }

        return $slug;
    }
}
