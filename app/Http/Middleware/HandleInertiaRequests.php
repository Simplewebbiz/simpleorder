<?php

namespace App\Http\Middleware;

use App\Models\Tenant\Setting;
use Illuminate\Http\Request;
use Inertia\Middleware;

class HandleInertiaRequests extends Middleware
{
    protected $rootView = 'app';

    public function version(Request $request): ?string
    {
        return parent::version($request);
    }

    public function share(Request $request): array
    {
        $shared = [
            ...parent::share($request),
            'flash' => [
                'success' => fn () => $request->session()->get('success'),
                'error'   => fn () => $request->session()->get('error'),
            ],
        ];

        // Share tenant branding on all tenant pages
        if (tenancy()->initialized) {
            $shared['tenant_brand'] = [
                'name'    => Setting::get('store_name', ''),
                'phone'   => Setting::get('store_phone', ''),
                'address' => Setting::get('store_address', []),
                'logo'    => Setting::get('logo_url', null),
                'email'   => Setting::get('order_email', ''),
                'hours'   => Setting::get('store_hours', []),
            ];
            $shared['auth']['tenant_user'] = $request->user('tenant');
            $shared['auth']['tenant'] = [
                'id' => tenant()->id,
                'name' => tenant()->name,
            ];
        }

        if ($request->user('platform')) {
            $shared['auth']['platform_user'] = $request->user('platform');
            $shared['auth']['tenant'] = $request->user('platform')->tenant ?? null;
        }

        return $shared;
    }
}
