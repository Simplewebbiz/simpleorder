<?php

declare(strict_types=1);

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Tenant;
use App\Http\Middleware\InitializeTenancyByDomainOrShowNotConfigured;
use Stancl\Tenancy\Middleware\PreventAccessFromCentralDomains;

// All tenant routes share these middleware
Route::middleware([
    PreventAccessFromCentralDomains::class,
    InitializeTenancyByDomainOrShowNotConfigured::class,
])->name('tenant.')->group(function () {

    // Customer-facing ordering endpoints
    Route::middleware(['tenant.subscription'])->group(function () {
        // Ordering API endpoints
        Route::prefix('ordering')->name('ordering.')->group(function () {
            // Cart
            Route::post('/cart/save', [Tenant\CartController::class, 'save'])->name('cart.save');
            Route::post('/cart/item', [Tenant\CartController::class, 'addItem'])->name('cart.item');
            Route::post('/cart/item/remove', [Tenant\CartController::class, 'removeItem'])->name('cart.item.remove');
            Route::post('/cart/validate-address', [Tenant\CartController::class, 'validateAddress'])->name('cart.validate-address');
            Route::post('/cart/validate-payment', [Tenant\CartController::class, 'validatePayment'])->name('cart.validate-payment');
            Route::post('/cart/order', [Tenant\CartController::class, 'placeOrder'])->name('cart.order');

            // Order status (public via key)
            Route::post('/order/{key}', [Tenant\OrderStatusController::class, 'show'])->name('order.show');
        });

        // Customer auth
        Route::prefix('account')->name('account.')->group(function () {
            Route::post('/register', [Tenant\CustomerAuthController::class, 'register'])->name('register');
            Route::post('/login', [Tenant\CustomerAuthController::class, 'login'])->name('login');
            Route::post('/logout', [Tenant\CustomerAuthController::class, 'logout'])->name('logout');
            Route::get('/orders', [Tenant\CustomerAuthController::class, 'orders'])->name('orders')->middleware('auth:tenant');
        });

        // Media
        Route::get('/tenant-media/{file}', [Tenant\MediaController::class, 'serve'])->where('file', '.*')->name('media.serve');
    });

    // Tenant admin auth
    Route::prefix('admin')->name('admin.')->group(function () {
        Route::get('/login', [Tenant\Admin\AuthController::class, 'showLogin'])->name('login');
        Route::post('/login', [Tenant\Admin\AuthController::class, 'login'])->name('login.post');
        Route::post('/logout', [Tenant\Admin\AuthController::class, 'logout'])->name('logout')->middleware('auth:tenant');
    });
    // Tenant Admin Panel
    Route::prefix('admin')->name('admin.')->middleware(['auth:tenant', 'tenant.admin'])->group(function () {

        Route::get('/', [Tenant\Admin\DashboardController::class, 'index'])->name('dashboard');

        // Orders are available to owners, managers, and fulfillment staff.
        Route::prefix('orders')->name('orders.')->group(function () {
            Route::get('/', [Tenant\Admin\OrderController::class, 'index'])->name('index');
            Route::get('/{order}', [Tenant\Admin\OrderController::class, 'show'])->name('show');
            Route::patch('/{order}/status', [Tenant\Admin\OrderController::class, 'updateStatus'])->name('status');
        });

        Route::middleware(['tenant.manager'])->group(function () {
            // Reports
            Route::prefix('reports')->name('reports.')->group(function () {
                Route::get('/', [Tenant\Admin\ReportController::class, 'index'])->name('index');
                Route::get('/export', [Tenant\Admin\ReportController::class, 'export'])->name('export');
            });

            // Menu
            Route::resource('categories', Tenant\Admin\CategoryController::class);
            Route::resource('items', Tenant\Admin\ItemController::class);
            Route::resource('coupons', Tenant\Admin\CouponController::class)->except(['show', 'create', 'edit']);
            Route::resource('pages', Tenant\Admin\PageController::class)->only(['index', 'edit', 'update']);
            Route::post('/items/{item}/options', [Tenant\Admin\ItemController::class, 'saveOptions'])->name('items.options');

            // Users / Staff
            Route::resource('users', Tenant\Admin\UserController::class)->except(['show']);

            // Settings
            Route::get('/settings', [Tenant\Admin\SettingsController::class, 'index'])->name('settings.index');
            Route::post('/settings', [Tenant\Admin\SettingsController::class, 'update'])->name('settings.update');
            Route::get('/settings/stripe', [Tenant\Admin\StripeController::class, 'index'])->name('settings.stripe');
            Route::post('/settings/stripe/direct', [Tenant\Admin\StripeController::class, 'saveDirectKeys'])->name('settings.stripe.direct');
            Route::delete('/settings/stripe/direct', [Tenant\Admin\StripeController::class, 'removeDirectKeys'])->name('settings.stripe.direct.remove');

            // Media
            Route::get('/media', [Tenant\Admin\MediaController::class, 'index'])->name('media.index');
            Route::post('/media', [Tenant\Admin\MediaController::class, 'store'])->name('media.store');
            Route::delete('/media/{media}', [Tenant\Admin\MediaController::class, 'destroy'])->name('media.destroy');
        });
    });


    // Storefront SPA fallback. Keep this after admin routes so /admin/* is not swallowed.
    Route::middleware(['tenant.subscription'])
        ->get('/{path?}', [Tenant\StorefrontController::class, 'index'])
        ->where('path', '.*')
        ->name('storefront');
});