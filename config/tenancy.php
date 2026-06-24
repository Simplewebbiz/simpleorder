<?php

declare(strict_types=1);

use App\Models\Tenant;
use Stancl\Tenancy\Database\Models\Domain;

$bootstrappers = [
    Stancl\Tenancy\Bootstrappers\DatabaseTenancyBootstrapper::class,
    class_exists(Stancl\Tenancy\Bootstrappers\CacheTenancyBootstrapper::class)
        ? Stancl\Tenancy\Bootstrappers\CacheTenancyBootstrapper::class
        : null,
    class_exists(Stancl\Tenancy\Bootstrappers\CacheTagsBootstrapper::class)
        ? Stancl\Tenancy\Bootstrappers\CacheTagsBootstrapper::class
        : null,
    Stancl\Tenancy\Bootstrappers\FilesystemTenancyBootstrapper::class,
    Stancl\Tenancy\Bootstrappers\QueueTenancyBootstrapper::class,
];

return [
    'tenant_model' => Tenant::class,
    'id_generator' => Stancl\Tenancy\UUIDGenerator::class,

    'domain_model' => Domain::class,

    'central_domains' => array_filter(array_map('trim', explode(',', env(
        'CENTRAL_DOMAINS',
        parse_url(env('APP_URL', 'http://localhost'), PHP_URL_HOST) ?: 'localhost'
    )))),

    'bootstrappers' => array_values(array_filter($bootstrappers)),


    'database' => [
        'central_connection' => env('DB_CONNECTION', 'mysql'),
        'template_tenant_connection' => null,
        'prefix' => env('TENANCY_DB_PREFIX', 'simpleorder_tenant_'),
        'suffix' => '',
        'managers' => [
            'mysql' => Stancl\Tenancy\TenantDatabaseManagers\MySQLDatabaseManager::class,
        ],
    ],

    'cache' => [
        'tag_base' => 'tenant',
    ],

    'filesystem' => [
        'suffix_base' => 'tenant',
        'disks' => [
            'local',
            'public',
        ],
        'root_override' => [
            'local' => (env('APP_STORAGE_PATH') ?: storage_path('app')) . '/',
            'public' => (env('APP_STORAGE_PATH') ?: storage_path('app')) . '/public/',
        ],
        'suffix_storage_path' => true,
        'asset_helper_tenancy' => false,
    ],

    'redis' => [
        'prefix_base' => 'tenant',
        'prefixed_connections' => [],
    ],

    'features' => [
        // Stancl\Tenancy\Features\UserImpersonation::class,
        // Stancl\Tenancy\Features\TelescopeTags::class,
        Stancl\Tenancy\Features\UniversalRoutes::class,
    ],

    'migration_parameters' => [
        '--force' => true,
        '--path' => [database_path('migrations/tenant')],
        '--realpath' => true,
    ],

    'seeder_parameters' => [
        '--class' => 'TenantDatabaseSeeder',
    ],
];
