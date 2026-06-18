<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Platform;

// ─── Platform (central domain) ──────────────────────────────────────────────
// Public marketing + signup + login

Route::get('/', [Platform\MarketingController::class, 'home'])->name('home');
Route::get('/pricing', [Platform\MarketingController::class, 'pricing'])->name('pricing');

// Auth
Route::middleware('guest:platform')->group(function () {
    Route::get('/login', [Platform\AuthController::class, 'showLogin'])->name('login');
    Route::post('/login', [Platform\AuthController::class, 'login'])->name('login.post');
    Route::get('/register', [Platform\AuthController::class, 'showRegister'])->name('register');
    Route::post('/register', [Platform\AuthController::class, 'register'])->name('register.post');
});

Route::post('/logout', [Platform\AuthController::class, 'logout'])->name('logout')->middleware('auth:platform');

// Stripe Connect OAuth callback
Route::get('/platform/stripe/callback', [Platform\StripeConnectController::class, 'callback'])
    ->name('stripe.connect.callback')->middleware('auth:platform');

// Tenant dashboard (after login) — billing, settings, Stripe Connect
Route::prefix('dashboard')->name('dashboard.')->middleware(['auth:platform', 'tenant.active'])->group(function () {
    Route::get('/', [Platform\DashboardController::class, 'index'])->name('index');
    Route::get('/billing', [Platform\BillingController::class, 'index'])->name('billing');
    Route::post('/billing/subscribe', [Platform\BillingController::class, 'subscribe'])->name('billing.subscribe');
    Route::post('/billing/cancel', [Platform\BillingController::class, 'cancel'])->name('billing.cancel');
    Route::get('/billing/portal', [Platform\BillingController::class, 'portal'])->name('billing.portal');
    Route::get('/stripe/connect', [Platform\StripeConnectController::class, 'redirect'])->name('stripe.connect');
    Route::post('/stripe/disconnect', [Platform\StripeConnectController::class, 'disconnect'])->name('stripe.disconnect');
});

// Stripe webhook (platform subscriptions)
Route::post('/webhook/stripe', [Platform\WebhookController::class, 'handle'])->name('webhook.stripe');

// Super admin panel
Route::prefix('superadmin')->name('platform.superadmin.')->middleware(['auth:platform', 'super.admin'])->group(function () {
    Route::get('/', [Platform\SuperAdmin\DashboardController::class, 'index'])->name('index');
    Route::resource('tenants', Platform\SuperAdmin\TenantController::class)->names('tenants');
    Route::resource('plans', Platform\SuperAdmin\PlanController::class)->names('plans');
    Route::post('/tenants/{tenant}/impersonate', [Platform\SuperAdmin\TenantController::class, 'impersonate'])->name('tenants.impersonate');

    // Platform-wide API settings
    Route::get('/settings', [Platform\SuperAdmin\PlatformSettingsController::class, 'index'])->name('settings');
    Route::post('/settings', [Platform\SuperAdmin\PlatformSettingsController::class, 'update'])->name('settings.update');
    Route::post('/settings/test', [Platform\SuperAdmin\PlatformSettingsController::class, 'testConnection'])->name('settings.test');
});
