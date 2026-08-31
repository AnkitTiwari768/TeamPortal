<?php 
declare(strict_types=1);
namespace App\Web\SubDistrict;

use App\Http\Controllers\ClientController;
use App\Http\Api\V1\SubDistrict\SubDistrictService; 
use App\Http\Api\V1\SubDistrict\SubDistrictValidation as Validation;
use Illuminate\Http\Request;
use Illuminate\View\View;

class SubDistrictController extends ClientController
{
    private static $module = 'sub_district.index';
    
    public function __construct(private SubDistrictService $service)
    {
        $this->service = $service;
    }

    public function index(): View
    {
        //guard(config('permissions.district-view'));
        return view('district.index')
            ->with('title', __('message.district_list'))            
            ->with('details', $this->service->getDetails());
    }

    public function datalist(): mixed
    {   
        //guard(config('permissions.district-view'));
        try{            
            $result= $this->service->getDistricts();
            return $this->success($result);
        }catch(\Throwable $e){
            return $this->handleException($e);
        }    
    }

    public function create(): View
    {
        //guard(config('permissions.district-create'));
        return view('district.form')
            ->with('title', __('message.add_district'))
            ->with('module_url', self::$module)
            ->with('details', $this->service->getDetails());
    }

    public function store(Request $request): mixed 
    {   
        //guard(config('permissions.district-create'));
        try{     
            $validator = Validation::getRules($request->all());
            
            if ($validator->fails()) 
            {
                return $this->error($validator->errors());
            }       
            $result = $this->service->create($validator->validated());
            return $this->created($result);
        }catch(\Throwable $e){
            return $this->handleException($e);
        }
    }

    public function edit(string $id): View
    {
        //guard(config('permissions.district-update'));
        return view('district.form')
            ->with('title', __('message.edit_district'))
            ->with('module_url', self::$module)
            ->with('id', $id)
            ->with('row', $this->service->findById($id))
            ->with('details', $this->service->getDetails());
    }

    public function update(Request $request, string $id): mixed
    {
        //guard(config('permissions.district-update'));
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
	
	public function getByDistrict(string $Id): mixed
    {
        return $this->service->getByDistrict($Id);    
    }
}