<?php

declare(strict_types=1);

namespace App\Providers;

use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\ServiceProvider;
use Stancl\JobPipeline\JobPipeline;
use Stancl\Tenancy\Events;
use Stancl\Tenancy\Jobs;
use Stancl\Tenancy\Listeners;
use Stancl\Tenancy\Middleware;

class TenancyServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        //
    }

    public function boot(): void
    {
        $this->bootEvents();
        $this->mapRoutes();
    }

    protected function bootEvents(): void
    {
        Event::listen(
            Events\TenantCreated::class,
            JobPipeline::make([
                Jobs\CreateDatabase::class,
                Jobs\MigrateDatabase::class,
                Jobs\SeedDatabase::class,
            ])->send(fn (Events\TenantCreated $event) => $event->tenant)->toListener()
        );

        Event::listen(Events\TenantDeleted::class,
            JobPipeline::make([
                Jobs\DeleteDatabase::class,
            ])->send(fn (Events\TenantDeleted $event) => $event->tenant)->toListener()
        );

        Event::listen(Events\TenancyInitialized::class, Listeners\BootstrapTenancy::class);
        Event::listen(Events\TenancyEnded::class, Listeners\RevertToCentralContext::class);
    }

    protected function mapRoutes(): void
    {
        // Routes are loaded via bootstrap/app.php `then` callback
        // to ensure they are registered AFTER central web routes.
    }
}
