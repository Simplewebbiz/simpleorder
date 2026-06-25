<?php

namespace App\Http\Middleware;

use App\Models\PlatformSetting;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class LoadPlatformSettings
{
    public function handle(Request $request, Closure $next): Response
    {
        PlatformSetting::applyToConfig();

        return $next($request);
    }
}
