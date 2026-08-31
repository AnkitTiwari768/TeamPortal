<?php 

declare(strict_types=1);

namespace App\Http\Api\V1\Module;

use App\Http\Services\ApiService;
use App\Http\Api\V1\Module\Module as Model;
use Illuminate\Http\Request;
use App\Http\Services\CommonService;
use Illuminate\Database\Eloquent\Collection;
use DB;

class ModuleService extends ApiService 
{
    protected array $columns = [
        1 => 'name',
        2 => 'url',
        3 => 'icon',
        4 => 'status',
    ];

    public function getModules()
    {
        [$limit, $order, $dir, $search, $page, $filters] = $this->getDataTableParams();
        
		$query = Model::query();

        if (isset($search) && !empty($search) && $this->escape_special_characters($search))
        {
			$query->where('name','LIKE',"%$search%")
			    ->orWhere('slug','LIKE',"%$search%")
                ->orWhere('url','LIKE',"%$search%")
                ->orWhere('icon','LIKE',"%$search%")
			    ->orWhereRaw($this->datatable_status("status"). "LIKE '%$search%'");
        }

        if (isset($filters['module_id']) && !empty($filters['module_id']))
        {
            $query->where('id', $filters['module_id']);
        }

        $query->orderBy($order, $dir);
        
        if (isset($page) && !empty($page)) 
        {
            return $this->getDataTableResult(
                ModuleResource::collection($query->paginate($limit))
            );
        }

        return ModuleResource::collection($query->get());
    }

    /**
     * Lightweight {id, name} lookup for the Module Name filter dropdown on the Module and
     * Permission list pages. Includes every module regardless of status, matching what
     * those list pages already display.
     */
    public function getModuleNameList(): array
    {
        return Model::select('id', 'name')
            ->orderBy('name')
            ->get()
            ->toArray();
    }

    public function create(array $payload)
    {
		if(!empty($payload['parent_id'])){
			 $payload['parent_id'] = $payload['parent_id'];
		}else{
			 $payload['parent_id'] =NULL;
		}
		
        $payload['id'] = $this->uuid();
        $payload['slug'] = $payload['name'];
        $payload['sort_order'] = $payload['sort_order'];
		$payload['created_by'] =AuthId();
		$payload['created_at'] = currentDateTime();
        return Model::create($payload);
    }

    public function update(array $payload, string $id)
    {
        // dd($payload);
		if (
            $this->checkStatusIsDisabled(status: (int) $payload['status']) && 
            $this->checkIfIdExists(
                id: $id, 
                foreignIdConvention: 'module_id', 
                tables: ['permissions']
            )
        )
        { 
           return $this->error([], __('message.deactivation_err', ['name' => 'module']));   
        }
		
        $payload['slug'] = $payload['name'];
        $payload['sort_order'] = $payload['sort_order'];
        
        if (!empty($payload['parent_id'])) {
            $payload['parent_id'] = $payload['parent_id'];
        } else {
            $payload['parent_id'] = null;
        }

		$payload['updated_by'] =AuthId();
		$payload['updated_at'] = currentDateTime();
        $model = Model::findOrFail($id);
        $model->fill($payload)->save();
        return $this->updated();
    }

    public function findById(string $id)
    {
        $model = Model::findOrFail($id);
        return ModuleResource::make($model)->resolve();
    }
	

	
	public function getDetails()
    {
        $commonService = new CommonService();
        
        return [
            'status' => $commonService->getStatus() 
        ];
    }
	
	
	public function getNestedModules()
    {
       $commonService = new CommonService();
       return $commonService->getNestedModules(); 
    }

    public function getModuleChildren(string $slug): Collection
    {
        return Module::query()
              ->select('m.name', 'm.url', 'm.second_url', 'm.color')
              ->from('modules AS m')
              ->join('modules AS m2', 'm.parent_id', '=', 'm2.id')
              ->where('m2.slug', $slug)
              ->orderBy('m.sort_order', 'asc')
              ->get();
    }
}