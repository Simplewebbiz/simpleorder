<?php

namespace App\Http\Controllers\Tenant;

use App\Http\Controllers\Controller;
use App\Models\Tenant\{Category, Cart, Page, Setting};
use App\Services\CartService;
use Illuminate\Http\Request;
use Inertia\Inertia;

class StorefrontController extends Controller
{
    public function index(Request $request, CartService $cartService)
    {
        Page::seedDefaults();

        $categories = Category::with(['items.image', 'items.options.values', 'image'])
            ->where('is_active', true)
            ->orderBy('sort')
            ->get();

        $pages = Page::with('hero')
            ->where('is_published', true)
            ->orderBy('sort')
            ->get();

        $cart = $cartService->getOrCreate($request);
        $cartData = $cartService->format($cart);

        $settings = [
            'store_name'     => Setting::get('store_name', 'SimpleOrder'),
            'store_address'  => Setting::get('store_address'),
            'store_phone'    => Setting::get('store_phone'),
            'store_hours'    => Setting::get('store_hours', Setting::defaults()['store_hours']),
            'tax_rate'       => Setting::get('tax_rate', 0),
            'food_charge'    => Setting::get('food_charge', 0),
            'grocery_charge' => Setting::get('grocery_charge', 0),
            'timezone'       => Setting::get('timezone', 'America/Chicago'),
            'allow_pickup'   => Setting::get('allow_pickup', true),
            'allow_delivery' => Setting::get('allow_delivery', true),
            'stripe_key'     => Setting::get('stripe_publishable_key') ?: config('stripe.key'),
        ];

        $tenant = tenant();

        return Inertia::render('Storefront/App', [
            'menu'     => $categories,
            'pages'    => $pages,
            'cart'     => $cartData,
            'settings' => $settings,
            'tenant'   => [
                'name' => $tenant->name,
                'id'   => $tenant->id,
            ],
            'auth'     => [
                'user' => auth('tenant')->user(),
            ],
        ]);
    }
}