<?php

return [
    'key'             => env('STRIPE_KEY'),
    'secret'          => env('STRIPE_SECRET'),
    'webhook_secret'  => env('STRIPE_WEBHOOK_SECRET'),
    'platform_fee'    => env('STRIPE_PLATFORM_FEE', 0.02), // 2% default
    'currency'        => 'usd',
    'connect_redirect' => env('STRIPE_CONNECT_REDIRECT', env('APP_URL') . '/platform/stripe/callback'),
];
