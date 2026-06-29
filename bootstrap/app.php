<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        api: __DIR__ . '/../routes/api.php',
        commands: __DIR__ . '/../routes/console.php',
        channels: __DIR__ . '/../routes/channels.php',
        then: function () {
            $centralDomains = config('tenancy.central_domains', []);

            foreach ($centralDomains as $domain) {
                \Illuminate\Support\Facades\Route::middleware('web')
                    ->domain($domain)
                    ->group(base_path('routes/web.php'));
            }

            \Illuminate\Support\Facades\Route::middleware('web')
                ->group(base_path('routes/tenant.php'));
        },
    )
    ->withMiddleware(function (Middleware $middleware) {
        $middleware->prepend(\App\Http\Middleware\LoadPlatformSettings::class);

        $middleware->web(append: [
            \App\Http\Middleware\HandleInertiaRequests::class,
        ]);

        $middleware->alias([
            'tenant.admin'         => \App\Http\Middleware\RequireTenantAdmin::class,
            'tenant.manager'       => \App\Http\Middleware\RequireTenantManager::class,
            'tenant.active'        => \App\Http\Middleware\RequireActiveTenant::class,
            'tenant.subscription'  => \App\Http\Middleware\TenantSubscriptionActive::class,
            'super.admin'          => \App\Http\Middleware\RequireSuperAdmin::class,
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions) {
        $exceptions->render(function (
            \Stancl\Tenancy\Exceptions\TenantCouldNotBeIdentifiedOnDomainException $e,
            \Illuminate\Http\Request $request
        ) {
            if ($request->expectsJson()) {
                return response()->json([
                    'message' => 'No storefront is configured for this domain.',
                ], 404);
            }

            return response()->view('errors.tenant-not-found', [
                'domain' => $request->getHost(),
                'platformUrl' => config('app.url'),
            ], 404);
        });
    })->create();



