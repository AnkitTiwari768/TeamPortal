<?php declare (strict_types = 1);

namespace App\Http\Api\V1\RolePermission; 

use App\Http\Api\V1\Role\Role;
use App\Contracts\GrantType;
use App\Http\Services\ApiService;

class RolePermissionService extends ApiService 
{
    public function getRoleName(string $roleId): string|null 
    {
        $role = Role::select('name')->find($roleId);
        
        return $role->name ?? null;
    }

    public function getRolePermissions(string $roleId): array
    {
        $result = \DB::table('role_permissions')
            ->select('permission_id')
            ->where('role_id', $roleId)
            ->get()
            ->toArray();

        return ($result) ? array_column($result, 'permission_id') : [];
    }

    public function getRoleTypeId(string $roleId): ?string
    {
        return Role::where('id', $roleId)->value('role_type_id');
    }

    /**
     * A role's permissions can never exceed what its Role Type allows -- the tree offered by
     * both the Add/Edit Role form and the standalone Role Permission screen is already scoped
     * to the role's Role Type, but this is the server-side guarantee against a tampered or
     * stale request re-introducing a permission the Role Type no longer grants (e.g. removed
     * via Role Type Edit in another tab).
     */
    public function scopeToRoleType(array $permissionIds, ?string $roleTypeId): array
    {
        if (! $roleTypeId) {
            return [];
        }

        $allowed = (new \App\Web\RoleType\SyncRoleTypePermissionsAction)->getAssignedPermissionIds($roleTypeId);

        return array_values(array_intersect($permissionIds, $allowed));
    }

    // public function storeRolePermission(array $payload): bool
    // {
    //     return \DB::transaction(function () use ($payload) {
            
    //         $roleId = $payload['role_id'] ?? null;
    //         $permissions = $payload['permissions'] ?? null;
            
    //         if (!$roleId || !$permissions)
    //         {
    //             return false;
    //         }
            
    //         $rolePermissionData = array_map(
    //             fn(string $permissionId) => ([
    //                 'role_id' => $roleId, 
    //                 'permission_id' => $permissionId
    //             ]), 
    //             $permissions
    //         );

    //         \DB::table('role_permissions')
    //             ->where('role_id', $roleId)
    //             ->delete();

    //         \DB::table('role_permissions')
    //             ->insert($rolePermissionData);

    //         $roleUsers = \DB::table('user_roles')
    //             ->select('user_id')
    //             ->where('role_id', $roleId)
    //             ->get()
    //             ->toArray();
            
    //         if ($roleUsers) 
    //         {
    //             $userPermissionData = [];

    //             foreach ($roleUsers as $user) 
    //             {
    //                 $data = array_map(
    //                     fn($permissionId) => ([
    //                         'user_id' => $user->user_id,
    //                         'permission_id' => $permissionId,
    //                         'type' => GrantType::THROUGH_ROLE
    //                     ]), 
    //                     $permissions
    //                 );

    //                 $userPermissionData = [...$userPermissionData, ...$data];
    //             }

    //             if ($userPermissionData)
    //             {
    //                 $userIds = array_column($userPermissionData, 'user_id');
                    
    //                 \DB::table('user_permissions')
    //                     ->whereIn('user_id', $userIds)
    //                     ->delete();
                    
    //                 \DB::table('user_permissions')
    //                     ->insert($userPermissionData);
    //             } 
    //         }
            
    //         return true;
    //     });
    // }

    // public function storeRolePermission(array $payload): bool
    // {
    //     $maxRetries = 3;
    //     $retryCount = 0;

    //     while ($retryCount < $maxRetries) {
    //         try {
    //             return \DB::transaction(function () use ($payload) {
    //                 $roleId = $payload['role_id'] ?? null;
    //                 $permissions = $payload['permissions'] ?? null;

    //                 if (!$roleId || !$permissions) {
    //                     return false;
    //                 }

    //                 $rolePermissionData = array_map(
    //                     fn(string $permissionId) => ([
    //                         'role_id' => $roleId, 
    //                         'permission_id' => $permissionId
    //                     ]), 
    //                     $permissions
    //                 );

    //                 // Delete existing role permissions
    //                 \DB::table('role_permissions')
    //                     ->where('role_id', $roleId)
    //                     ->delete();

    //                 // Insert new role permissions
    //                 \DB::table('role_permissions')
    //                     ->insert($rolePermissionData);

    //                 // Get all users associated with the role
    //                 $roleUsers = \DB::table('user_roles')
    //                     ->select('user_id')
    //                     ->where('role_id', $roleId)
    //                     ->get()
    //                     ->toArray();

    //                 if ($roleUsers) {
    //                     foreach (array_chunk($roleUsers, 1000) as $userChunk) {
    //                         $userPermissionData = [];
    //                         $userIds = [];

    //                         foreach ($userChunk as $user) {
    //                             $userIds[] = $user->user_id;
    //                             $data = array_map(
    //                                 fn($permissionId) => ([
    //                                     'user_id' => $user->user_id,
    //                                     'permission_id' => $permissionId,
    //                                     'type' => GrantType::THROUGH_ROLE
    //                                 ]), 
    //                                 $permissions
    //                             );

    //                             $userPermissionData = array_merge($userPermissionData, $data);
    //                         }

    //                         if ($userIds) {
    //                             foreach (array_chunk($userIds, 1000) as $userIdsChunk) {
    //                                 \DB::table('user_permissions')
    //                                     ->whereIn('user_id', $userIdsChunk)
    //                                     ->delete();
    //                             }
    //                         }

    //                         if ($userPermissionData) {
    //                             foreach (array_chunk($userPermissionData, 1000) as $chunk) {
    //                                 \DB::table('user_permissions')
    //                                     ->insert($chunk);
    //                             }
    //                         }
    //                     }
    //                 }

    //                 return true;
    //             });

    //         } catch (\Illuminate\Database\QueryException $e) {
    //             if ($e->getCode() == 1205) { // 1205 is the MySQL error code for lock wait timeout
    //                 $retryCount++;
    //                 if ($retryCount >= $maxRetries) {
    //                     throw $e; // If retries are exhausted, rethrow the exception
    //                 }
    //             } else {
    //                 throw $e; // Rethrow any other exceptions
    //             }
    //         }
    //     }

    //     return false;
    // }

    /**
     * Backs the standalone Role Permission screen. An empty $permissions array is honoured as
     * "clear every permission for this role", matching the Add/Edit Role form's behaviour --
     * unchecking every checkbox is a valid submission, not a no-op.
     */
    public function storeRolePermission(array $payload): bool
    {
        $roleId = $payload['role_id'] ?? null;
        $permissions = $payload['permissions'] ?? [];

        if (!$roleId) {
            return false;
        }

        $permissions = $this->scopeToRoleType($permissions, $this->getRoleTypeId($roleId));

        return $this->syncRolePermissions($roleId, $permissions);
    }

    /**
     * Writes exactly $permissions against $roleId and cascades the change to every user
     * holding the role, replacing only their THROUGH_ROLE grants.
     *
     * Unlike storeRolePermission() an empty $permissions array is honoured and clears the
     * role, which is what the Add/Edit Role form needs when the user unchecks everything.
     * Safe to call from inside an outer transaction -- DB::transaction() nests as a
     * savepoint, so a failure here still rolls the caller back.
     */
    public function syncRolePermissions(string $roleId, array $permissions): bool
    {
        $maxRetries = 3;
        $retryCount = 0;

        while ($retryCount < $maxRetries) {
            try {
                return \DB::transaction(function () use ($roleId, $permissions) {

                    // ✅ Step 1: Make permissions unique
                    $permissions = array_values(array_unique(array_filter($permissions)));

                    // ✅ Step 2: Prepare role permission data
                    $rolePermissionData = array_map(function ($permissionId) use ($roleId) {
                        return [
                            'role_id' => $roleId,
                            'permission_id' => $permissionId,
                        ];
                    }, $permissions);

                    // ✅ Step 3: Delete old role permissions
                    \DB::table('role_permissions')
                        ->where('role_id', $roleId)
                        ->delete();

                    // ✅ Step 4: Insert role permissions (safe)
                    if (!empty($rolePermissionData)) {
                        \DB::table('role_permissions')->upsert(
                            $rolePermissionData,
                            ['role_id', 'permission_id']
                        );
                    }

                    // ✅ Step 5: Get users of this role
                    $roleUsers = \DB::table('user_roles')
                        ->where('role_id', $roleId)
                        ->pluck('user_id')
                        ->toArray();

                    if (!empty($roleUsers)) {

                        foreach (array_chunk($roleUsers, 1000) as $userChunk) {

                            $userPermissionData = [];

                            foreach ($userChunk as $userId) {

                                foreach ($permissions as $permissionId) {
                                    $userPermissionData[] = [
                                        'user_id' => $userId,
                                        'permission_id' => $permissionId,
                                        'type' => GrantType::THROUGH_ROLE,
                                    ];
                                }
                            }

                            // ✅ Step 6: Remove only role-based permissions
                            \DB::table('user_permissions')
                                ->whereIn('user_id', $userChunk)
                                ->where('type', GrantType::THROUGH_ROLE)
                                ->delete();

                            // ✅ Step 7: Insert user permissions safely
                            if (!empty($userPermissionData)) {
                                \DB::table('user_permissions')->upsert(
                                    $userPermissionData,
                                    ['user_id', 'permission_id']
                                );
                            }
                        }
                    }

                    return true;
                });

            } catch (\Illuminate\Database\QueryException $e) {

                // ✅ Retry on deadlock / lock wait timeout
                if ($e->getCode() == 1205) {
                    $retryCount++;

                    if ($retryCount >= $maxRetries) {
                        throw $e;
                    }

                    usleep(100000); // small delay before retry
                } else {
                    throw $e;
                }
            }
        }

        return false;
    }


}