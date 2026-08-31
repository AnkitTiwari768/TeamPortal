<?php

declare(strict_types=1);

namespace App\Domain\User;

use App\Contracts\GrantType;
use App\Domain\UserLog\UserLogService;
use App\Models\User;
use Illuminate\Support\Facades\DB;

class UpdateUserAction
{
    // Super Admin (administrator) cannot move a user out of, or into, one of these roles.
    private const ADMINISTRATOR_LOCKED_ROLE_SLUGS = ['bnp', 'snp', 'lsp', 'ia-registration', 'msme'];

    public function __construct(
        protected UserService $userService,
        protected UserLogService $userLogService
    ) {}

    public function execute(string $userId, UpdateUserDTO $dto): bool
    {
        return DB::transaction(function () use ($userId, $dto) {

           // $status = $this->userService->processStatus($dto->status);

            $oldRow = (array) (DB::table('users')->where('id', $userId)->first() ?? []);
            $oldRoleIds = DB::table('user_roles')->where('user_id', $userId)->pluck('role_id')->all();

            $userData = [
                'first_name' => $dto->firstName,
                'middle_name' => $dto->middleName,
                'last_name' => $dto->lastName,
                'email' => $dto->email,
                'username' => $dto->email,
                'mobile' => $dto->mobile,
                'status' => $dto->status,
                'parent_user_id' => authId(),
                'updated_at' => now(),
                'updated_by' => authId(),
            ];

            User::where('id', $userId)->update($userData);

            // Map roles if provided
            $roleId = $this->userService->processUserRole($dto->roleId);

            // Super Admin (administrator) role management: a user currently holding a
            // restricted role (BNP/SNP/LSP/IA Registration/MSME) must keep that role, and no
            // user may be moved into one of these roles either -- regardless of what the
            // request submits, so the restriction can't be bypassed by posting directly to
            // this endpoint. Any other role (including roles added later) stays free to change.
            if ($roleId && hasRole('administrator')) {
                $oldRoleIsLocked = ! empty($oldRoleIds) && DB::table('roles')
                    ->whereIn('id', $oldRoleIds)
                    ->whereIn('slug', self::ADMINISTRATOR_LOCKED_ROLE_SLUGS)
                    ->exists();

                $requestedRoleIsLocked = DB::table('roles')
                    ->where('id', $roleId)
                    ->whereIn('slug', self::ADMINISTRATOR_LOCKED_ROLE_SLUGS)
                    ->exists();

                if ($oldRoleIsLocked || $requestedRoleIsLocked) {
                    $roleId = $oldRoleIds[0] ?? null;
                }
            }

            if ($roleId) {
                DB::table('user_roles')->updateOrInsert(
                    ['user_id' => $userId],
                    ['role_id' => $roleId, 'type' => 1]
                );

                // Super Admin (administrator) role management only: keep this user's
                // role-based permissions (type = THROUGH_ROLE) in sync with their current
                // active role -- clear the old role's grants and assign the new role's
                // active permissions, mirroring
                // RolePermissionService::syncRolePermissions()'s delete-then-upsert pattern.
                // Manual per-user overrides (THROUGH_USER / THROUGH_AUXILIARY_ROLE) are
                // untouched, and non-administrator flows (e.g. an SNP/BNP/LSP assigning its
                // own role to a sub-user) are left exactly as before.
                if (hasRole('administrator')) {
                    $rolePermissionIds = DB::table('role_permissions as rp')
                        ->join('permissions as p', 'rp.permission_id', '=', 'p.id')
                        ->where('rp.role_id', $roleId)
                        ->where('p.status', config('constant.ACTIVE'))
                        ->pluck('rp.permission_id')
                        ->unique()
                        ->values()
                        ->all();

                    DB::table('user_permissions')
                        ->where('user_id', $userId)
                        ->where('type', GrantType::THROUGH_ROLE)
                        ->delete();

                    if (! empty($rolePermissionIds)) {
                        DB::table('user_permissions')->upsert(
                            array_map(fn($permissionId) => [
                                'user_id' => $userId,
                                'permission_id' => $permissionId,
                                'type' => GrantType::THROUGH_ROLE,
                            ], $rolePermissionIds),
                            ['user_id', 'permission_id']
                        );
                    }
                }
            }

            $this->userLogService->logUpdated($userId, $oldRow, $userData, authId());
            $this->userLogService->logStatusChanged($userId, $oldRow['status'] ?? null, $userData['status'] ?? null, authId());

            if ($roleId) {
                $this->userLogService->logRoleChanged($userId, $oldRoleIds, [$roleId], authId());
            }

            return true;
        });
    }
}
