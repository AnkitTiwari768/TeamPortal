<?php 

declare(strict_types=1);

namespace App\Web\Country;

use Illuminate\View\View;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

use App\Http\Controllers\ClientController;
use App\Http\Api\V1\Country\{CountryService, CountryRequest};


class CountryController extends ClientController
{
	private static string $module = 'country.index';
	
    public function __construct(private CountryService $countryService) {}
	
	
	public function index(): View
    {
	
		guard(config('permissions.country-view'));
        
        $title = __('message.country_list');
        
		return view('country.index1', compact('title'));  
        
    }

	public function getCountries()
    {
		guard(config('permissions.country-view'));
        
        return $this->success($this->countryService->getCountries());
    }
	
    

    public function create(): View
    {
        guard(config('permissions.country-create'));
		 
        $title = __('message.add_country');
        $module_url = static::$module;

        return view('country.form1', compact('title', 'module_url'));
      
    }


    public function createCountry(Request $request): mixed 
    {   
        guard(config('permissions.country-create'));
		
        $validator = Validator::make($request->all(), CountryRequest::getRules());
        
        if ($validator->fails()) 
        {
            return $this->error($validator->errors());
        }
       
        return $this->created(
            $this->countryService->storeCountry($validator->validated())
        );
        
    }



    public function edit(string $id): View
    {
		guard(config('permissions.country-update'));
		$title = __('message.edit_country');
        $module_url = static::$module;
        
        $row = $this->countryService->getCountry($id);
        return view('country.form1', compact('title', 'module_url', 'row', 'id'));
        
    }


    public function updateCountry(Request $request, string $id): mixed
    {
		guard(config('permissions.country-update'));
        
        $validator = Validator::make($request->all(), CountryRequest::getRules($id));
        
        if ($validator->fails()) 
        {
            return $this->error($validator->errors());
        }
       
        return $this->updated(
            $this->countryService->storeCountry($validator->validated(), $id)
        );
        
    }
	
	


    
}