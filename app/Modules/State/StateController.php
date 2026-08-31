<?php 
declare(strict_types=1);
namespace App\Modules\State;
use App\Http\Api\V1\State\StateService; 
use App\Http\Controllers\ClientController;
use App\Http\Api\V1\State\StateValidation as Validation;
use Illuminate\Http\Request;
use Illuminate\View\View;

final class StateController extends ClientController
{
    private static $module = 'states.index';
    
    public function __construct(private StateService $service)
    {
        $this->service = $service;
    }

    public function index(): View
    {
        guard(config('permissions.state-view'));
        return view('state.index')
            ->with('title', __('message.state_list'))            
            ->with('details', $this->service->getDetails());
    }

    public function datalist(): mixed
    {
        guard(config('permissions.state-view'));
        try{            
            $result= $this->service->getStates();  
            return $this->success($result);
        }catch(\Throwable $e){
            return $this->handleException($e);
        }
    }

    public function create(): View
    {
        guard(config('permissions.state-create'));
        return view('state.form')
            ->with('title', __('message.add_state'))
            ->with('module_url', self::$module)
            ->with('details', $this->service->getDetails());
    }

    public function store(Request $request): mixed 
    {   
        guard(config('permissions.state-create'));
        try{
            $validator = Validation::getRules($request->all());
            
            if ($validator->fails()) 
            {
                return $this->error($validator->errors());
            }
            $result=$this->service->create($validator->validated());
            return $this->created($result);
        }catch(\Throwable $e){
            return $this->handleException($e);
        } 
    }

    public function edit(string $id): View
    {
        guard(config('permissions.state-update'));
        return view('state.form')
            ->with('title', __('message.edit_state'))
            ->with('module_url', self::$module)
            ->with('id', $id)
            ->with('row', $this->service->findById($id))
            ->with('details', $this->service->getDetails());
    }

    public function update(Request $request, string $id): mixed
    {   
        guard(config('permissions.state-update'));
        try{
            $validator = Validation::getRules($request->all(), $id);
            
            if ($validator->fails()) 
            {
                return $this->error($validator->errors());
            }

           return $this->service->update($validator->validated(), $id); 
            //return $this->updated();
        }catch(\Throwable $e){
            return $this->handleException($e);
        }
    }
	
	public function getByCountry(string $countryId): mixed
    {
        return $this->service->getByCountry($countryId);    
    }
}