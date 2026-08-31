<?php 

declare(strict_types=1);

namespace App\Web\Components;

use Illuminate\View\View;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use App\Http\Controllers\ClientController;


class ComponentsController extends ClientController
{
	private static string $module = 'components';
	
    public function __construct(private ComponentsService $service) {}
	
	
	public function index(): View
    {
	
		guard('component-view');
        
        $title = __('Component List');
		
        $details = (object) $this->service->getDetails();
		return view('components-category.index', compact('title','details'));  
        
    }

	public function getComponents()
    {
		guard('component-view');
        
        return $this->success($this->service->getComponents());
    }
	
    

    public function create(): View
    {
        guard('component-create');
		 
        $title = __('Add Component');
        $module_url = static::$module;
		$details = (object) $this->service->getDetails();
		return view('components-category.form', compact('title', 'module_url','details'));
      
    }


    public function createComponents(Request $request): mixed 
    {   
        guard('component-create');
		
        $validator = Validator::make($request->all(), ComponentsRequest::getRules(),ComponentsRequest::messages());
        
        if ($validator->fails()) 
        {
            return $this->error($validator->errors());
        }
       
        return $this->created(
            $this->service->storeComponent($validator->validated())
        );
        
    }



    public function edit(string $id): View
    {
        guard('component-create');
		$title = __('Edit Component');
        $module_url = static::$module;
        
        $row = $this->service->getComponent($id);
		$details = (object) $this->service->getDetails();
        return view('components-category.form', compact('title', 'module_url', 'row', 'id','details'));
        
    }


    public function updateComponents(Request $request, string $id): mixed
    {
		guard('component-create');
        
        $validator = Validator::make($request->all(), ComponentsRequest::getRules($id));
        
        if ($validator->fails()) 
        {
            return $this->error($validator->errors());
        }
       
        return $this->updated(
            $this->service->storeComponent($validator->validated(), $id)
        );
        
    }


    
}