<?php 
declare(strict_types=1);
namespace App\Http\Api\V1\State;
//use App\Http\Services\ApiService;
use App\Http\Api\V1\State\State as Model;
use App\Http\Services\CommonService;
use DB;
use App\Traits\DataTable;

class StateService
{
	use DataTable;
    protected array $columns = [
        1 => 'country_name',
        2 => 'name',
        3 => 'code',
        4 => 'status',
    ];

    public function getStates()
    {
        [$limit, $order, $dir, $search, $page, $filters] = $this->getDataTableParams();

        
		$query = DB::table('states')
            ->select('states.*', 'countries.name as country_name')
            ->join('countries', 'countries.id', '=', 'states.country_id', 'left');

        if (!empty($filters['country_id']) && $filters['country_id']) {  
            $query->where('country_id', $filters['country_id']);
        }
		if (!empty($filters['state_id']) && $filters['state_id']) {  
            $query->where('states.id', $filters['state_id']);
        }
        if (isset($search) && !empty($search) && $this->escape_special_characters($search)) 
        {
			$query->where('states.name','LIKE',"%$search%")
				->orWhere('countries.name','LIKE',"%$search%")
                ->orWhere('states.code','LIKE',"%$search%")
			    ->orWhereRaw($this->datatable_status("states.status"). "LIKE '%$search%'");
        }

        $query->orderBy($order, $dir); 
        
        if (isset($page) && !empty($page)) 
        {
            return $this->getDataTableResult(
                StateResource::collection($query->paginate($limit))
            );
        }

        return StateResource::collection($query->get());
    }

	public function storeState(array $payload, ?string $stateId= null): bool
    {
        $stateData = [
            'name' => $payload['name'],
            'country_id' => $payload['country_id'] ,
			'code' => $payload['code'],
			'slug' => slugify($payload['name']),
            'status' => $payload['status'],
        ];

        if (! $stateId)
        {
            $stateData['created_at'] = currentDateTime();
            $stateData['created_by'] = AuthId();
            $stateData['updated_at'] = currentDateTime();
            $stateData['updated_by'] = AuthId();

            return (bool) State::create($stateData);
        }

        $stateData['updated_at'] = currentDateTime();
        $stateData['updated_by'] = AuthId();

       $state = State::findOrfail($stateId);
        
       return (bool) $state->fill($stateData)->save();
    }

    /*public function create(array $payload)
    {         
        $payload['id'] = $this->uuid();
        $payload['slug'] = $payload['name'];
        $payload['created_by'] =AuthId();
        $payload['created_at'] = currentDateTime();
        return Model::create($payload);
    }

    public function update(array $payload, string $id)
    {       
        if (
            $this->checkStatusIsDisabled(status: (int) $payload['status']) && 
            $this->checkIfIdExists(
                id: $id, 
                foreignIdConvention: 'state_id', 
                tables: ['addresses']
            )
        )
        {
           return $this->error([], __('message.deactivation_err', ['name' => 'designation']));   
        }
       // $payload = $validator->validated();
        $payload['slug'] = $payload['name'];
        $payload['updated_by'] =AuthId();
        $payload['updated_at'] = currentDateTime();
        $model = Model::findOrFail($id);
        $model->fill($payload)->save();
        return $this->updated();
    }*/

    public function getState(string $id)
    {
        $model = Model::findOrFail($id);
        return StateResource::make($model)->resolve();
    }

    public function getDetails()
    {
        $commonService = new CommonService();
        
        return [
            'countries' => $commonService->getCountries(),
            'states' => $commonService->getStates(),
            'status' => $commonService->getStatus(),
        ];
    }
	
	public function getByCountry($country_id){
        $query = Model::where('country_id',$country_id);
        return StateResource::collection($query->get());  
		/* $commonService = new CommonService();
		return [
            'states' => $commonService->getStates($country_id);
        ]; */
    }
}