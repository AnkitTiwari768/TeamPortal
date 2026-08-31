<?php 

declare(strict_types=1);

namespace App\Web\SubComponents;

use Illuminate\View\View;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use App\Http\Controllers\ClientController;


class SubComponentsController extends ClientController
{
	private static string $module = 'sub-components';
	
    public function __construct(private SubComponentsService $service) {}
	
	
	public function index(): View
    {
	
		guard('sub-component-view');        
        $title = __('Sub Components List');
        $details = (object) $this->service->getDetails();
		return view('sub_components.index', compact('title','details'));  
        
    }

	public function getSubComponents()
    {
		guard('sub-component-view');           
        return $this->success($this->service->getSubComponents());
    }
	
    

    public function create(): View
    {
        guard('sub-component-create');    
        $title = __('Add Sub Component');
        $module_url = static::$module;
		$details = (object) $this->service->getDetails();
        return view('sub_components.form', compact('title', 'module_url','details'));
      
    }


    public function createSubComponents(Request $request): mixed 
    {   
        guard('sub-component-create');    
		
        $validator = Validator::make($request->all(), SubComponentsRequest::getRules(),SubComponentsRequest::messages());
        
        if ($validator->fails()) 
        {
            return $this->error($validator->errors());
        }
       
        return $this->created(
            $this->service->storeSubComponents($validator->validated())
        );
        
    }



    public function edit(string $id): View
    {
		guard('sub-component-create');    
		$title = __('Edit Sub Component');
        $module_url = static::$module;
        
        $row = $this->service->getSubComponent($id);
		$details = (object) $this->service->getDetails();
        return view('sub_components.form', compact('title', 'module_url', 'row', 'id','details'));
        
    }


    public function updateSubComponents(Request $request, string $id): mixed
    {
		guard('sub-component-create');    
        
        $validator = Validator::make($request->all(), SubComponentsRequest::getRules($id),SubComponentsRequest::messages());
        
        if ($validator->fails()) 
        {
            return $this->error($validator->errors());
        }
       
        return $this->updated(
            $this->service->storeSubComponents($validator->validated(), $id)
        );
        
    }

    public function getComponent(string $majorComponentID): mixed
    {
        return $this->service->getComponent($majorComponentID);    
    }	

    public function getSubComponentByComponent(string $componentID): mixed
    {
        return $this->service->getSubComponentByComponent($componentID);    
    }	
    
}