<?php 
declare(strict_types=1);
namespace App\Web\SubDomain;
use App\Traits\DataTable;
use App\Core\BaseService;
use App\Traits\HasAttribute;
use App\Http\Services\CommonService;

class SubDomainService extends BaseService
{
    use DataTable,HasAttribute;

    protected array $columns = [
        1 => 'domain',
        2 => 'name',
        3 => 'status'
    ];

    public function getSubDomain()
    {
        [$limit, $order, $dir, $search, $page, $filters] = $this->getDataTableParams();

        $roleId = isset($filters['role_id']) ? $filters['role_id'] : null;
        $status = isset($filters['status']) ? $filters['status'] : null;
        
        $search ??= $this->escape_special_characters($search);

       
        $query = \DB::table('sub_domains as s')
        ->select('s.*', 'at.attribute_value as domain')
        ->join('attribute_values as at', 'at.id', '=', 's.domain_id', 'left');
        
        if ($roleId) 
        {
            $query->where('id', $roleId);
        }

        if (isset($filters['s.status'])) 
        {
            $query->where('s.status', $status);
        }
        else 
        {
            $query->where('s.status', config('constant.ACTIVE'));
        }

        if ($search) 
        {
            $query->where(function($query) use ($search) {
                $query->where('s.name','like', "%$search%")
                        ->orWhere('at.attribute_value','like', "%$search%")
			        ->orWhereRaw($this->datatable_status("s.status"). "LIKE '$search%'");    
            });
        }

        $query->orderBy($order, $dir); 
        
        if ($page) 
        {
            return $this->getDataTableResult(
                SubDomainResource::collection($query->paginate($limit))
            );
        }

        return SubDomainResource::collection($query->get());
    }

    public function getDropdownList()
    {
        $commonService = new CommonService();
        return [
		    'domain'=>$this->listOf('domain'),
            'status' => $commonService->getStatus()
        ];
    }

    public function storeSubDomain(array $payload, ?string $subdomainId = null): bool
    {
        $subdomainData = [
            'domain_id' => $payload['domain_id'],
            'name' => $payload['name'],
            'code' => slugify($payload['name']),
            'description' => $payload['description'] ?? null,
            'status' => $payload['status'],
        ];

        if (! $subdomainId)
        {
            $subdomainData['created_at'] = currentDateTime();
            $subdomainData['created_by'] = AuthId();

            $subdomainData['updated_at'] = currentDateTime();
            $subdomainData['updated_by'] = AuthId();

            return (bool) SubDomain::create($subdomainData);
        }

        $subdomainData['updated_at'] = currentDateTime();
        $subdomainData['updated_by'] = AuthId();

        $subdomain = SubDomain::findOrFail($subdomainId);
        
        return (bool) $subdomain->fill($subdomainData)->save();
    }

    

    public function getSubDomainbyId(string $subdomainId)
    {
        return SubDomain::select('id','domain_id' ,'name', 'status')->find($subdomainId);
    }
}