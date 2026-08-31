<?php 
declare(strict_types=1);
namespace App\Http\Api\V1\Department;
use App\Traits\DataTable;
use App\Core\BaseService;

class DepartmentService extends BaseService
{
    protected array $columns = [
        1 => 'name',
        2 => 'status'
    ];

    public function storeDepartment(array $payload, ?string $roleId = null): bool
    {
        $roleData = [
            'name' => $payload['name'],
            'slug' => slugify($payload['name']),
            'description' => $payload['description'] ?? null,
            'status' => $payload['status'],
        ];

        if (! $roleId)
        {
            $roleData['created_at'] = currentDateTime();
            $roleData['created_by'] = AuthId();

            $roleData['updated_at'] = currentDateTime();
            $roleData['updated_by'] = AuthId();

            return (bool) Department::create($roleData);
        }

        $roleData['updated_at'] = currentDateTime();
        $roleData['updated_by'] = AuthId();

        $role = Department::findOrFail($roleId);
        
        return (bool) $role->fill($roleData)->save();
    }

    public function getDepartments()
    {
        [$limit, $order, $dir, $search, $page, $filters] = $this->getDataTableParams();

        $roleId = isset($filters['role_id']) ? $filters['role_id'] : null;
        $status = isset($filters['status']) ? $filters['status'] : null;
        
        $search ??= $this->escape_special_characters($search);

        $query = Department::select('id', 'name', 'status');
        
        if ($roleId) 
        {
            $query->where('id', $roleId);
        }

        if (isset($filters['status'])) 
        {
            $query->where('status', $status);
        }
        else 
        {
            $query->where('status', config('constant.ACTIVE'));
        }

        if ($search) 
        {
            $query->where(function($query) use ($search) {
                $query->where('name','like', "%$search%")
			        ->orWhereRaw($this->datatable_status("status"). "LIKE '$search%'");    
            });
        }

        $query->orderBy($order, $dir); 
        
        if ($page) 
        {
            return $this->getDataTableResult(
                DepartmentResource::collection($query->paginate($limit))
            );
        }

        return DepartmentResource::collection($query->get());
    }

    public function getDepartment(string $productionId)
    {
        return Department::select('id', 'name', 'status')->find($productionId);
    }
}