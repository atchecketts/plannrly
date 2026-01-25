<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class SetTenantContext
{
    public function handle(Request $request, Closure $next): Response
    {
        if (auth()->check() && auth()->user()->tenant_id) {
            app()->instance('current_tenant_id', auth()->user()->tenant_id);
            app()->instance('current_tenant', auth()->user()->tenant);
        }

        return $next($request);
    }
}
