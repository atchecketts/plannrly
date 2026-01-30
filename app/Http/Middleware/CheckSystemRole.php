<?php

namespace App\Http\Middleware;

use App\Enums\SystemRole;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class CheckSystemRole
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next, string ...$roles): Response
    {
        $user = auth()->user();

        if (! $user) {
            return redirect()->route('login');
        }

        $requiredRoles = array_map(
            fn ($role) => SystemRole::tryFrom($role),
            $roles
        );

        $requiredRoles = array_filter($requiredRoles);

        foreach ($requiredRoles as $role) {
            if ($user->hasSystemRole($role)) {
                return $next($request);
            }
        }

        abort(403, 'You do not have permission to access this resource.');
    }
}
