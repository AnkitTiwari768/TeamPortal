<?php declare(strict_types=1); 
namespace App\Modules\UserPermission; 
use App\Http\Controllers\ClientController;
use App\Exceptions\ValidationException;
use App\Http\Api\V1\UserPermission\UserPermissionService as Service;
use App\Http\Api\V1\UserPermission\UserPermissionDTO;
use App\Http\Api\V1\UserPermission\UserPermissionValidation as Validation; 
use Illuminate\Http\Request;
use App\Http\Api\V1\ModulePermissionTree\{ModulePermissionTreeService, ModulePermissionTreeRepository};

final class UserPermissionController extends ClientController 
{
	protected $module_url='users.index';
    protected $service;

    public function __construct(Service $service)
    {
        $this->service = $service;
    }


    public function edit($id)
    {
        //guard(config('permissions.permission-button-view'));
        $this->authorizeManage($id);

		$module_url=$this->module_url;
        $details = $this->service->getDetails($id);
        ['userName' => $user_name,'userPermissions' => $user_permissions] = $details;
        // dd($this->service->getDetails($id));
		$permissions = (new ModulePermissionTreeService(new ModulePermissionTreeRepository))->getUserPermissionTree($id);
		//dd($permissions);
        return view('users.user_permission', compact('id','module_url','user_name','permissions','user_permissions'))
            ->with('title', __('message.user_permission'));
    }

    public function show($id)
    {
        //guard(config('permissions.permission-button-view'));
        $this->authorizeManage($id);
        try
        {
            $response['permissions'] = $this->service->findById($id);
            return $this->success($response);
        }
        catch (\Throwable $exception)
        {
            return $this->handler($exception);
        }
    }

    public function store(Request $request)
    {
	    //guard(config('permissions.permission-button-view'));
        if ($request->filled('user_id')) {
            $this->authorizeManage((string) $request->input('user_id'));
        }

        try
        {
           $validator=Validation::validate($request->all(),null);
            if ($validator->fails())
               return $this->error($validator->errors());
		    $userPermissionDTO = UserPermissionDTO::create($validator->validated());
            $response = $this->service->save($userPermissionDTO);
            return $this->created($response);
        }
        catch (\Throwable $exception)
        {
            return $this->handler($exception);
        }
    }

    /**
     * Restricts permission management to administrators and the parent user of the target
     * sub-user (or the user themself) -- mirrors the same "self + sub-users of the same
     * parent" scope UserRepository::getUsers() already uses to decide User List visibility,
     * so a parent can never assign/remove permissions for a user outside their own hierarchy.
     */
    private function authorizeManage(string $userId): void
    {
        if (hasRole('administrator')) {
            return;
        }

        if (! in_array($userId, authIdsWithSubUsers(), true)) {
            abort(403);
        }
    }
}