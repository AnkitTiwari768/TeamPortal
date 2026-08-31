<?php 

declare(strict_types=1);

namespace App\Web\SubComponents;
use App\Http\Services\ApiService;
use App\Http\Services\CommonService;
use DB;

class SubComponentsService extends ApiService 
{
    protected array $columns = [ 
        1 => 'major_component_name',
        2 => 'component_name',
        3 => 'name',
        4 => 'status',
    ];

    public function getSubComponents()
    {
        [$limit, $order, $dir, $search, $page, $filters] = $this->getDataTableParams();

        
		$query = DB::table('sub-components')
            ->select('sub-components.*', 'major-components.name as major_component_name', 'components.name as component_name')
            ->join('major-components', 'major-components.id', '=', 'sub-components.major_component_id', 'left')
            ->join('components', 'components.id', '=', 'sub-components.component_id', 'left');

        if (!empty($filters['major_component_id']) && $filters['major_component_id']) { 
            $query->where('sub-components.major_component_id', $filters['major_component_id']);
        }

        if (!empty($filters['component_id']) && $filters['component_id']) { 
            $query->where('sub-components.component_id', $filters['component_id']);
        }
		
        if (isset($search) && !empty($search) && $this->escape_special_characters($search)) 
        {
			$query->where('sub-components.name','LIKE',"%$search%")
				->orWhere('major-components.name','LIKE',"%$search%")
				->orWhere('components.name','LIKE',"%$search%")
			    ->orWhereRaw($this->datatable_status("sub-components.status"). "LIKE '%$search%'");
        }

        $query->orderBy($order, $dir); 
        
        if (isset($page) && !empty($page)) 
        {
            return $this->getDataTableResult(
                SubComponentsResource::collection($query->paginate($limit))
            );
        }

        return SubComponentsResource::collection($query->get());
    }
	
	public function storeSubComponents(array $payload, ?string $districtId= null): bool
    {
        $districtData = [
            'name' => $payload['name'],
            'major_component_id' => $payload['major_component_id'] ,
			'component_id' => $payload['component_id'],
			'slug' => slugify($payload['name']),
            'status' => $payload['status'],
        ];

        if (! $districtId)
        {
            $districtData['created_at'] = currentDateTime();
            $districtData['created_by'] = AuthId();
            $districtData['updated_at'] = currentDateTime();
            $districtData['updated_by'] = AuthId();

            return (bool) SubComponents::create($districtData);
        }

        $districtData['updated_at'] = currentDateTime();
        $districtData['updated_by'] = AuthId();

       $district = SubComponents::findOrfail($districtId);
        
       return (bool) $district->fill($districtData)->save();
    }


    
    public function getSubComponent(string $id)
    {
        $model = SubComponents::findOrFail($id);
        return SubComponentsResource::make($model)->resolve();
    }

    public function getDetails()
    {
        $commonService = new CommonService();
        
        return [
            'majorcomponents' => $commonService->getMajorComponents(),
            'components' => $commonService->getComponents(),
            'status' => $commonService->getStatus()
        ];
    }

    public function getComponent($major_component_id){
        return \DB::table('components')->select('id','name')->where('major_component_id',$major_component_id)->get();
    }

    public function getSubComponentByComponent($component_id){
        return \DB::table('sub-components')->select('id','name')->where('component_id',$component_id)->orderBy('name')->get();
    }
	
}