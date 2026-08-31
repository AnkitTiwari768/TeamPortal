<?php 

declare(strict_types=1);

namespace App\Http\Api\V1\Country;

//use App\Http\Services\ApiService;
//use App\Http\Api\V1\Country\Country as Model;
//use App\Http\Services\CommonService;
//use DB;
use App\Traits\DataTable;

class CountryService
{

	use DataTable;
    protected array $columns = [
        1 => 'name',
        2 => 'iso2_code',
        3 => 'iso3_code',
        4 => 'status',
    ];

	public function storeCountry(array $payload, ?string $countryId= null): bool
    {
        $countryData = [
            'name' => $payload['name'],
			'country_code' => $payload['country_code'] ,
            'iso2_code' => $payload['iso2_code'] ,
			'iso3_code' => $payload['iso3_code'] ,
			'slug' => slugify($payload['name']),
            'status' => $payload['status'],
        ];

        if (! $countryId)
        {
            $countryData['created_at'] = currentDateTime();
            $countryData['created_by'] = AuthId();
            $countryData['updated_at'] = currentDateTime();
            $countryData['updated_by'] = AuthId();

            return (bool) Country::create($countryData);
        }

        $countryData['updated_at'] = currentDateTime();
        $countryData['updated_by'] = AuthId();

       $country = Country::findOrfail($countryId);
        
       return (bool) $country->fill($countryData)->save();
    }

    public function getCountries()
    {
        [$limit, $order, $dir, $search, $page, $filters] = $this->getDataTableParams();

        $iso2_code= isset($filters['iso2_code']) ? $filters['iso2_code'] : null;
		$iso3_code= isset($filters['iso3_code']) ? $filters['iso3_code'] : null;
        $status = isset($filters['status']) ? $filters['status'] : null;
        
        $search ??= $this->escape_special_characters($search);

        $query = Country::select('country_code','iso2_code','iso3_code', 'name', 'status','id');
        
        if ($iso2_code) 
        {
            $query->where('iso2_code', $iso2_code);
        }
		if ($iso3_code) 
        {
            $query->where('iso3_code', $iso3_code);
        }

        if ($status) 
        {
            $query->where('status', $status);
        }

        if ($search) 
        {
            $query->where(function($query) use ($search) {
                $query->where('name','like', "%$search%")
					  ->orWhere('iso2_code','like', "%$search%")
					  ->orWhere('iso3_code','like', "%$search%")
			          ->orWhereRaw($this->datatable_status("status"). "LIKE '$search%'");    
            });
        }

        $query->orderBy($order, $dir); 
        
        if ($page) 
        {
            return $this->getDataTableResult(
                CountryResource::collection($query->paginate($limit))
            );
        }

        return CountryResource::collection($query->get());
    }

   
	public function getCountry(string $countryId)
    {
        return Country::select('iso2_code','iso3_code', 'name', 'status')->where('id',$countryId)->first();
    }

    public function getIsdCodes()
    {
        $countries = Country::select('country_code')
                            ->where('status', config('constant.ACTIVE'))
                            ->whereNotNull('country_code')
                            ->groupBy('country_code')
                            ->orderBy('country_code', 'asc')
                            ->get();
                            
        $data = [];

        if ($countries) 
        {   
            foreach ($countries as $country)
            {
                $data[$country->country_code] = $country->country_code;
            }    
        }

        return $data;
    }

}