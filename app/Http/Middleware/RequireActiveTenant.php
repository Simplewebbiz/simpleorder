<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class RequireActiveTenant
{
    public function handle(Request $request, Closure $next)
    {
        $user = auth('platform')->user();
        if (!$user || !$user->tenant) {
            return redirect()->route('register');
        }
        return $next($request);
    }
}
