<?php

namespace App\Web\FlowManagement;

use App\Http\Controllers\ClientController;
use App\Web\FlowManagement\FlowManagementValidation as Validation;
use App\Web\Role\RoleService;
use Illuminate\Support\Facades\Validator;
use Illuminate\Http\Request;
use DB;

class FlowManagementController extends ClientController
{
    protected $module_url = 'flow-management.index';
    protected $service;

    public function __construct(FlowManagementService $service, private FlowHelperService $helper)
    {
        $this->service = $service;
        $this->helper = $helper;
    }

    public function index()
    {
        //guard('view-flow-management');
        return view('flow-management.index')
            ->with('title', 'Workflow Management');
    }

    public function datalist()
    {
        $response = $this->service->getDataTableList();
        return $this->success($response);
    }

    public function create(RoleService $roleService)
    {
        $module_url = $this->module_url;
        $roles = $roleService->getRoles();
        $workflowTypes = $this->helper->getWorkflowTypes();

        return view('flow-management.form', compact('module_url', 'roles', 'workflowTypes'))
            ->with('title', __('menu.add_menu'));
    }


    public function store(Request $request)
    {
        //guard('edit-menu');
        try {
            /*$validator = Validator::make(
                $request->all(), 
                Validation::getRules($request->all()),
                Validation::CustomMessages()
            );

            if ($validator->fails()) 
                return $this->error($validator->errors());*/

            $response = $this->service->save($request->all());
            return $this->created($response);
        } catch (Throwable $exception) {
            return $this->handler($error);
        }
    }

    public function edit($id)
    {

        //guard('edit-menu');
        $module_url = $this->module_url;
        //$row = $this->service->findById($id); 
        $data = $this->service->findByIdAllWorkflow($id);
        $row = $data['single'];

        //echo "<pre/>";print_r($data);exit;
        $allRows = json_encode($data['all'], true);
        //echo "<pre/>";print_r($data['all']);exit;
        $workflowTypes = $this->helper->getWorkflowTypes();

        $roles = $this->helper->getRoles();

        return view('flow-management.form', compact('id', 'row', 'allRows', 'workflowTypes', 'module_url', 'roles'))
            ->with('title', __('menu.add_menu'));
    }

    public function update(Request $request, $id)
    {
        //guard('edit-menu');
        try {
            /*$validator = Validator::make(
                $request->all(), 
                Validation::getRules($request->all(),(string) $id),
				 Validation::CustomMessages()
            );

            if ($validator->fails()) 
                return $this->error($validator->errors());
			*/

            $this->service->save($request->all(), $id);
            return $this->updated();
        } catch (Throwable $exception) {
            return $this->handler($error);
        }
    }

    public function get_existed_menu_order($menu_id)
    {
        $menu_orders = $this->helper->getExistingMenuOrder($menu_id);
        //get_existed_menu_order($menu_id);
        $sort_orders = array_column($menu_orders, 'sort_order');
        $range = range(1, 50);
        foreach ($sort_orders as $sort_order) {
            unset($range[array_search($sort_order, $range)]);
        }
        echo json_encode(array('data' => $range));
        exit;
    }


    /*public function getPermissionsByWorkFlowId($workFlowId){
		 return \DB::table('service_workflow_permissions')
		->where('service_workflow_id', $workFlowId)
		->pluck('permission_id') // returns Collection of permission_id values
		->toArray();
	}*/


    public function getUsersByRoleId(Request $request)
    {
        $users = $this->usersByRoleId($request->roleId);
        if ($users) {
            return view('flow-management.user-lists', ['users' => $users, 'userId' => $request->userId, 'randomId' => $request->randomId])->render();
        }
    }


    public function usersByRoleId($roleId)
    {
        return \DB::table('users')
            ->join('user_roles', 'users.id', '=', 'user_roles.user_id')
            ->select('users.id', 'users.full_name')
            ->where('user_roles.role_id', $roleId)
            ->get();
    }


    public function getPermissionsByRoleIdAndUserId(Request $request)
    {
        //print_r($request->permissionId);exit;
        $permissions = $this->permissionsByRoleIdAndUserId($request->roleId, $request->userId);
        if ($permissions) {
            return view('flow-management.user-permissions', ['permissions' => $permissions, 'permissionId' => $request->permissionId, 'randomId' => $request->randomId])->render();
        }
    }


    public function permissionsByRoleIdAndUserId($roleId, $userId)
    {


        //user wise permissions
        /*if($roleId && $userId){
			$permissions->join('user_permissions', 'permissions.id', '=', 'user_permissions.permission_id');
			$permissions->where('user_permissions.user_id', $userId);
		}*/

        //role wise permissions
        $permissions = \DB::table('permissions')
            ->select('permissions.id', 'permissions.name');

        if ($roleId) {
            $permissions->join('role_permissions', 'permissions.id', '=', 'role_permissions.permission_id')
                ->where('role_permissions.role_id', $roleId);
        }

        return $permissions->get();
    }
}
