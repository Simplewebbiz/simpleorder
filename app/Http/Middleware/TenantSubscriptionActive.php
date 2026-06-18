<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class TenantSubscriptionActive
{
    public function handle(Request $request, Closure $next)
    {
        $tenant = tenant();
        if (!$tenant) return $next($request);

        // Block storefront if subscription is canceled (not just past_due — give grace period)
        if ($tenant->subscription_status === 'canceled' && $tenant->subscription_ends_at?->isPast()) {
            return response()->view('errors.subscription-expired', ['tenant' => $tenant], 402);
        }

        return $next($request);
    }
}
