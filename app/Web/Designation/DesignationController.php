<?php 
declare(strict_types=1);
namespace App\Web\Designation;
use Illuminate\View\View;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use App\Http\Controllers\ClientController; 
use App\Http\Api\V1\Designation\DesignationValidation as Validation;
use App\Http\Api\V1\Designation\{DesignationService, DesignationRequest};


class DesignationController extends ClientController
{
    private static string $module = 'services.index';

    public function __construct(private DesignationService $service){}

    public function index(): View
    {
        guard(config('permissions.designation-view'));        
        $title = __('message.designation_list');        
        return view('designation.index1', compact('title'));  
    }

    public function create(): View
    {
        guard(config('permissions.designation-create'));        
        $title = __('message.add_designation');
        $module_url = static::$module;
        return view('designation.form1', compact('title', 'module_url'));
    }

    public function edit(string $id): View
    {
        guard(config('permissions.designation-update'));        
        $title = __('message.edit_designation');
        $module_url = static::$module;        
        $row = $this->service->getDesignation($id);
        //dd($row);
        return view('designation.form1', compact('title', 'module_url', 'row', 'id'));
            
    }

    public function getDesignations()
    {
        guard(config('permissions.designation-view'));
        
        return $this->success($this->service->getDesignations());
    }
    
    public function createDesignation(Request $request) 
    {   
        guard(config('permissions.designation-create'));
        
        $validator = Validator::make($request->all(), DesignationRequest::getRules());
        
        if ($validator->fails()) 
        {
            return $this->error($validator->errors());
        }
       
        return $this->created(
            $this->service->storeDesignation($validator->validated())
        );
    }

    public function updateDesignation(Request $request, string $id) 
    {   
        guard(config('permissions.designation-update'));
        
        $validator = Validator::make($request->all(), DesignationRequest::getRules($id));
        
        if ($validator->fails()) 
        {
            return $this->error($validator->errors());
        }
       
        return $this->updated(
            $this->service->storeDesignation($validator->validated(), $id)
        );
    }
}