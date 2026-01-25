<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureTenantAccess
{
    public function handle(Request $request, Closure $next): Response
    {
        $user = auth()->user();

        if (! $user) {
            return redirect()->route('login');
        }

        if (! $user->tenant_id) {
            abort(403, 'No tenant associated with this user.');
        }

        if (! $user->tenant?->is_active) {
            auth()->logout();

            return redirect()->route('login')
                ->withErrors(['email' => 'Your organization account has been deactivated.']);
        }

        if (! $user->is_active) {
            auth()->logout();

            return redirect()->route('login')
                ->withErrors(['email' => 'Your account has been deactivated.']);
        }

        return $next($request);
    }
}
