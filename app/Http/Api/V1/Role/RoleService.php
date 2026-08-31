<?php

declare(strict_types=1);

namespace App\Http\Api\V1\Role;

use App\Core\BaseService;
use App\Http\Api\V1\RolePermission\RolePermissionService;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Schema;

class RoleService extends BaseService
{
    protected array $columns = [
        1 => 'name',
        2 => 'status'
    ];

    /**
     * Saves the role and, when the form posted the permission tree, its permission set in a
     * single transaction so a failure part-way cannot leave a role without its permissions.
     *
     * Permissions are written through RolePermissionService::syncRolePermissions() -- the
     * same code the standalone Role Permission screen uses -- rather than a second
     * implementation, so the user_permissions cascade stays in one place.
     *
     * `permissions_submitted` marks that the Add/Edit Role form actually rendered the
     * permission tree. Without it an all-unchecked submit is indistinguishable from a
     * caller that never showed the section (or a role saved with no role type selected),
     * and the role's existing permissions would be wiped silently.
     */
    public function storeRole(array $payload, ?string $roleId = null): array
    {
        $syncPermissions = (bool) ($payload['permissions_submitted'] ?? false);
        $permissions = $payload['permissions'] ?? [];

        $roleData = [
            'name' => $payload['name'],
            'department_id' => $payload['department'] ?? null,
            'role_type_id' => $payload['role_type'] ?? null,
            'slug' => slugify($payload['name']),
            'description' => $payload['description'] ?? null,
            'status' => $payload['status'],
        ];

        // Checked before opening the transaction: this is a rejection, not a failure.
        if ($roleId) {
            $existing = Role::find($roleId);

            if (! $existing) {
                return [
                    'status' => false,
                    'error' => 'Invalid role selected!'
                ];
            }

            if ($existing->department_id !== $roleData['department_id']) {
                $count = \DB::table('user_roles')->where('role_id', $roleId)->count();

                if ($count > 0) {
                    return [
                        'status' => false,
                        'error' => 'Could not update department becuase role is already mapped to user(s)!'
                    ];
                }
            }
        }

        try {
            return \DB::transaction(function () use ($roleData, $roleId, $syncPermissions, $permissions) {

                if (! $roleId) {
                    $roleData['created_at'] = currentDateTime();
                    $roleData['created_by'] = AuthId();

                    $roleData['updated_at'] = currentDateTime();
                    $roleData['updated_by'] = AuthId();

                    $role = Role::create($roleData);
                    $roleId = (string) $role->id;

                    //$this->assignDepartmentPermissions($role->id, $roleData['department_id']);
                } else {
                    $roleData['updated_at'] = currentDateTime();
                    $roleData['updated_by'] = AuthId();

                    $role = Role::findOrFail($roleId);
                    $role->fill($roleData)->save();

                    //$this->assignDepartmentPermissions($roleId, $roleData['department_id']);
                }

                if ($syncPermissions) {
                    app(RolePermissionService::class)->syncRolePermissions($roleId, $permissions);
                }

                return [
                    'status' => true,
                    'data' => true
                ];
            });
        } catch (\Throwable $e) {
            \Log::error('Role could not be saved: ' . $e->getMessage());

            return [
                'status' => false,
                'error' => 'Could not save the role. Please try again.'
            ];
        }
    }

    public function assignDepartmentPermissions($roleId, $departmentId)
    {
        $departmetPermissions = \DB::table('department_permissions')
            ->select('permission_id')
            ->where('department_id', $departmentId)
            ->get()
            ->toArray();

        if ($departmetPermissions) {
            $departmetPermissions = array_column($departmetPermissions, 'permission_id');

            $data = [];

            foreach ($departmetPermissions as $permissionId) {
                $data[] = [
                    'permission_id' => $permissionId,
                    'role_id' => $roleId
                ];
            }

            \DB::table('permission_role_mapping')->where('role_id', $roleId)->delete();
            \DB::table('permission_role_mapping')->insert($data);
        }
    }

    public function getRoles()
    {
        [$limit, $order, $dir, $search, $page, $filters] = $this->getDataTableParams();

        $roleId = isset($filters['role_id']) ? $filters['role_id'] : null;
        $status = isset($filters['status']) ? $filters['status'] : null;

        $search ??= $this->escape_special_characters($search);

        $query = Role::select('id', 'name', 'status')
            ->where('status', 1)
            ->selectRaw('(SELECT COUNT(*) FROM role_permissions rp WHERE rp.role_id = roles.id) as permission_count');

        if ($roleId) {
            $query->where('id', $roleId);
        }

        if ($search) {
            $query->where(function ($query) use ($search) {
                $query->where('name', 'like', "%$search%")
                    ->orWhereRaw($this->datatable_status("status") . "LIKE '$search%'");
            });
        }

        $query->orderBy($order, $dir);

        if ($page && $limit > 0) {
            return $this->getDataTableResult(
                RoleResource::collection($query->paginate($limit))
            );
        }

        return RoleResource::collection($query->get());
    }

    public function getRole(string $roleId)
    {
        return Role::select('id', 'name', 'department_id AS department', 'role_type_id AS role_type', 'description', 'status')->find($roleId);
    }
  public function getRoleWithDetails(string $id): array
{
    try {
        // Check if users table has first_name and last_name
        $hasFirstName = Schema::hasColumn('users', 'first_name');
        $hasLastName = Schema::hasColumn('users', 'last_name');
        
        $selectFields = [
            'r.id',
            'r.name',
            'r.description',
            'r.status',
            'r.created_at',
            'r.created_by',
            'r.department_id',
            'r.role_type_id',
            'r.updated_at',
            'd.name as department_name',
            'rt.name as role_type_name',
            'rt.slug as role_type_slug'
        ];
        
        if ($hasFirstName && $hasLastName) {
            $selectFields[] = DB::raw("CONCAT(u.first_name, ' ', u.last_name) as created_by_name");
        } else {
            $selectFields[] = 'u.name as created_by_name';
            $selectFields[] = 'u.email as created_by_email';
        }
        
        $role = DB::table('roles as r')
            ->leftJoin('users as u', 'r.created_by', '=', 'u.id')
            ->leftJoin('departments as d', 'r.department_id', '=', 'd.id')
            ->leftJoin('role_types as rt', 'r.role_type_id', '=', 'rt.id')
            ->where('r.id', $id)
            ->select($selectFields)
            ->first();

        if (!$role) {
            return [];
        }

        return [
            'id' => $role->id,
            'name' => $role->name,
            'description' => $role->description ?? '',
            'status' => $role->status,
            'created_at' => $role->created_at,
            'updated_at' => $role->updated_at ?? null,
            'created_by' => $role->created_by,
            'created_by_name' => $role->created_by_name ?? $role->created_by ?? $role->created_by_email ?? '',
            'department_id' => $role->department_id,
            'department_name' => $role->department_name ?? '',
            'role_type_id' => $role->role_type_id ?? null,
            'role_type_name' => $role->role_type_name ?? '',
            'role_type_slug' => $role->role_type_slug ?? '',
            'permissions' => $this->getAssignedPermissions($id)
        ];
    } catch (\Exception $e) {
        Log::error('Error fetching role details: ' . $e->getMessage());
        return [];
    }
}

    /**
     * Permissions currently granted to the role, grouped-ready for the view screen.
     */
    public function getAssignedPermissions(string $roleId): array
    {
        return DB::table('role_permissions as rp')
            ->select('p.id', 'p.name', 'p.slug', 'm.name as module_name')
            ->join('permissions as p', 'p.id', '=', 'rp.permission_id')
            ->leftJoin('modules as m', 'm.id', '=', 'p.module_id')
            ->where('rp.role_id', $roleId)
            ->orderBy('m.name')
            ->orderBy('p.name')
            ->get()
            ->map(fn($row) => (array) $row)
            ->toArray();
    }
}
