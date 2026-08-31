<?php

declare(strict_types=1);

namespace App\Web\RoleType;

use App\Core\BaseService;
use Illuminate\Support\Facades\DB;

class RoleTypeService extends BaseService
{
    public function __construct(
        private ListRoleTypeAction $listRoleTypeAction,
        private StoreRoleTypeAction $storeRoleTypeAction,
        private GetRoleTypePermissionTreeAction $getRoleTypePermissionTreeAction
    ) {}

    public function getRoleTypes()
    {
        return $this->listRoleTypeAction->execute();
    }

    public function storeRoleType(array $payload, ?string $roleTypeId = null): array
    {
        return $this->storeRoleTypeAction->execute($payload, $roleTypeId);
    }

    public function getPermissionTree(?string $roleTypeId = null): array
    {
        return $this->getRoleTypePermissionTreeAction->execute($roleTypeId);
    }

    public function getRoleType(string $roleTypeId)
    {
        return RoleType::select('id', 'name', 'slug', 'status')->find($roleTypeId);
    }

    public function roleTypeExists(string $roleTypeId): bool
    {
        return RoleType::where('id', $roleTypeId)->exists();
    }

    public function getRoleTypeWithDetails(string $roleTypeId): array
    {
        $roleType = RoleType::select('id', 'name', 'slug', 'status', 'created_at', 'updated_at')
            ->find($roleTypeId);

        if (! $roleType) {
            return [];
        }

        return [
            'id' => $roleType->id,
            'name' => $roleType->name,
            'slug' => $roleType->slug,
            'status' => (int) $roleType->status,
            'created_at' => $roleType->created_at,
            'updated_at' => $roleType->updated_at,
            'permissions' => $this->getRoleTypePermissions($roleTypeId)
        ];
    }

    /**
     * Flat permission list used by the read-only view screen, resolved to plain arrays so
     * the blade can group it without reaching through resource objects.
     */
    public function getRoleTypePermissions(string $roleTypeId): array
    {
        $permissions = DB::table('role_type_permissions AS rtp')
            ->select('p.id', 'p.name', 'p.slug', 'm.name AS module_name')
            ->join('permissions AS p', 'rtp.permission_id', '=', 'p.id')
            ->leftJoin('modules AS m', 'p.module_id', '=', 'm.id')
            ->where('rtp.role_type_id', $roleTypeId)
            ->orderBy('m.name')
            ->orderBy('p.name')
            ->get();

        return RoleTypePermissionResource::collection($permissions)->resolve();
    }
}
