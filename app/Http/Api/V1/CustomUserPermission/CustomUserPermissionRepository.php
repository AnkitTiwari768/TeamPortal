<?php 

declare(strict_types=1);

namespace App\Http\Api\V1\CustomUserPermission;

use Illuminate\Support\Facades\DB;
use App\Http\Api\V1\CustomUser\Result;

class CustomUserPermissionRepository
{
    public function getCustomPermissions(string $userId): Result
    {
        $data = DB::table('custom_permissions as p')
            ->select('p.id', 'p.name')
            ->selectRaw('EXISTS (
                SELECT 1 
                FROM user_custom_permissions ucp 
                WHERE ucp.user_id = ? 
                AND ucp.custom_permission_id = p.id
            ) as is_selected', [$userId])
            ->get();

        if ($data) {
            return new Result(status: true, data: $data);
        }

        return new Result(status: false, message: "No data found");
    }

    public function storeUserPermissions(CustomUserPermissionDto $permissionDto): Result 
    {
        $userPermissionData = CustomUserPermissionMapper::mapToUserPermissions($permissionDto);

        return DB::transaction(function() use ($userPermissionData, $permissionDto) {
            DB::table('user_custom_permissions')
                ->where('user_id', $permissionDto->userId)
                ->delete();
            
            DB::table('user_custom_permissions')
                ->insert($userPermissionData);

            return new Result(status: true);
        });
    }
}