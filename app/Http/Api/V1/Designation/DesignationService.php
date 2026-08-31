<?php 

declare(strict_types=1);
namespace App\Http\Api\V1\Designation;
use App\Core\BaseService;

class DesignationService extends BaseService
{
    protected array $columns = [
        1 => 'name',
        2 => 'status'
    ];

    public function storeDesignation(array $payload, ?string $DesignationId = null): bool
    {
        $DesignationData = [
            'department_id' => $payload['department_id'],
            'name' => $payload['name'],
            'slug' => slugify($payload['name']),
            'description' => $payload['description'] ?? null,
            'status' => $payload['status'],
        ];

        if (! $DesignationId)
        {
            $DesignationData['created_at'] = currentDateTime();
            $DesignationData['created_by'] = AuthId();

            $DesignationData['updated_at'] = currentDateTime();
            $DesignationData['updated_by'] = AuthId();

            return (bool) Designation::create($DesignationData);
        }

        $DesignationData['updated_at'] = currentDateTime();
        $DesignationData['updated_by'] = AuthId();

        $Designation = Designation::findOrFail($DesignationId);
        
        return (bool) $Designation->fill($DesignationData)->save();
    }

    public function getDesignations()
    {
        [$limit, $order, $dir, $search, $page, $filters] = $this->getDataTableParams();

        $DesignationId = isset($filters['Designation_id']) ? $filters['Designation_id'] : null;
        $status = isset($filters['status']) ? $filters['status'] : null;
        
        $search ??= $this->escape_special_characters($search);

        $query = Designation::select('services.id', 'services.name', 'services.status','d.name as department_name')
        ->leftJoin('departments as d', 'services.department_id', '=', 'd.id');
        
        if ($DesignationId) 
        {
            $query->where('services.id', $DesignationId);
        }

        if ($status) 
        {
            $query->where('services.status', $status);
        }

        if ($search) 
        {
            $query->where(function($query) use ($search) {
                $query->where('services.name','like', "%$search%")
                    ->orWhereRaw($this->datatable_status("services.status"). "LIKE '$search%'");    
            });
        }

        $query->orderBy($order, $dir); 
        
        if ($page) 
        {
            return $this->getDataTableResult(
                DesignationResource::collection($query->paginate($limit))
            );
        }

        return DesignationResource::collection($query->get());
    }

    public function getDesignation(string $DesignationId)
    {   
       return Designation::select('*')->where('id',$DesignationId)->first();
        //dd($dd->toSql());
    }
}