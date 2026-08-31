<?php 

declare(strict_types=1);

namespace App\Web\District;

use Illuminate\View\View;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

use App\Http\Controllers\ClientController;
use App\Http\Api\V1\District\{DistrictService, DistrictRequest};


class DistrictController extends ClientController
{
	private static string $module = 'district.index';
	
    public function __construct(private DistrictService $districtService) {}
	
	
	public function index(): View
    {
	
		guard(config('permissions.district-view'));
        
        $title = __('Districts List');
		
        $details = $this->districtService->getDetails();
		return view('district.index', compact('title','details'));  
        
    }

	public function getDistricts()
    {
		guard(config('permissions.district-view'));
        
        return $this->success($this->districtService->getDistricts());
    }
	
    

    public function create(): View
    {
        guard(config('permissions.district-create'));
		 
        $title = __('Add Districts');
        $module_url = static::$module;
		//dd($module_url);
		$details = $this->districtService->getDetails();
		
        return view('district.form', compact('title', 'module_url','details'));
      
    }


    public function createDistrict(Request $request): mixed 
    {   
        guard(config('permissions.district-create'));
		
        $validator = Validator::make($request->all(), districtRequest::getRules(),districtRequest::messages());
        
        if ($validator->fails()) 
        {
            return $this->error($validator->errors());
        }
       
        return $this->created(
            $this->districtService->storeDistrict($validator->validated())
        );
        
    }



    public function edit(string $id): View
    {
		guard(config('permissions.district-update'));
		$title = __('Edit City');
        $module_url = static::$module;
        
        $row = $this->districtService->getDistrict($id);
		$details = $this->districtService->getDetails();
        return view('district.form', compact('title', 'module_url', 'row', 'id','details'));
        
    }


    public function updateDistrict(Request $request, string $id): mixed
    {
		guard(config('permissions.district-update'));
        
        $validator = Validator::make($request->all(), DistrictRequest::getRules($id),districtRequest::messages());
        
        if ($validator->fails()) 
        {
            return $this->error($validator->errors());
        }
       
        return $this->updated(
            $this->districtService->storeDistrict($validator->validated(), $id)
        );
        
    }
	
	public function getByState(string $stateId): mixed
    {
        return $this->districtService->getByState($stateId);    
    }
	
	


    
}