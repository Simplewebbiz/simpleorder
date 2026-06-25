<?php

namespace App\Console\Commands;

use App\Models\Plan;
use App\Models\Tenant;
use App\Models\Tenant\Category;
use App\Models\Tenant\Coupon;
use App\Models\Tenant\Item;
use App\Models\Tenant\Order;
use App\Models\Tenant\Page;
use App\Models\Tenant\Setting;
use App\Models\Tenant\User;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;

class CreateDemoTenant extends Command
{
    protected $signature = 'simpleorder:demo-tenant
        {--id=demo : Tenant subdomain/id to create}
        {--name=Demo Bistro : Restaurant name}
        {--email=owner@example.com : Owner login email}
        {--password=ChangeMe123! : Owner login password}';

    protected $description = 'Create or refresh a safe demo restaurant tenant with admin user, CMS pages, menu items, and a sample order.';

    public function handle(): int
    {
        $id = Str::slug((string) $this->option('id'));
        $name = trim((string) $this->option('name')) ?: 'Demo Bistro';
        $email = trim((string) $this->option('email')) ?: 'owner@example.com';
        $password = (string) $this->option('password');

        if ($id === '') {
            $this->error('The tenant id cannot be empty.');
            return self::FAILURE;
        }

        $plan = Plan::query()->where('is_active', true)->orderBy('sort')->first();

        if (! $plan) {
            $this->error('No active plan exists. Run the PlanSeeder first.');
            return self::FAILURE;
        }

        $tenant = Tenant::query()->find($id);

        if (! $tenant) {
            $tenant = Tenant::create([
                'id' => $id,
                'name' => $name,
                'plan_id' => $plan->id,
                'subscription_status' => 'trialing',
                'trial_ends_at' => now()->addDays(14),
                'is_active' => true,
            ]);
        } else {
            $tenant->update([
                'name' => $name,
                'plan_id' => $tenant->plan_id ?: $plan->id,
                'subscription_status' => $tenant->subscription_status ?: 'trialing',
                'trial_ends_at' => $tenant->trial_ends_at ?: now()->addDays(14),
                'is_active' => true,
            ]);
        }

        $host = parse_url(config('app.url'), PHP_URL_HOST) ?: 'simpleorder.biz';
        $domain = $id . '.' . $host;

        $tenant->domains()->updateOrCreate(
            ['domain' => $domain],
            ['is_primary' => true, 'is_custom' => false, 'verified' => true]
        );

        tenancy()->initialize($tenant);

        try {
            $owner = User::query()->updateOrCreate(
                ['email' => $email],
                [
                    'name' => 'Demo Owner',
                    'password' => Hash::make($password),
                    'role' => User::ROLE_OWNER,
                    'phone' => '555-0100',
                ]
            );

            foreach (Setting::defaults() as $key => $value) {
                Setting::set($key, $key === 'store_name' ? $name : $value);
            }

            Page::seedDefaults();
            $this->seedCoupons();
            $this->seedMenu();
            $this->seedSampleOrder($owner);

            if (Schema::hasTable('order_sequences') && DB::table('order_sequences')->count() === 0) {
                DB::table('order_sequences')->insert(['next_id' => 2]);
            }

            $counts = [
                'pages' => Schema::hasTable('pages') ? DB::table('pages')->count() : 0,
                'categories' => Schema::hasTable('categories') ? DB::table('categories')->whereNull('deleted_at')->count() : 0,
                'items' => Schema::hasTable('items') ? DB::table('items')->whereNull('deleted_at')->count() : 0,
                'orders' => Schema::hasTable('orders') ? DB::table('orders')->whereNull('deleted_at')->count() : 0,
                'coupons' => Schema::hasTable('coupons') ? DB::table('coupons')->whereNull('deleted_at')->count() : 0,
            ];
        } finally {
            tenancy()->end();
        }

        $this->info('Demo tenant is ready.');
        foreach ($counts ?? [] as $label => $count) {
            $this->line(ucfirst($label) . ': ' . $count);
        }
        $this->line('Restaurant site: https://' . $domain);
        $this->line('Restaurant admin: https://' . $domain . '/admin');
        $this->line('Owner login: ' . $email);

        return self::SUCCESS;
    }

    private function seedCoupons(): void
    {
        Coupon::query()->updateOrCreate(
            ['code' => 'WELCOME10'],
            [
                'name' => 'Welcome Special',
                'type' => 'percent',
                'value' => 10,
                'minimum_subtotal' => 10,
                'is_active' => true,
            ]
        );
    }
    private function seedMenu(): void
    {
        $category = Category::query()->updateOrCreate(
            ['slug' => 'favorites'],
            [
                'name' => 'House Favorites',
                'description' => 'Popular dishes for testing the public menu and admin screens.',
                'sort' => 1,
                'is_active' => true,
            ]
        );

        $items = [
            [
                'name' => 'Sunshine Chicken Bowl',
                'sku' => 'DEMO-BOWL',
                'description' => 'Grilled chicken, bright vegetables, herbs, and citrus rice.',
                'price' => 14.95,
                'sort' => 1,
            ],
            [
                'name' => 'Garden Flatbread',
                'sku' => 'DEMO-FLATBREAD',
                'description' => 'Roasted vegetables, mozzarella, tomato, and fresh basil.',
                'price' => 12.50,
                'sort' => 2,
            ],
            [
                'name' => 'Berry Lemonade',
                'sku' => 'DEMO-LEMONADE',
                'description' => 'House lemonade with strawberry and blueberry.',
                'price' => 4.25,
                'sort' => 3,
            ],
        ];

        foreach ($items as $data) {
            $item = Item::query()->updateOrCreate(
                ['sku' => $data['sku']],
                $data + ['taxable' => true, 'type' => 'food', 'is_active' => true]
            );

            $category->items()->syncWithoutDetaching([$item->id => ['sort' => $data['sort']]]);
        }
    }

    private function seedSampleOrder(User $owner): void
    {
        if (Order::query()->where('stripe_payment_intent', 'demo-payment-intent')->exists()) {
            return;
        }

        Order::query()->create([
            'increment_id' => 1,
            'key' => Str::random(32),
            'user_id' => $owner->id,
            'method' => 'pickup',
            'status' => 'placed',
            'contact_firstname' => 'Sample',
            'contact_lastname' => 'Customer',
            'contact_email' => 'customer@example.com',
            'contact_phone' => '555-0111',
            'billing_firstname' => 'Sample',
            'billing_lastname' => 'Customer',
            'billing_address' => '100 Main Street',
            'billing_city' => 'Springfield',
            'billing_state' => 'IL',
            'billing_zip' => '62701',
            'stripe_payment_intent' => 'demo-payment-intent',
            'subtotal' => 19.20,
            'tax' => 1.34,
            'delivery' => 0,
            'tip' => 3.00,
            'total' => 23.54,
            'comments' => 'Demo order for testing the admin queue.',
        ]);
    }
}