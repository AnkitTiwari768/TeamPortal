<?php 

declare(strict_types=1);

namespace App\Web\Role;

use Illuminate\View\View;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

use App\Http\Controllers\ClientController;
use App\Http\Api\V1\Role\{RoleService, RoleRequest};
use App\Web\Role\RoleService as WebRoleService;

class RoleController extends ClientController
{
    private static string $module = 'roles.index';

    public function __construct(
        private RoleService $roleService,
        private WebRoleService $webRoleService
    ) {}

    public function index(): View
    {
        guard(config('permissions.role-view'));
        
        $title = __('message.role_list');
        
        return view('roles.index', compact('title'));  
    }

    public function create(): View
    {
        guard(config('permissions.role-create'));
        
        $title = __('message.add_role');
        $module_url = static::$module;

        return view('roles.form', compact('title', 'module_url'));
    }

    public function edit(string $id): View
    {
        guard(config('permissions.role-update'));
        
        $title = __('message.edit_role');
        $module_url = static::$module;
        
        $row = $this->roleService->getRole($id);

        abort_if(! $row, 404);

        return view('roles.form', compact('title', 'module_url', 'row', 'id'));
    }

    public function getRoles()
    {
        guard(config('permissions.role-view'));

        return $this->success($this->roleService->getRoles());
    }

    /**
     * Feeds the Add/Edit Role permission tree over AJAX whenever the Role Type dropdown
     * changes. Returns rendered markup so the form shares one partial (and one tree.js
     * behaviour) with the standalone Role Permission screen.
     */
    public function getRoleTypePermissions(string $roleTypeId, ?string $roleId = null)
    {
        guard(config('permissions.permission-button-view'));

        if (! $this->webRoleService->roleTypeExists($roleTypeId)) {
            return $this->error([], __('message.invalid_role_type'));
        }

        ['permissions' => $permissions, 'rolePermissions' => $rolePermissions] =
            $this->webRoleService->getRoleTypePermissionTree($roleTypeId, $roleId);

        return $this->success([
            'html' => view('roles.permission-tree', compact('permissions', 'rolePermissions'))->render(),
            'total' => count($permissions),
            'assigned' => count($rolePermissions)
        ]);
    }

    public function createRole(Request $request)
    {
        guard(config('permissions.role-create'));

        $validator = Validator::make($request->all(), RoleRequest::getRules());

        if ($validator->fails())
        {
            return $this->error($validator->errors());
        }

        $result = $this->roleService->storeRole(
            $this->permissionPayload($validator->validated(), $request)
        );

        return $result['status'] ? $this->created($result['data']) : response()->json([
            'status' => false,
            'message' => $result['error'],
            'errors' => []
        ]);
    }

    public function updateRole(Request $request, string $id) 
    {   
        guard(config('permissions.role-update'));
        
        $validator = Validator::make($request->all(), RoleRequest::getRules($id));
        
        if ($validator->fails()) 
        {
            return $this->error($validator->errors());
        }

        $result = $this->roleService->storeRole(
            $this->permissionPayload($validator->validated(), $request),
            $id
        );

        return $result['status'] ? $this->updated($result['data']) : response()->json([
            'status' => false,
            'message' => $result['error'],
            'errors' => []
        ]);
    }

    /**
     * jQuery drops an empty array from the request body, so the form posts a separate flag
     * to tell "every permission was unchecked" apart from "the permission tree was never
     * rendered" (no Role Type selected, or the user lacks the permission button rights).
     */
    private function permissionPayload(array $validated, Request $request): array
    {
        $validated['permissions'] = $validated['permissions'] ?? [];
        $validated['permissions_submitted'] = $request->boolean('permissions_submitted');

        return $validated;
    }
    public function view(string $id): View
    {
        guard(config('permissions.role-view'));

        $title = __('message.role_details');
        $module_url = static::$module;
        $row = $this->roleService->getRoleWithDetails($id);

        abort_if(! $row, 404);

        return view('roles.view', compact('title', 'row', 'id', 'module_url'));
    }
}