<?php

namespace App\Http\Middleware;

use App\Helpers\ApiResponse;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureUserIsAdmin
{
    /**
     * Ensure the authenticated user has the admin role.
     */
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();
        $role = is_string($user?->role) ? strtolower($user->role) : null;

        if (! $user || $role !== 'admin') {
            return ApiResponse::error('Forbidden', 403, 'Admin role required');
        }

        return $next($request);
    }
}
