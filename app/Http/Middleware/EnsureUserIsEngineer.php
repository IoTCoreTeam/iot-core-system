<?php

namespace App\Http\Middleware;

use App\Helpers\ApiResponse;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureUserIsEngineer
{
    /**
     * Ensure the authenticated user has the engineer role.
     */
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();
        $role = is_string($user?->role) ? strtolower($user->role) : null;

        if (! $user || $role !== 'engineer') {
            return ApiResponse::error('Forbidden', 403, 'Engineer role required');
        }

        return $next($request);
    }
}
