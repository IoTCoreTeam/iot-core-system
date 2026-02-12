<?php

namespace App\Http\Middleware;

use App\Helpers\ApiResponse;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureUserIsAdminOrEngineer
{
    /**
     * Ensure the authenticated user has the admin or engineer role.
     */
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();
        $role = is_string($user?->role) ? strtolower($user->role) : null;

        if (! $user || ! in_array($role, ['admin', 'engineer'], true)) {
            return ApiResponse::error('Forbidden', 403, 'Admin or engineer role required');
        }

        return $next($request);
    }
}
