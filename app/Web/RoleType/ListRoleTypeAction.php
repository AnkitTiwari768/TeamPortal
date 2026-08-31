<?php

declare(strict_types=1);

namespace App\Web\RoleType;

use App\Traits\DataTable;

class ListRoleTypeAction
{
    use DataTable;

    protected array $columns = [
        1 => 'name',
        2 => 'slug',
        4 => 'status'
    ];

    public function execute()
    {
        [$limit, $order, $dir, $search, $page, $filters] = $this->getDataTableParams();

        $roleTypeId = isset($filters['role_type_id']) ? $filters['role_type_id'] : null;
        $status = isset($filters['status']) ? $filters['status'] : null;

        $search ??= $this->escape_special_characters($search);

        $query = RoleType::select('id', 'name', 'slug', 'status', 'created_at')
            ->selectRaw('(SELECT COUNT(*) FROM role_type_permissions rtp WHERE rtp.role_type_id = role_types.id) AS permission_count');

        if ($roleTypeId) {
            $query->where('id', $roleTypeId);
        }

        if ($status !== null && $status !== '') {
            $query->where('status', (int) $status);
        }

        if ($search) {
            $query->where(function ($query) use ($search) {
                $query->where('name', 'like', "%$search%")
                    ->orWhere('slug', 'like', "%$search%")
                    ->orWhereRaw($this->datatable_status('status') . "LIKE '$search%'");
            });
        }

        $query->orderBy($order, $dir);

        if ($page && $limit > 0) {
            return $this->getDataTableResult(
                RoleTypeResource::collection($query->paginate($limit))
            );
        }

        return RoleTypeResource::collection($query->get());
    }
}
