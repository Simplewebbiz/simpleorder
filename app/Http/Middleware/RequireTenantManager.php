<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class RequireTenantManager
{
    public function handle(Request $request, Closure $next)
    {
        $user = auth('tenant')->user();

        if (! $user || ! $user->isManager()) {
            abort(403, 'Only owners and managers can access this area.');
        }

        return $next($request);
    }
}