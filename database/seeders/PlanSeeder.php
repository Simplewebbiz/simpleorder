<?php

namespace Database\Seeders;

use App\Models\Plan;
use Illuminate\Database\Seeder;

class PlanSeeder extends Seeder
{
    public function run(): void
    {
        $plans = [
            [
                'name'          => 'Starter',
                'slug'          => 'starter',
                'description'   => 'Perfect for small restaurants just getting started.',
                'price_monthly' => 4900,  // $49/mo
                'price_yearly'  => 47040, // $470.40/yr (20% off)
                'max_items'     => 50,
                'max_categories' => 10,
                'custom_domain' => false,
                'order_reports' => true,
                'sort'          => 1,
            ],
            [
                'name'          => 'Pro',
                'slug'          => 'pro',
                'description'   => 'For growing restaurants with more menu items.',
                'price_monthly' => 9900,  // $99/mo
                'price_yearly'  => 95040, // $950.40/yr (20% off)
                'max_items'     => 0,     // unlimited
                'max_categories' => 0,
                'custom_domain' => true,
                'order_reports' => true,
                'sort'          => 2,
            ],
            [
                'name'          => 'Enterprise',
                'slug'          => 'enterprise',
                'description'   => 'Multi-location, priority support, custom integrations.',
                'price_monthly' => 24900, // $249/mo
                'price_yearly'  => 239040,
                'max_items'     => 0,
                'max_categories' => 0,
                'custom_domain' => true,
                'order_reports' => true,
                'sort'          => 3,
            ],
        ];

        foreach ($plans as $plan) {
            Plan::updateOrCreate(['slug' => $plan['slug']], $plan);
        }
    }
}
