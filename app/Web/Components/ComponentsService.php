<?php 
declare(strict_types=1);
namespace App\Web\Components;
use App\Http\Services\CommonService;
use DB;
use App\Traits\DataTable;

class ComponentsService
{
	use DataTable;
    protected array $columns = [
        1 => 'major_component_name',
        2 => 'name',
        3 => 'status',
    ];

    public function getComponents()
    {
        [$limit, $order, $dir, $search, $page, $filters] = $this->getDataTableParams();

        
		$query = DB::table('components')
            ->select('components.*', 'major-components.name as major_component_name')
            ->join('major-components', 'major-components.id', '=', 'components.major_component_id', 'left');

        if (!empty($filters['major_component_id']) && $filters['major_component_id']) {  
            $query->where('major_component_id', $filters['major_component_id']);
        }
		
        if (isset($search) && !empty($search) && $this->escape_special_characters($search)) 
        {
			$query->where('components.name','LIKE',"%$search%")
				->orWhere('major-components.name','LIKE',"%$search%")
			    ->orWhereRaw($this->datatable_status("components.status"). "LIKE '%$search%'");
        }

        $query->orderBy($order, $dir); 
        
        if (isset($page) && !empty($page)) 
        {
            return $this->getDataTableResult(
                ComponentsResource::collection($query->paginate($limit))
            );
        }

        return ComponentsResource::collection($query->get());
    }

	public function storeComponent(array $payload, ?string $stateId= null): bool
    {
        $stateData = [
            'name' => $payload['name'],
            'major_component_id' => $payload['major_component_id'] ,
			'slug' => slugify($payload['name']),
            'status' => $payload['status'],
        ];

        if (! $stateId)
        {
            $stateData['created_at'] = currentDateTime();
            $stateData['created_by'] = AuthId();
            $stateData['updated_at'] = currentDateTime();
            $stateData['updated_by'] = AuthId();

            return (bool) Components::create($stateData);
        }

        $stateData['updated_at'] = currentDateTime();
        $stateData['updated_by'] = AuthId();

       $state = Components::findOrfail($stateId);
        
       return (bool) $state->fill($stateData)->save();
    }

    public function getComponent(string $id)
    {
        $model = Components::findOrFail($id);
        return ComponentsResource::make($model)->resolve();
    }

    public function getDetails()
    {
        $commonService = new CommonService();
        
        return [
            'majorcomponents' => $commonService->getMajorComponents(),
            'components' => $commonService->getComponents(),
            'status' => $commonService->getStatus(),
        ];
    }

}