<?php

declare(strict_types=1);

namespace App\Http\Api\V1\UserPermission;

use Illuminate\Support\Facades\DB;

class CreateOrUpdateUserPermissionAction
{
    public function execute(UserPermissionDTO $dto): bool
    {
        return DB::transaction(function () use ($dto) {

            $userId = $dto->userId;
            $permissions = $dto->userPermissions ?? [];

            DB::table('user_permissions')
                ->where('user_id', $userId)
                ->delete();

            if (!empty($permissions)) {

                $userPermissionsData = array_map(
                    fn ($permissionId) => [
                        'user_id' => $userId,
                        'permission_id' => $permissionId,
                        'type' => 2,
                    ],
                    $permissions
                );

                DB::table('user_permissions')
                    ->insert($userPermissionsData);
            }

            return true;
        });
    }
}
