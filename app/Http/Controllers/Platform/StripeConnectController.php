<?php

namespace App\Http\Controllers\Platform;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Stripe\StripeClient;

class StripeConnectController extends Controller
{
    private StripeClient $stripe;

    public function __construct()
    {
        $this->stripe = new StripeClient(config('stripe.secret'));
    }

    public function redirect()
    {
        $tenant = auth('platform')->user()->tenant;
        $state  = Str::random(40);

        session(['stripe_connect_state' => $state]);

        $params = http_build_query([
            'response_type'  => 'code',
            'client_id'      => config('services.stripe.client_id'),
            'scope'          => 'read_write',
            'redirect_uri'   => config('stripe.connect_redirect'),
            'state'          => $state,
            'stripe_user[email]'         => auth('platform')->user()->email,
            'stripe_user[business_name]' => $tenant->name,
        ]);

        return redirect('https://connect.stripe.com/oauth/authorize?' . $params);
    }

    public function callback(Request $request)
    {
        $tenant = auth('platform')->user()->tenant;
        $returnUrl = $this->tenantStripeSettingsUrl($tenant);

        if ($request->state !== session('stripe_connect_state')) {
            return redirect($returnUrl)->withErrors(['stripe' => 'Invalid state parameter.']);
        }

        if ($request->has('error')) {
            return redirect($returnUrl)->withErrors(['stripe' => $request->error_description]);
        }

        try {
            $response = $this->stripe->oauth->token([
                'grant_type' => 'authorization_code',
                'code'       => $request->code,
            ]);

            $tenant->update([
                'stripe_connect_id'           => $response->stripe_user_id,
                'stripe_connect_access_token' => $response->access_token,
                'stripe_connect_active'       => true,
            ]);

            tenancy()->initialize($tenant);
            \App\Models\Tenant\Setting::set('stripe_connect_id', $response->stripe_user_id);
            tenancy()->end();

            return redirect($returnUrl)->with('success', 'Stripe account connected successfully!');
        } catch (\Exception $e) {
            return redirect($returnUrl)->withErrors(['stripe' => $e->getMessage()]);
        }
    }

    public function disconnect()
    {
        $tenant = auth('platform')->user()->tenant;

        $tenant->update([
            'stripe_connect_id'           => null,
            'stripe_connect_access_token' => null,
            'stripe_connect_active'       => false,
        ]);

        tenancy()->initialize($tenant);
        \App\Models\Tenant\Setting::set('stripe_connect_id', null);
        tenancy()->end();

        return redirect($this->tenantStripeSettingsUrl($tenant))->with('success', 'Stripe account disconnected.');
    }

    private function tenantStripeSettingsUrl($tenant): string
    {
        $domain = $tenant->domains()->where('is_primary', true)->value('domain')
            ?: $tenant->domains()->value('domain');

        if (! $domain) {
            return route('platform.dashboard');
        }

        $scheme = parse_url(config('app.url'), PHP_URL_SCHEME) ?: 'https';

        return $scheme . '://' . $domain . '/admin/settings/stripe';
    }
}