<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Platform;

Route::get('/', [Platform\MarketingController::class, 'home'])->name('home');
Route::get('/about', [Platform\MarketingController::class, 'about'])->name('about');
Route::get('/plans', [Platform\MarketingController::class, 'plans'])->name('plans');
Route::get('/pricing', [Platform\MarketingController::class, 'plans'])->name('pricing');
Route::get('/contact', [Platform\MarketingController::class, 'contact'])->name('contact');

Route::middleware('guest:platform')->group(function () {
    Route::get('/login', [Platform\AuthController::class, 'showLogin'])->name('login');
    Route::post('/login', [Platform\AuthController::class, 'login'])->name('login.post');
    Route::get('/register', [Platform\AuthController::class, 'showRegister'])->name('register');
    Route::post('/register', [Platform\AuthController::class, 'register'])->name('register.post');
});

Route::post('/logout', [Platform\AuthController::class, 'logout'])->name('platform.logout')->middleware('auth:platform');

Route::get('/platform/stripe/callback', [Platform\StripeConnectController::class, 'callback'])
    ->name('platform.stripe.callback')->middleware('auth:platform');

Route::prefix('dashboard')->name('platform.')->middleware(['auth:platform', 'tenant.active'])->group(function () {
    Route::get('/', [Platform\DashboardController::class, 'index'])->name('dashboard');
    Route::get('/billing', [Platform\BillingController::class, 'index'])->name('billing.index');
    Route::post('/billing/subscribe', [Platform\BillingController::class, 'subscribe'])->name('billing.subscribe');
    Route::post('/billing/cancel', [Platform\BillingController::class, 'cancel'])->name('billing.cancel');
    Route::get('/billing/portal', [Platform\BillingController::class, 'portal'])->name('billing.portal');
    Route::get('/stripe/connect', [Platform\StripeConnectController::class, 'redirect'])->name('stripe.redirect');
    Route::post('/stripe/disconnect', [Platform\StripeConnectController::class, 'disconnect'])->name('stripe.disconnect');
});

Route::post('/webhook/stripe', [Platform\WebhookController::class, 'handle'])->name('webhook.stripe');

Route::prefix('superadmin')->name('platform.superadmin.')->middleware(['auth:platform', 'super.admin'])->group(function () {
    Route::get('/', [Platform\SuperAdmin\DashboardController::class, 'index'])->name('index');
    Route::resource('tenants', Platform\SuperAdmin\TenantController::class)->names('tenants');
    Route::resource('plans', Platform\SuperAdmin\PlanController::class)->names('plans');
    Route::post('/tenants/{tenant}/impersonate', [Platform\SuperAdmin\TenantController::class, 'impersonate'])->name('tenants.impersonate');

    Route::get('/settings', [Platform\SuperAdmin\PlatformSettingsController::class, 'index'])->name('settings');
    Route::post('/settings', [Platform\SuperAdmin\PlatformSettingsController::class, 'update'])->name('settings.update');
    Route::post('/settings/test', [Platform\SuperAdmin\PlatformSettingsController::class, 'testConnection'])->name('settings.test');
});