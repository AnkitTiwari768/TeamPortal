<?php

declare(strict_types=1);

namespace App\Http\Api\V1\User;

use App\Contracts\GrantType;
use App\Domain\EmailTemplate\EmailTemplateService;
use App\Domain\UserLog\UserLogService;
use App\Services\PasswordGenerator;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class SaveUserAction
{
    public function __construct(protected UserLogService $userLogService) {}

    public function execute(UserDTO $dto, ?string $id = null): bool
    {
        return DB::transaction(function () use ($dto, $id) {

            $isCreate = empty($id);
            $userId = $id ?? uuid();

            // Snapshot pre-update state so the change can be logged as an old/new diff.
            $oldRow = [];
            $oldRoleIds = [];
            if (! $isCreate) {
                $oldRow = (array) (DB::table('users')->where('id', $userId)->first() ?? []);
                $oldRoleIds = DB::table('user_roles')->where('user_id', $userId)->pluck('role_id')->all();
            }

            $userData = [
                'id' => $userId,
                'first_name' => $dto->firstName,
                'middle_name' => $dto->middleName,
                'last_name' => $dto->lastName,
                'email' => $dto->email,
                'username' => $dto->email,
                'mobile' => $dto->mobile,
                'department_id' => $dto->departmentId,
                'state_id' => $dto->stateId,
                'status' => $dto->status ?? 1,
                'is_role_mapped' => $dto->isRoleMapped,
                'parent_user_id' => authId(),
            ];

            if ($isCreate) {
                // Administrator creates a top-level user => 0, otherwise the creator's own
                // account (e.g. an SNP user) is spawning a sub-user under itself => 1.
                $userData['is_sub_user'] = hasRole('administrator') ? 0 : 1;
                $userData['created_at'] = now();
                $userData['created_by'] = authId();
            } else {
                $userData['updated_at'] = now();
                $userData['updated_by'] = authId();
            }

            // Password handling
            $generatedPassword = null;
            // dd($isCreate, $dto);
            if ($isCreate) {
                $plainPassword = $dto->password ?? PasswordGenerator::generate();
                $generatedPassword = $plainPassword;
                $userData['password'] = Hash::make($plainPassword);
            } elseif (!empty($dto->password)) {
                $userData['password'] = Hash::make($dto->password);
            }

            // Save user
            $isCreate
                ? User::create($userData)
                : User::where('id', $userId)->update($userData);

            // Roles handling
            DB::table('user_roles')->where('user_id', $userId)->delete();

            // Super Admin (administrator) role management: a user currently holding a
            // restricted role (BNP/SNP/LSP/IA Registration/MSME) must keep that role, and
            // no user may be moved into one of these roles either — regardless of what the
            // request submits, so the restriction can't be bypassed by posting directly to
            // this endpoint. Any other role (including roles added later) is left free to change.
            $administratorLockedRoleSlugs = ['bnp', 'snp', 'lsp', 'ia-registration', 'msme'];
            $roleChangeLockedForAdministrator = false;

            if (! $isCreate && hasRole('administrator')) {
                $oldRoleIsLocked = ! empty($oldRoleIds)
                    && DB::table('roles')->whereIn('id', $oldRoleIds)->whereIn('slug', $administratorLockedRoleSlugs)->exists();

                $requestedRoleIds = $dto->userRoles ? json_decode($dto->userRoles, true) : [];
                $requestedRoleIds = is_array($requestedRoleIds) ? $requestedRoleIds : (is_string($requestedRoleIds) ? [$requestedRoleIds] : []);

                $requestedRoleIsLocked = ! empty($requestedRoleIds)
                    && DB::table('roles')->whereIn('id', $requestedRoleIds)->whereIn('slug', $administratorLockedRoleSlugs)->exists();

                $roleChangeLockedForAdministrator = $oldRoleIsLocked || $requestedRoleIsLocked;
            }

            if ($roleChangeLockedForAdministrator) {
                $roles = $oldRoleIds;
            } elseif (session('active_role') && in_array(session('active_role'), ['snp', 'bnp', 'lsp'])) {
                $activeRole = DB::table('roles')->where('slug', session('active_role'))->value('id');
                $roles = [$activeRole];
            } else {
                $roles = $dto->userRoles
                    ? json_decode($dto->userRoles, true)
                    : ($isCreate ? [authRoleId()] : $oldRoleIds);
            }

            if (is_array($roles)) {
                $rolesData = array_map(fn($roleId) => [
                    'user_id' => $userId,
                    'role_id' => $roleId,
                    'type' => GrantType::THROUGH_ROLE
                ], $roles);
            }

            if (is_string($roles)) {
                $rolesData = [
                    'user_id' => $userId,
                    'role_id' => $roles,
                    'type' => GrantType::THROUGH_ROLE
                ];
            }


            DB::table('user_roles')->insert($rolesData);

            $newRoleIds = is_array($roles) ? $roles : (is_string($roles) ? [$roles] : []);

            // Super Admin (administrator) role management: keep this user's role-based
            // permissions (type = THROUGH_ROLE) in sync with the newly assigned role --
            // clear the old role's grants and assign the new role's permissions, mirroring
            // RolePermissionService::syncRolePermissions()'s delete-then-upsert pattern.
            // Manual per-user overrides (THROUGH_USER / THROUGH_AUXILIARY_ROLE) are untouched.
            if (! $isCreate && hasRole('administrator')) {
                $rolePermissionIds = DB::table('role_permissions')
                    ->whereIn('role_id', $newRoleIds)
                    ->pluck('permission_id')
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

            if ($isCreate) {
                $this->userLogService->logCreated($userId, $userData, authId());
            } else {
                $this->userLogService->logUpdated($userId, $oldRow, $userData, authId());
                $this->userLogService->logStatusChanged($userId, $oldRow['status'] ?? null, $userData['status'] ?? null, authId());
                $this->userLogService->logRoleChanged($userId, $oldRoleIds, $newRoleIds, authId());
            }

            // Email only on create
            if ($isCreate) {
                app(EmailTemplateService::class)->send(
                    templateKey: 'user-registration',
                    toEmail: $dto->email,
                    data: [
                        'name' => $dto->firstName,
                        'username' => $dto->email,
                        'password' => $generatedPassword
                    ]
                );
            }

            return true;
        });
    }
}
