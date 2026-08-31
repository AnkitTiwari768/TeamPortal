<?php

declare(strict_types=1);

namespace App\Http\Api\V1\Auth;

use Illuminate\Support\Facades\DB;

class AuthRepository
{
    private function checkIsSubUser(string $userId): array
    {
        $row = DB::table('users')->where('id', $userId)->first();
        $parentUserId = $row->parent_user_id ?? null;
        $isSubUser = $row->is_sub_user ?? false;
        return [$isSubUser, $parentUserId];
    }
  

    public function getAllUserPermissions(string $userId, ?string $roleSlug = null): array
    {
        // Step 1: Determine role slug (session fallback, then priority fallback)
        if ($roleSlug === null) {
            $roleSlug = session('active_role');
        }
    
        if ($roleSlug === null) {
            $userRoleSlugs = DB::table('user_roles')
                ->join('roles', 'user_roles.role_id', '=', 'roles.id')
                ->where('user_roles.user_id', $userId)
                ->pluck('roles.slug')
                ->toArray();

            $roleSlug = app(AuthService::class)->resolveDefaultRole($userRoleSlugs);
            \Log::info('Resolved role slug: ' . $roleSlug);
        }

        // Check if user is sub-user and get parent
        $row = DB::table('users')->select('is_sub_user', 'parent_user_id')->where('id', $userId)->first();
        $isSubUser = $row->is_sub_user ?? false;
        $parentUserId = $row->parent_user_id ?? null;
        $hasParent = !empty($parentUserId);

        // ============================================================
        // CASE 2A: Role Slug Available (Specific role requested via switch or default)
        // ============================================================
        if ($roleSlug !== null) {
            
            // If user has a parent (sub-user), we first check if the parent has the role.
            if ($hasParent) {
                $userAuthPermissions = DB::table('user_permissions as up')
                    ->join('permissions as p', 'up.permission_id', '=', 'p.id')
                    ->where('up.user_id', $userId)
                    ->pluck('p.slug')
                    ->toArray();

                return array_values(array_unique($userAuthPermissions));
            }

            // User has NO parent - Get permissions specifically for this role
            $rolePermissions = DB::table('role_permissions as rp')
                ->select('p.slug')
                ->join('permissions as p', 'rp.permission_id', '=', 'p.id')
                ->whereIn('rp.role_id', function ($query) use ($userId, $roleSlug) {
                    $query->select('ur.role_id')
                        ->from('user_roles as ur')
                        ->join('roles as r', 'ur.role_id', '=', 'r.id')
                        ->where('ur.user_id', $userId)
                        ->where('r.slug', $roleSlug);
                })
                ->pluck('slug')
                ->toArray();

            return array_values(array_unique($rolePermissions));
        }

        // ============================================================
        // CASE 2B: No Role Slug - Fetch all user permissions (Administrator/fallback)
        // ============================================================
        if ($isSubUser) {
            $userAuthPermissions = DB::table('users')
                ->select('permissions.slug')
                ->join('user_permissions', 'users.id', '=', 'user_permissions.user_id')
                ->join('permissions', 'user_permissions.permission_id', '=', 'permissions.id')
                ->where('user_permissions.user_id', $userId)
                ->pluck('slug')
                ->toArray();
            return array_values(array_unique($userAuthPermissions));
        }

        $userPermissions = DB::table('users')
            ->select('permissions.slug')
            ->join('user_permissions', 'users.id', '=', 'user_permissions.user_id')
            ->join('permissions', 'user_permissions.permission_id', '=', 'permissions.id')
            ->where('user_permissions.user_id', $userId);

        if (!$hasParent) {
            $rolePermissions = DB::table('role_permissions as rp')
                ->select('p.slug')
                ->join('permissions as p', 'rp.permission_id', '=', 'p.id')
                ->whereIn('rp.role_id', function ($query) use ($userId) {
                    $query->select('ur.role_id')
                        ->from('user_roles as ur')
                        ->where('ur.user_id', $userId);
                });
            $userPermissions->union($rolePermissions);
        }

        return $userPermissions->pluck('slug')->toArray();
    }
}