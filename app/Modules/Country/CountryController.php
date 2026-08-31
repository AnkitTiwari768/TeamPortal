<?php 
declare(strict_types=1);
namespace App\Modules\Country;
use App\Http\Api\V1\Country\CountryService; 
use App\Http\Controllers\ClientController;
use App\Http\Api\V1\Country\CountryValidation as Validation;
use Illuminate\Http\Request;
use Illuminate\View\View;

final class CountryController extends ClientController
{
    private static $module = 'countries.index';
    
    public function __construct(private CountryService $service)
    {
        $this->service = $service;
    }

    public function index(): View
    {
        guard(config('permissions.country-view'));
        return view('country.index')
            ->with('title', __('message.country_list'));
    }

    public function datalist(): mixed
    {   
        guard(config('permissions.country-view'));
        try{
            $result=$this->service->getCountries(); 
            return $this->success($result);
        }
        catch(\Throwable $e){
            return $this->handleException($e);
        }            
    }

    public function create(): View
    { 
        guard(config('permissions.country-create'));
        return view('country.form')
            ->with('title', __('message.add_country'))
            ->with('module_url', self::$module)
			->with('details', $this->service->getDetails());
    }

    public function store(Request $request): mixed 
    {   
        guard(config('permissions.country-create'));
        try{ 
            $validator = Validation::getRules($request->all());            
            if ($validator->fails()) 
            {
                return $this->error($validator->errors());
            }

            $result= $this->service->create($validator->validated());
            return $this->created($result);
        }
        catch (\Throwable $e) 
        {
            return $this->handleException($e);
        }
    }

    public function edit(string $id): View
    {
        guard(config('permissions.country-update'));
        return view('country.form')
            ->with('title', __('message.edit_country'))
            ->with('module_url', self::$module)
            ->with('id', $id)
            ->with('row', $this->service->findById($id))
			->with('details', $this->service->getDetails());
    }

    public function update(Request $request, string $id): mixed
    {   
        guard(config('permissions.country-update'));
        try{      
            $validator = Validation::getRules($request->all(), $id);
            
            if ($validator->fails()) 
            {
                return $this->error($validator->errors());
            }       
           return $result= $this->service->update($validator->validated(), $id);
            //return $this->updated();
        }catch(\Throwable $e){
            return $this->handleException($e);
        }
    }

	
	public function getCountryName(string $countryId): mixed
    {
        return $this->service->getCountryName($countryId);    
    }
}