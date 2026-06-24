<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class RequireActiveTenant
{
    public function handle(Request $request, Closure $next)
    {
        $user = auth('platform')->user();
        $tenant = $user?->tenant;

        if (! $tenant) {
            return redirect()->route('register');
        }

        if (! $tenant->is_active) {
            auth('platform')->logout();
            return redirect()->route('login')->withErrors(['email' => 'This account is not active.']);
        }

        return $next($request);
    }
}