<?php

namespace App\Http\Controllers\Tenant\Admin;

use App\Http\Controllers\Controller;
use App\Models\Tenant\Setting;
use App\Models\Tenant;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Inertia\Inertia;
use Stripe\StripeClient;

class StripeController extends Controller
{
    public function index()
    {
        $tenant         = tenant();
        $connectId      = Setting::get('stripe_connect_id');
        $directPubKey   = Setting::get('stripe_publishable_key');
        $directSecKey   = Setting::get('stripe_secret_key');

        // Determine active mode
        $mode = 'none';
        if ($directSecKey) {
            $mode = 'direct';
        } elseif ($connectId) {
            $mode = 'connect';
        }

        return Inertia::render('Admin/Stripe/Index', [
            'connectId'    => $connectId,
            'connectActive'=> (bool) $tenant->stripe_connect_active,
            'connectUrl'   => rtrim(config('app.url'), '/') . '/dashboard/stripe/connect',
            'disconnectUrl'=> rtrim(config('app.url'), '/') . '/dashboard/stripe/disconnect',
            'directPubKey' => $directPubKey,
            // Never send the secret to the frontend  just whether it's set
            'directSecSet' => ! empty($directSecKey),
            'mode'         => $mode,
            'platformFee'  => config('stripe.platform_fee', 0.02),
        ]);
    }

    /**
     * Save direct Stripe API keys entered by the tenant.
     * If both are provided, validates the secret key against Stripe before saving.
     */
    public function saveDirectKeys(Request $request)
    {
        $request->validate([
            'publishable_key' => 'required|string|starts_with:pk_',
            'secret_key'      => 'required|string|starts_with:sk_',
        ]);

        // Validate the key actually works before saving
        try {
            $client = new StripeClient($request->secret_key);
            $client->accounts->retrieve(); // Throws if invalid
        } catch (\Stripe\Exception\AuthenticationException $e) {
            return back()->withErrors(['secret_key' => 'Invalid Stripe secret key. Please check and try again.']);
        } catch (\Throwable $e) {
            // Network or other error  allow save but warn
        }

        Setting::set('stripe_publishable_key', $request->publishable_key);
        Setting::set('stripe_secret_key', $request->secret_key);

        // If they switch to direct keys, clear Connect so only one mode is active
        Setting::set('stripe_connect_id', null);
        tenant()->update([
            'stripe_connect_id'           => null,
            'stripe_connect_access_token' => null,
            'stripe_connect_active'       => false,
        ]);

        return back()->with('success', 'Stripe keys saved. Payments will go directly to your Stripe account.');
    }

    /**
     * Remove direct API keys (tenant wants to switch back to Connect or remove Stripe).
     */
    public function removeDirectKeys()
    {
        Setting::set('stripe_publishable_key', null);
        Setting::set('stripe_secret_key', null);

        return back()->with('success', 'Direct Stripe keys removed.');
    }
}
