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
        if ($request->state !== session('stripe_connect_state')) {
            return redirect()->route('dashboard.settings.stripe')->withErrors(['stripe' => 'Invalid state parameter.']);
        }

        if ($request->has('error')) {
            return redirect()->route('dashboard.settings.stripe')->withErrors(['stripe' => $request->error_description]);
        }

        try {
            $response = $this->stripe->oauth->token([
                'grant_type' => 'authorization_code',
                'code'       => $request->code,
            ]);

            auth('platform')->user()->tenant->update([
                'stripe_connect_id'           => $response->stripe_user_id,
                'stripe_connect_access_token' => $response->access_token,
                'stripe_connect_active'       => true,
            ]);

            // Also save to tenant's own settings DB
            tenancy()->initialize(auth('platform')->user()->tenant);
            \App\Models\Tenant\Setting::set('stripe_connect_id', $response->stripe_user_id);
            tenancy()->end();

            return redirect()->route('dashboard.settings.stripe')->with('success', 'Stripe account connected successfully!');
        } catch (\Exception $e) {
            return redirect()->route('dashboard.settings.stripe')->withErrors(['stripe' => $e->getMessage()]);
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

        return back()->with('success', 'Stripe account disconnected.');
    }
}
