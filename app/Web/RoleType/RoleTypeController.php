<?php

declare(strict_types=1);

namespace App\Web\RoleType;

use Illuminate\View\View;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

use App\Http\Controllers\ClientController;

class RoleTypeController extends ClientController
{
    private static string $module = 'role-types.index';

    public function __construct(private RoleTypeService $roleTypeService) {}

    public function index(): View
    {
        guard(config('permissions.role-type-view'));

        $title = __('message.role_type_list');

        return view('role-types.index', compact('title'));
    }

    public function create(): View
    {
        guard(config('permissions.role-type-create'));

        $title = __('message.add_role_type');
        $module_url = static::$module;

        return view('role-types.form', compact('title', 'module_url'));
    }

    public function edit(string $id): View
    {
        guard(config('permissions.role-type-update'));

        $title = __('message.edit_role_type');
        $module_url = static::$module;

        $row = $this->roleTypeService->getRoleType($id);

        abort_if(! $row, 404);

        return view('role-types.form', compact('title', 'module_url', 'row', 'id'));
    }

    public function view(string $id): View
    {
        guard(config('permissions.role-type-view'));

        $title = __('message.role_type_details');
        $module_url = static::$module;

        $row = $this->roleTypeService->getRoleTypeWithDetails($id);

        abort_if(! $row, 404);

        return view('role-types.view', compact('title', 'module_url', 'row', 'id'));
    }

    public function getRoleTypes()
    {
        guard(config('permissions.role-type-view'));

        return $this->success($this->roleTypeService->getRoleTypes());
    }

    /**
     * Renders the permission tree partial for the Add/Edit form. Returned as HTML rather
     * than raw JSON so the Add and Edit screens share exactly one markup source with the
     * Role Permission screen's tree.
     */
    public function getPermissionTree(?string $id = null)
    {
        guard(config('permissions.role-type-permission-view'));

        if ($id && ! $this->roleTypeService->roleTypeExists($id)) {
            return $this->error([], __('message.invalid_role_type'));
        }

        ['permissions' => $permissions, 'assignedPermissions' => $assignedPermissions] =
            $this->roleTypeService->getPermissionTree($id);

        return $this->success([
            'html' => view('role-types.permission-tree', compact('permissions', 'assignedPermissions'))->render(),
            'total' => count($permissions),
            'assigned' => count($assignedPermissions)
        ]);
    }

    public function createRoleType(Request $request)
    {
        guard(config('permissions.role-type-create'));

        $payload = $this->withGeneratedSlug($request);

        $validator = Validator::make($payload, RoleTypeRequest::getRules(), RoleTypeRequest::getMessages());

        if ($validator->fails()) {
            return $this->error($validator->errors());
        }

        $result = $this->roleTypeService->storeRoleType(
            $this->permissionPayload($validator->validated(), $request)
        );

        return $result['status']
            ? $this->created($result['data'])
            : response()->json([
                'status' => false,
                'message' => $result['error'],
                'errors' => []
            ]);
    }

    public function updateRoleType(Request $request, string $id)
    {
        guard(config('permissions.role-type-update'));

        if (! $this->roleTypeService->roleTypeExists($id)) {
            return response()->json([
                'status' => false,
                'message' => __('message.invalid_role_type'),
                'errors' => []
            ]);
        }

        $payload = $this->withGeneratedSlug($request);

        $validator = Validator::make($payload, RoleTypeRequest::getRules($id), RoleTypeRequest::getMessages());

        if ($validator->fails()) {
            return $this->error($validator->errors());
        }

        $result = $this->roleTypeService->storeRoleType(
            $this->permissionPayload($validator->validated(), $request),
            $id
        );

        return $result['status']
            ? $this->updated($result['data'])
            : response()->json([
                'status' => false,
                'message' => $result['error'],
                'errors' => []
            ]);
    }

    /**
     * The form never posts a slug -- it is derived from the name here so the duplicate slug
     * rule can run as ordinary validation instead of blowing up on the unique index.
     */
    private function withGeneratedSlug(Request $request): array
    {
        $payload = $request->all();

        $payload['name'] = is_string($payload['name'] ?? null) ? trim($payload['name']) : ($payload['name'] ?? null);
        $payload['slug'] = is_string($payload['name'] ?? null) && $payload['name'] !== ''
            ? (string) slugify($payload['name'])
            : null;

        return $payload;
    }

    /**
     * `permissions` is dropped from the request body by jQuery when nothing is checked, so
     * the form posts a separate flag to distinguish "cleared every permission" from "the
     * permission section was never rendered for this user".
     */
    private function permissionPayload(array $validated, Request $request): array
    {
        $validated['permissions'] = $validated['permissions'] ?? [];
        $validated['permissions_submitted'] = $request->boolean('permissions_submitted');

        return $validated;
    }
}
