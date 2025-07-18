<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class RolesMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next, ...$roles): Response
    {
        $user = $request->user();
        if (! $user) {
            abort(403, 'Unauthorized');
        }

        $userRoles = $user->roles()->pluck('name')->toArray();

        if (! array_intersect($userRoles, $roles)) {
            abort(403, 'Unauthorized');
        }

        return $next($request);
    }
}
