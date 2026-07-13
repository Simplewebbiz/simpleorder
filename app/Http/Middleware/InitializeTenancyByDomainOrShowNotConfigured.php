<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Stancl\Tenancy\Exceptions\TenantCouldNotBeIdentifiedOnDomainException;
use Stancl\Tenancy\Middleware\InitializeTenancyByDomain;
use Symfony\Component\HttpFoundation\Response;

class InitializeTenancyByDomainOrShowNotConfigured
{
    public function __construct(private readonly InitializeTenancyByDomain $initializeTenancy)
    {
    }

    public function handle(Request $request, Closure $next): Response
    {
        try {
            return $this->initializeTenancy->handle($request, $next);
        } catch (TenantCouldNotBeIdentifiedOnDomainException) {
            if ($request->expectsJson()) {
                return response()->json([
                    'message' => 'No storefront is configured for this domain.',
                ], 404);
            }

            return response()->view('errors.tenant-not-found', [
                'domain' => $request->getHost(),
                'platformUrl' => config('app.url'),
            ], 404);
        }
    }
}