<?php 

declare(strict_types=1);

namespace App\Web\MajorComponents;

use Illuminate\View\View;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use App\Http\Controllers\ClientController;


class MajorComponentsController extends ClientController
{
	private static string $module = 'major-components';
	
    public function __construct(private MajorComponentsService $service) {}
	
	
	public function index(): View
    {
	
		guard('major-component-view');
        
        $title = __('Major Component List');
        
		return view('majorcomponents.index', compact('title'));  
        
    }

	public function getMajorComponents()
    {
		guard('major-component-view');
        
        return $this->success($this->service->getMajorComponents());
    }
	
    

    public function create(): View
    {
        guard('major-component-create');
		 
        $title = __('Add Major Component');
        $module_url = static::$module;

        return view('majorcomponents.form', compact('title', 'module_url'));
      
    }


    public function createMajorComponents(Request $request): mixed 
    {   
        guard('major-component-create');
		
        $validator = Validator::make($request->all(), MajorComponentsRequest::getRules());
        
        if ($validator->fails()) 
        {
            return $this->error($validator->errors());
        }
       
        return $this->created(
            $this->service->storeMajorComponent($validator->validated())
        );
        
    }



    public function edit(string $id): View
    {
		guard('major-component-create');
		$title = __('Edit Major Component List');
        $module_url = static::$module;
        
        $row = $this->service->getMajorComponent($id);
        return view('majorcomponents.form', compact('title', 'module_url', 'row', 'id'));
        
    }


    public function updateMajorComponents(Request $request, string $id): mixed
    {
		guard('major-component-create');
        
        $validator = Validator::make($request->all(), MajorComponentsRequest::getRules($id));
        
        if ($validator->fails()) 
        {
            return $this->error($validator->errors());
        }
       
        return $this->updated(
            $this->service->storeMajorComponent($validator->validated(), $id)
        );
        
    }
	
	


    
}