<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class MarketingPage extends Model
{
    protected $fillable = [
        'title',
        'slug',
        'nav_label',
        'eyebrow',
        'summary',
        'content',
        'hero_image_url',
        'cta_label',
        'cta_url',
        'is_published',
        'sort',
    ];

    protected $casts = [
        'is_published' => 'boolean',
    ];

    public static function seedDefaults(): void
    {
        $defaults = [
            [
                'title' => 'Beautiful online ordering for restaurants',
                'slug' => 'home',
                'nav_label' => 'Home',
                'eyebrow' => 'Restaurant ordering made lighter',
                'summary' => 'Give every restaurant a clean website, visual menu, online ordering, payments, customer updates, and a dashboard built for daily service.',
                'content' => '<h2>Built for restaurant owners, not software teams.</h2><p>SimpleOrder helps restaurants launch quickly with Home, About, Pricing, Contact, menu, checkout, payments, text updates, email updates, staff users, reports, and a CMS for everyday edits.</p>',
                'hero_image_url' => 'https://images.unsplash.com/photo-1528605248644-14dd04022da1?auto=format&fit=crop&w=1200&q=80',
                'cta_label' => 'Start Your Restaurant Site',
                'cta_url' => '/register',
                'sort' => 1,
            ],
            [
                'title' => 'About SimpleOrder',
                'slug' => 'about',
                'nav_label' => 'About Us',
                'eyebrow' => 'About SimpleOrder',
                'summary' => 'We help restaurant owners sell online with a website that feels polished, practical, and easy to run.',
                'content' => '<h2>Restaurants deserve tools that feel calm and clear.</h2><p>SimpleOrder brings the public website, menu, customer ordering, payment setup, notifications, reports, and staff workflows into one place.</p><h3>Made for real service days</h3><p>Owners can update content, staff can manage orders, and guests can order without confusion.</p>',
                'hero_image_url' => 'https://images.unsplash.com/photo-1559339352-11d035aa65de?auto=format&fit=crop&w=1200&q=80',
                'sort' => 2,
            ],
            [
                'title' => 'Pricing for growing restaurants',
                'slug' => 'plans',
                'nav_label' => 'Pricing',
                'eyebrow' => 'Plans',
                'summary' => 'Start with the tools you need today and upgrade when your restaurant grows.',
                'content' => '<h2>Everything starts with a restaurant website.</h2><p>Plans include menu management, ordering, payments, reports, tenant pages, staff users, and support for customer updates.</p>',
                'hero_image_url' => 'https://images.unsplash.com/photo-1555396273-367ea4eb4db5?auto=format&fit=crop&w=1200&q=80',
                'cta_label' => 'Create Your Account',
                'cta_url' => '/register',
                'sort' => 3,
            ],
            [
                'title' => 'Contact SimpleOrder',
                'slug' => 'contact',
                'nav_label' => 'Contact Us',
                'eyebrow' => 'Contact Us',
                'summary' => 'Need help launching, connecting payments, or planning your restaurant ordering setup? Send us a note.',
                'content' => '<h2>We can help you get launched.</h2><p>Reach out for help with setup, domains, Stripe, notifications, menu structure, or getting your first restaurant live.</p>',
                'hero_image_url' => 'https://images.unsplash.com/photo-1552566626-52f8b828add9?auto=format&fit=crop&w=1200&q=80',
                'sort' => 4,
            ],
        ];

        foreach ($defaults as $page) {
            static::firstOrCreate(['slug' => $page['slug']], $page);
        }
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