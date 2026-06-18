<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class RequireSuperAdmin
{
    public function handle(Request $request, Closure $next)
    {
        if (!auth('platform')->user()?->is_super) {
            abort(403);
        }
        return $next($request);
    }
}
