<?php

declare(strict_types=1);

namespace App\Services;

use Illuminate\Support\Facades\DB;

class UserService
{
    public function getUserPermissions(string $userId): array
    {
        $userPermissions = DB::table('users')
            ->select('permissions.slug')
            ->join('user_permissions', 'users.id', '=', 'user_permissions.user_id')
            ->join('permissions', 'user_permissions.permission_id', '=', 'permissions.id')
            ->where('user_permissions.user_id', $userId);

        $rolePermissions = DB::table('role_permissions as rp')
            ->select('p.slug')
            ->join('permissions as p', 'rp.permission_id', '=', 'p.id')
            ->whereIn('rp.role_id', function ($query) use ($userId) {
                $query->select('ur.role_id')
                    ->from('user_roles as ur')
                    ->where('ur.user_id', $userId);
            });

        return $userPermissions
            ->union($rolePermissions)
            ->pluck('slug')
            ->toArray();
    }
}
