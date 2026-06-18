<?php

return [
    'defaults' => [
        'guard'     => 'web',
        'passwords' => 'users',
    ],

    'guards' => [
        'web' => [
            'driver'   => 'session',
            'provider' => 'users',
        ],

        // Platform owner login (central DB — PlatformAdmin model)
        'platform' => [
            'driver'   => 'session',
            'provider' => 'platform_admins',
        ],

        // Tenant staff/customer login (tenant DB — Tenant\User model)
        'tenant' => [
            'driver'   => 'session',
            'provider' => 'tenant_users',
        ],
    ],

    'providers' => [
        'users' => [
            'driver' => 'eloquent',
            'model'  => App\Models\Tenant\User::class,
        ],

        'platform_admins' => [
            'driver' => 'eloquent',
            'model'  => App\Models\PlatformAdmin::class,
        ],

        'tenant_users' => [
            'driver' => 'eloquent',
            'model'  => App\Models\Tenant\User::class,
        ],
    ],

    'passwords' => [
        'users' => [
            'provider' => 'users',
            'table'    => 'password_reset_tokens',
            'expire'   => 60,
            'throttle' => 60,
        ],

        'platform_admins' => [
            'provider' => 'platform_admins',
            'table'    => 'password_resets',
            'expire'   => 60,
            'throttle' => 60,
        ],
    ],

    'password_timeout' => 10800,
];
