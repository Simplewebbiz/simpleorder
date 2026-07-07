<?php

namespace App\Console\Commands;

use App\Models\MarketingPage;
use App\Models\Plan;
use App\Models\PlatformAdmin;
use App\Models\Tenant;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Schema;
use Throwable;

class SimpleOrderSmokeTest extends Command
{
    protected $signature = 'simpleorder:smoke {--tenant= : Optional tenant id to test}';

    protected $description = 'Run safe smoke checks for SimpleOrder backend, super admin, tenant admin, and storefront plumbing.';

    private int $passed = 0;

    private int $warnings = 0;

    private int $failed = 0;

    public function handle(): int
    {
        $this->newLine();
        $this->info('SimpleOrder smoke test');
        $this->line('This checks configuration, database tables, routes, and page files. It does not charge cards or place orders.');
        $this->newLine();

        $this->checkConfig();
        $this->checkCentralDatabase();
        $this->checkRoutes();
        $this->checkFrontendPages();
        $this->checkTenantDatabase();

        $this->newLine();
        $this->line("Passed: {$this->passed}  Warnings: {$this->warnings}  Failed: {$this->failed}");

        if ($this->failed > 0) {
            $this->error('Smoke test failed. Fix the failed items above before relying on the site.');
            return self::FAILURE;
        }

        if ($this->warnings > 0) {
            $this->warn('Smoke test completed with warnings. Review them before launch.');
            return self::SUCCESS;
        }

        $this->info('Smoke test passed.');
        return self::SUCCESS;
    }

    private function checkConfig(): void
    {
        $this->section('Configuration');

        $this->passIf(config('app.key') !== '', 'APP_KEY is set');
        $this->passIf(str_starts_with((string) config('app.url'), 'https://'), 'APP_URL uses https');
        $this->passIf(config('cache.default') === 'file', 'Cache store is file', 'CACHE_STORE should be file on this cPanel install unless you create the database cache table.');
        $this->passIf(config('session.driver') === 'file', 'Session driver is file', 'SESSION_DRIVER should be file on this cPanel install unless you create the sessions table.');
        $tenantTemplateConnection = config('tenancy.database.template_tenant_connection');
        $tenantTemplateDriver = $tenantTemplateConnection ? config("database.connections.{$tenantTemplateConnection}.driver") : null;
        $this->passIf((bool) $tenantTemplateDriver, 'Tenant database driver is configured', 'Tenant DB template connection must point to a configured connection such as tenant.');
        $tenantDatabaseManager = $tenantTemplateDriver ? config("tenancy.database.managers.{$tenantTemplateDriver}") : null;
        $this->passIf((bool) $tenantDatabaseManager, 'Tenant database manager is configured', 'Tenant DB driver must have a matching Stancl tenancy database manager.');
        $this->passIf(File::exists(public_path('build/manifest.json')), 'Frontend build manifest exists', 'Run npm run build.');
    }

    private function checkCentralDatabase(): void
    {
        $this->section('Central backend database');

        $tables = [
            'plans',
            'tenants',
            'domains',
            'platform_admins',
            'platform_settings',
            'marketing_pages',
        ];

        foreach ($tables as $table) {
            $this->passIf(Schema::hasTable($table), "Central table exists: {$table}", "Run central migrations.");
        }

        if (Schema::hasTable('plans')) {
            $this->passIf(Plan::where('is_active', true)->count() > 0, 'At least one active plan exists', 'Run PlanSeeder.');
        }

        if (Schema::hasTable('marketing_pages')) {
            $slugs = MarketingPage::pluck('slug')->all();
            foreach (['home', 'about', 'plans', 'contact'] as $slug) {
                $this->passIf(in_array($slug, $slugs, true), "Marketing CMS page exists: {$slug}", 'Open / once or seed defaults through MarketingPage.');
            }
        }

        if (Schema::hasTable('platform_admins')) {
            $this->passIf(PlatformAdmin::where('is_super', true)->exists(), 'At least one super admin exists', 'Create the super admin login.');
        }
    }

    private function checkRoutes(): void
    {
        $this->section('Routes and controllers');

        $routes = [
            'home',
            'about',
            'plans',
            'contact',
            'login',
            'register',
            'platform.dashboard',
            'platform.billing.index',
            'platform.superadmin.index',
            'platform.superadmin.tenants.index',
            'platform.superadmin.plans.index',
            'platform.superadmin.marketing-pages.index',
            'platform.superadmin.settings',
            'tenant.ordering.order.show',
            'tenant.admin.dashboard',
            'tenant.admin.orders.index',
            'tenant.admin.categories.index',
            'tenant.admin.items.index',
            'tenant.admin.coupons.index',
            'tenant.admin.pages.index',
            'tenant.admin.media.index',
            'tenant.admin.users.index',
            'tenant.admin.reports.index',
            'tenant.admin.settings.index',
            'tenant.admin.settings.stripe',
            'tenant.storefront',
        ];

        foreach ($routes as $route) {
            $this->passIf(Route::has($route), "Route exists: {$route}", 'Check routes/web.php and routes/tenant.php.');
        }
    }

    private function checkFrontendPages(): void
    {
        $this->section('Frontend/admin pages');

        $files = [
            'resources/js/pages/Platform/Marketing/Home.vue',
            'resources/js/pages/Platform/Marketing/About.vue',
            'resources/js/pages/Platform/Marketing/Plans.vue',
            'resources/js/pages/Platform/Marketing/Contact.vue',
            'resources/js/pages/Platform/Auth/Login.vue',
            'resources/js/pages/Platform/SuperAdmin/Dashboard.vue',
            'resources/js/pages/Platform/SuperAdmin/Tenants.vue',
            'resources/js/pages/Platform/SuperAdmin/Plans.vue',
            'resources/js/pages/Platform/SuperAdmin/MarketingPages.vue',
            'resources/js/pages/Platform/SuperAdmin/Settings.vue',
            'resources/js/pages/Admin/Dashboard.vue',
            'resources/js/pages/Admin/Orders/Index.vue',
            'resources/js/pages/Admin/Categories/Index.vue',
            'resources/js/pages/Admin/Items/Index.vue',
            'resources/js/pages/Admin/Coupons/Index.vue',
            'resources/js/pages/Admin/Pages/Index.vue',
            'resources/js/pages/Admin/Media/Index.vue',
            'resources/js/pages/Admin/Users/Index.vue',
            'resources/js/pages/Admin/Reports/Index.vue',
            'resources/js/pages/Admin/Settings/Index.vue',
            'resources/js/pages/Storefront/App.vue',
        ];

        foreach ($files as $file) {
            $this->passIf(File::exists(base_path($file)), "Frontend file exists: {$file}", 'The frontend build may be missing files.');
        }
    }

    private function checkTenantDatabase(): void
    {
        $this->section('Tenant backend/admin database');

        if (! Schema::hasTable('tenants')) {
            $this->warnCheck('Cannot test tenant database because central tenants table is missing.');
            return;
        }

        $tenantId = $this->option('tenant');
        $tenant = $tenantId ? Tenant::find($tenantId) : Tenant::query()->first();

        if (! $tenant) {
            $this->warnCheck('No tenant exists yet. Tenant admin/storefront checks skipped until a restaurant signs up or a tenant is created.');
            return;
        }

        try {
            tenancy()->initialize($tenant);

            $tables = [
                'users',
                'media',
                'categories',
                'items',
                'category_item',
                'item_options',
                'item_option_values',
                'carts',
                'cart_items',
                'orders',
                'order_items',
                'coupons',
                'settings',
                'address_geocodes',
                'pages',
            ];

            foreach ($tables as $table) {
                $this->passIf(Schema::hasTable($table), "Tenant table exists for {$tenant->id}: {$table}", 'Run tenants:migrate.');
            }

            if (Schema::hasTable('users')) {
                $this->passIf(DB::table('users')->count() > 0, "Tenant {$tenant->id} has at least one user", 'Tenant admin login needs a user.');
                $this->passIf(DB::table('users')->whereIn('role', ['owner', 'manager'])->exists(), "Tenant {$tenant->id} has an admin user", 'Create an owner or manager user for the restaurant admin.');
            }

            if (Schema::hasTable('settings')) {
                $this->passIf(DB::table('settings')->exists(), "Tenant {$tenant->id} has settings rows", 'Tenant store settings may need defaults.');
                $this->passIf(DB::table('settings')->where('key', 'store_name')->exists(), "Tenant {$tenant->id} has store name setting", 'Tenant store settings may need defaults.');
            }

            if (Schema::hasTable('pages')) {
                foreach (['home', 'about', 'contact'] as $slug) {
                    $this->passIf(DB::table('pages')->where('slug', $slug)->exists(), "Tenant {$tenant->id} CMS page exists: {$slug}", 'Tenant CMS defaults need to be seeded.');
                }
            }

            if (Schema::hasTable('categories')) {
                $this->passIf(DB::table('categories')->whereNull('deleted_at')->count() > 0, "Tenant {$tenant->id} has menu categories", 'Create at least one menu category.');
            }

            if (Schema::hasTable('items')) {
                $this->passIf(DB::table('items')->whereNull('deleted_at')->count() > 0, "Tenant {$tenant->id} has menu items", 'Create at least one menu item.');
            }

            if (Schema::hasTable('orders')) {
                $this->passIf(DB::table('orders')->whereNull('deleted_at')->count() > 0, "Tenant {$tenant->id} has at least one order", 'Place or seed a test order to verify the order queue and reports.');
            }

            if (Schema::hasTable('coupons')) {
                $this->passIf(DB::table('coupons')->whereNull('deleted_at')->count() > 0, "Tenant {$tenant->id} has at least one coupon", 'Create a promo code for testing coupons.');
            }
        } catch (Throwable $exception) {
            $this->failCheck("Tenant database check failed: {$exception->getMessage()}");
        } finally {
            if (tenancy()->initialized) {
                tenancy()->end();
            }
        }
    }

    private function section(string $title): void
    {
        $this->newLine();
        $this->line($title);
        $this->line(str_repeat('-', strlen($title)));
    }

    private function passIf(bool $condition, string $message, string $warning = ''): void
    {
        if ($condition) {
            $this->passed++;
            $this->line("[PASS] {$message}");
            return;
        }

        if ($warning !== '') {
            $this->warnCheck("{$message}. {$warning}");
            return;
        }

        $this->failCheck($message);
    }

    private function warnCheck(string $message): void
    {
        $this->warnings++;
        $this->warn("[WARN] {$message}");
    }

    private function failCheck(string $message): void
    {
        $this->failed++;
        $this->error("[FAIL] {$message}");
    }
}