<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use Illuminate\Support\Facades\View;
use App\Http\Api\V1\Auth\AuthService;

class ActiveRoleMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        if (auth()->check()) {
            $userRoles = getAuthUserRoles();
            $roleSlugs = array_column($userRoles, 'slug');

            if (empty($roleSlugs)) {
                return $next($request);
            }

            $activeRole = session('active_role');

            // Standardize all assigned slugs to lowercase
            $roleSlugsLower = array_map('strtolower', $roleSlugs);

            // Find matching assigned role (case-insensitive)
            $matchedRole = null;
            if ($activeRole !== null) {
                $index = array_search(strtolower((string) $activeRole), $roleSlugsLower, true);
                if ($index !== false) {
                    $matchedRole = $roleSlugs[$index];
                }
            }

            // If active_role is missing or no longer valid for this user, default to the priority role.
            if ($matchedRole === null) {
                $activeRole = app(AuthService::class)->resolveDefaultRole($roleSlugs);
            } elseif ($activeRole !== $matchedRole) {
                // Standardize active role in session to match the exact case of user role slug
                $activeRole = $matchedRole;
            }

            // Re-resolve permissions from the database on every request (instead of only when
            // the active role changes) so that permission edits made in User Permissions are
            // reflected immediately on the next page load/refresh, without logout/login.
            $permissions = app(AuthService::class)
                ->getUserPermissionsAssigned((string) auth()->id(), $activeRole);

            session([
                'active_role' => $activeRole,
                'permissions' => $permissions,
            ]);

            View::share('activeRole', $activeRole);
            View::share('userRolesList', $userRoles);
        }

        return $next($request);
    }
}
