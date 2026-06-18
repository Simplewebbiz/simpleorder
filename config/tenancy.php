<?php

declare(strict_types=1);

use Stancl\Tenancy\Database\Models\Domain;
use App\Models\Tenant;

return [
    'tenant_model' => Tenant::class,
    'id_generator' => Stancl\Tenancy\UUIDGenerator::class,

    'domain_model' => Domain::class,

    'central_domains' => [
        'simpleorder.com',
        'www.simpleorder.com',
    ],

    'bootstrappers' => [
        Stancl\Tenancy\Bootstrappers\DatabaseTenancyBootstrapper::class,
        Stancl\Tenancy\Bootstrappers\CacheTagsBootstrapper::class,
        Stancl\Tenancy\Bootstrappers\FilesystemTenancyBootstrapper::class,
        Stancl\Tenancy\Bootstrappers\QueueTenancyBootstrapper::class,
    ],

    'database' => [
        'central_connection' => env('DB_CONNECTION', 'mysql'),
        'template_tenant_connection' => null,
        'prefix' => env('TENANCY_DB_PREFIX', 'simpleorder_tenant_'),
        'suffix' => '',
        'managers' => [
            'mysql' => Stancl\TenancyForLaravel\DatabaseManagers\MySQLDatabaseManager::class,
        ],
    ],

    'cache' => [
        'tag_base' => 'tenant',
    ],

    'filesystem' => [
        'suffix_base' => 'tenant',
        'disks' => [
            'local' => [
                'root_override' => env('APP_STORAGE_PATH', storage_path('app')) . '/tenant%tenant%/',
            ],
            'public' => [
                'root_override' => env('APP_STORAGE_PATH', storage_path('app')) . '/tenant%tenant%/public/',
                'url_override' => '/storage/tenant%tenant%',
            ],
        ],
        'root_override_option' => Stancl\Tenancy\StorageDrivers\Local\LocalStorageDriver::OPTION_PREPEND_TENANT_PREFIX,
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
