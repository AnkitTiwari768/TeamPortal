<?php 

declare(strict_types=1);

namespace App\Http\Api\V1\SubDistrict;

use App\Http\Services\ApiService;
use App\Http\Api\V1\District\SubDistrict as Model;
use App\Http\Services\CommonService;
use DB;

class SubDistrictService extends ApiService 
{
    protected array $columns = [ 
        1 => 'country_name',
        2 => 'state_name',
        3 => 'name',
        4 => 'code',
        5 => 'status',
    ];

    public function getSubDistricts()
    {
        [$limit, $order, $dir, $search, $page, $filters] = $this->getDataTableParams();

        
		$query = DB::table('sub_districts')
            ->select('locations.*', 'countries.name as country_name', 'states.name as state_name')
            ->join('countries', 'countries.id', '=', 'locations.country_id', 'left')
            ->join('states', 'states.id', '=', 'locations.state_id', 'left');

        if (!empty($filters['country_id']) && $filters['country_id']) { 
            $query->where('locations.country_id', $filters['country_id']);
        }

        if (!empty($filters['state_id']) && $filters['state_id']) { 
            $query->where('locations.state_id', $filters['state_id']);
        }
		
		if (!empty($filters['district_id']) && $filters['district_id']) {  
            $query->where('locations.id', $filters['district_id']);
        }

        if (isset($search) && !empty($search) && $this->escape_special_characters($search)) 
        {
			$query->where('locations.name','LIKE',"%$search%")
				->orWhere('countries.name','LIKE',"%$search%")
				->orWhere('states.name','LIKE',"%$search%")
                ->orWhere('locations.code','LIKE',"%$search%")
			    ->orWhereRaw($this->datatable_status("locations.status"). "LIKE '%$search%'");
        }

        $query->orderBy($order, $dir); 
        
        if (isset($page) && !empty($page)) 
        {
            return $this->getDataTableResult(
                DistrictResource::collection($query->paginate($limit))
            );
        }

        return DistrictResource::collection($query->get());
    }
	
	public function storeDistrict(array $payload, ?string $districtId= null): bool
    {
        $districtData = [
            'name' => $payload['name'],
            'country_id' => $payload['country_id'] ,
			'state_id' => $payload['state_id'],
			'code' => $payload['code'],
			'slug' => slugify($payload['name']),
            'status' => $payload['status'],
        ];

        if (! $districtId)
        {
            $districtData['created_at'] = currentDateTime();
            $districtData['created_by'] = AuthId();
            $districtData['updated_at'] = currentDateTime();
            $districtData['updated_by'] = AuthId();

            return (bool) District::create($districtData);
        }

        $districtData['updated_at'] = currentDateTime();
        $districtData['updated_by'] = AuthId();

       $district = District::findOrfail($districtId);
        
       return (bool) $district->fill($districtData)->save();
    }


    
    public function getDistrict(string $id)
    {
        $model = Model::findOrFail($id);
        return DistrictResource::make($model)->resolve();
    }

    public function getDetails()
    {
        $commonService = new CommonService();
        
        return [
            'countries' => $commonService->getCountries(),
            'states' => $commonService->getStates(),
            'districts' => $commonService->getDistricts(),
            'status' => $commonService->getStatus()
        ];
    }
	
	public function getByDistrict($district_id)
    {    
        $districts = DB::table('sub_districts')
            // ->select('sub_districts.*', 'countries.name as country_name', 'states.name as state_name')
            ->select('sub_districts.*')
            ->join('locations', 'locations.id', '=', 'sub_districts.district_id')
            // ->join('states', 'locations.state_id', '=', 'states.id')
            // ->join('countries', 'states.country_id', '=', 'countries.id')
            ->where('sub_districts.district_id',$district_id)
            // ->where('sub_districts.status', true)
            // ->where('sub_districts.slug', '!=', 'delhi')
            // ->where('sub_districts.name', '!=', 'N/A')
            // ->where('sub_districts.slug', '!=', 'n-a')
            ->get();

            //dd($districts);

        return $districts ? SubDistrictResource::collection($districts) : [];
    }
}