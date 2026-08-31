<?php 

declare(strict_types=1);

namespace App\Web\State;

use Illuminate\View\View;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

use App\Http\Controllers\ClientController;
use App\Http\Api\V1\State\{StateService, StateRequest};


class StateController extends ClientController
{
	private static string $module = 'state.index';
	
    public function __construct(private StateService $stateService) {}
	
	
	public function index(): View
    {
	
		guard(config('permissions.state-view'));
        
        $title = __('message.state_list');
		
        $details = $this->stateService->getDetails();
		return view('state.index', compact('title','details'));  
        
    }

	public function getStates()
    {
		guard(config('permissions.state-view'));
        
        return $this->success($this->stateService->getStates());
    }
	
    

    public function create(): View
    {
        guard(config('permissions.state-create'));
		 
        $title = __('message.add_state');
        $module_url = static::$module;
		$details = $this->stateService->getDetails();
		return view('state.form1', compact('title', 'module_url','details'));
      
    }


    public function createState(Request $request): mixed 
    {   
        guard(config('permissions.state-create'));
		
        $validator = Validator::make($request->all(), stateRequest::getRules(),stateRequest::messages());
        
        if ($validator->fails()) 
        {
            return $this->error($validator->errors());
        }
       
        return $this->created(
            $this->stateService->storeState($validator->validated())
        );
        
    }



    public function edit(string $id): View
    {
		guard(config('permissions.state-update'));
		$title = __('message.edit_state');
        $module_url = static::$module;
        
        $row = $this->stateService->getState($id);
		$details = $this->stateService->getDetails();
        return view('state.form1', compact('title', 'module_url', 'row', 'id','details'));
        
    }


    public function updateState(Request $request, string $id): mixed
    {
		guard(config('permissions.state-update'));
        
        $validator = Validator::make($request->all(), StateRequest::getRules($id));
        
        if ($validator->fails()) 
        {
            return $this->error($validator->errors());
        }
       
        return $this->updated(
            $this->stateService->storeState($validator->validated(), $id)
        );
        
    }
	
	public function getByCountry(string $countryId): mixed
    {
        return $this->stateService->getByCountry($countryId);    
    }
	
	


    
}