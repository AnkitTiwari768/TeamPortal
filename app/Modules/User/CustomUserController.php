<?php 
declare(strict_types=1);
namespace App\Modules\User;
use App\Http\Api\V1\CustomUser\{CustomUserService,CustomUserRequest,CustomUserDto};  
use App\Http\Api\V1\CustomUserPermission\{CustomUserPermissionService, CustomUserPermissionRequest, CustomUserPermissionDto};  
use App\Http\Controllers\ClientController;
use App\Http\Api\V1\User\UserValidation as Validation;
use Illuminate\Http\Request;
use Illuminate\View\View;  
use Session; 

final class CustomUserController extends ClientController
{
    private static $module = 'user.index';

    public function __construct(
        private CustomUserService $userService,
        private CustomUserPermissionService $userPermissionService
    )
    {
        $this->userService = $userService; 
    } 

    public function index(): View
    { 
        guard(config('permissions.create-view'));
        return view('user.index')
            ->with('title', __('message.user_list'));
    }

    public function datalist(): mixed
    {
        guard(config('permissions.create-view'));
       $result = $this->userService->listUser();
        return $result->status() 
            ? $this->success($result->data()) 
            : $this->error($result->message());
    } 

    public function create(): View
    {
        guard(config('permissions.create-view'));
        return view('user.create')
            ->with('title', __('message.add_user'))
            ->with('module_url', self::$module);
    } 

    public function store(Request $request): mixed 
    {   
	    //print_r($request->all());exit;
		guard(config('permissions.create-view'));
        
        $validationResult = CustomUserRequest::validateRequest($request);
        if (! $validationResult->status()) return $this->error($validationResult->errors()); 

       // dd($validationResult->validated());
        $result = $this->userService->createUser(CustomUserDto::fromRequest($validationResult->validated()));
        return (! $result->status()) 
            ? $this->error($result->message())
            : $this->created();
    }

    public function edit(string $id): View
    {
        guard(config('permissions.create-view')); 
     
        return view('user.create')
            ->with('title', __('message.user_state'))
            ->with('module_url', self::$module)
            ->with('id', $id)
            ->with('row',(array) $this->userService->getUser($id)->data()) 
			->with('title', __('message.edit_user'));	
    }

    public function show($id)
    {
        guard(config('permissions.create-view'));
        try 
        {			
			$module_url=self::$module;
            $row = (array) $this->service->getUser($id);
			//echo "<pre/>";print_r($row);exit;
            return view('users.details', compact('id', 'row','module_url'))
            ->with('title', __('message.user_details'));
        }
        catch (\Throwable $e) 
        {
            return $this->handleException($e);
        }
    }

    public function update(Request $request, string $userId): mixed
    {   
        guard(config('permissions.create-view'));
        $validationResult = CustomUserRequest::validateRequest($request, $userId);
        if (! $validationResult->status()) return $this->error($validationResult->errors());
        
        $result = $this->userService->updateUser(CustomUserDto::fromRequest($validationResult->validated()), $userId);
        return (! $result->status()) 
            ? $this->error($result->message())
            : $this->updated();
    }

    public function getCustomUserPermisisons($userId)
    {
        $result = $this->userPermissionService->getUserPermissions($userId);
        return $result->status() ? $this->success($result->data()) : $this->error($result->message());
    }

    public function saveCustomUserPermisisons(Request $request)
    {
        $validationResult = CustomUserPermissionRequest::validateRequest($request);
        if (! $validationResult->status()) return $this->error($validationResult->errors());
        
        $result = $this->userPermissionService->storeUserPermissions(CustomUserPermissionDto::fromRequest($validationResult->validated()));
        return (! $result->status()) 
            ? $this->error($result->message())
            : $this->created();
    }
 
}