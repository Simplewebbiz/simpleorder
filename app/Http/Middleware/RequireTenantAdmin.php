<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class RequireTenantAdmin
{
    public function handle(Request $request, Closure $next)
    {
        $user = auth('tenant')->user();

        if (!$user || $user->isCustomer()) {
            return redirect()->route('tenant.admin.login');
        }

        return $next($request);
    }
}
