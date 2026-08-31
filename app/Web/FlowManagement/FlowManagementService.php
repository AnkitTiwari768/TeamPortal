<?php

declare(strict_types=1);

namespace App\Web\FlowManagement;

use App\Core\BaseService;
use App\Web\FlowManagement\FlowManagement as Model;
use App\Web\FlowManagement\FlowManagementResource as Resource;
use Illuminate\Support\Str;
use DB;


class FlowManagementService extends BaseService
{
	protected static $model = Model::class;
	protected static $resource  = Resource::class;

	protected $columns = [
		1 => 'service_form_id',
		2 => 'level',
	];


	public function getDataTableList()
	{
		[$limit, $order, $dir, $search, $page, $filters] = $this->getDataTableParams();

		$orderColumn = $this->columns[$order] ?? 'sw.id';


		$query = DB::table('workflows as sw')
			->select(
				DB::raw('MIN(sw.id) as f_id'),
				'wt.name as workflow_type',
				'sw.status'
			)
			->join('workflow_types as wt', 'wt.id', '=', 'sw.workflow_type_id')
			->groupBy('wt.name', 'sw.status');

		$query->orderBy($orderColumn, $dir);

		if (isset($page) && !empty($page)) {
			return $this->getDataTableResult(
				Resource::collection(
					$query->paginate($limit)
				)
			);
		}


		return Resource::collection($query->get());
	}

	/*public function getDataTableList()
    { 
		[$limit, $order, $dir, $search, $page, $filters] = $this->getDataTableParams();

		$orderColumn = $this->columns[$order] ?? 'sw.id';


		$query =DB::table('service_workflows as sw')
			   ->select(
			        'sw.id as f_id',
			        'sw.*', 
			        'srd.name as d_name',   // Assuming department name from srd
			        'rsc.name as c_name',   // Category name from rsc
			        'r.name as r_name',     // Role name
			        'u.full_name as u_name'      // Fixed typo
			    )
			    ->leftJoin('rts_service_departments as srd', 'sw.service_department_id', '=', 'srd.id')  
			    ->leftJoin('rts_service_categories as rsc', 'sw.service_category_id', '=', 'rsc.id')  
			    ->leftJoin('roles as r', 'sw.role_id', '=', 'r.id')  
			    ->leftJoin('users as u', 'sw.user_id', '=', 'u.id');  
			$query->orderBy($orderColumn, $dir);
        if (isset($page) && !empty($page)) {
            return $this->getDataTableResult(
                Resource::collection(
                    $query->paginate($limit)
                )
            );
        }

        return Resource::collection($query->get());
    }*/


	public function save($payload, $id = null)
	{
		foreach ($payload['flow'] as $flowId => $flow) {
			$data = [
				'workflow_type_id' => $payload['workflow_type_id'],
				'level'                 => $flow['level'],
				'role_id'               => $flow['role_id'],
				'user_id'               => $flow['user_id'] ?? null,
				'status'                => $payload['is_active'],
			];

			// Case 1: Update if ID is provided and exists
			if (!empty($flow['id'])) {
				$existing = DB::table('workflows')->where('id', $flow['id'])->first();

				if ($existing) {
					// Update the record
					$data['updated_at'] = currentDateTime();
					$data['updated_by'] = AuthId();

					DB::table('workflows')->where('id', $flow['id'])->update($data);
					$workflowId = $flow['id'];
				} else {
					// Log or skip if ID doesn't exist (do NOT insert)
					continue; // skip to next flow
				}
			} else {
				// Case 2: Insert new entry if no ID is provided
				$workflowId = uuid();
				$data['id'] = $workflowId;
				$data['created_at'] = currentDateTime();
				$data['created_by'] = AuthId();

				DB::table('workflows')->insert($data);
			}

			// Remove old permissions
			DB::table('workflow_permissions')->where('workflow_id', $workflowId)->delete();

			// Insert new permissions
			if (!empty($flow['permission_id']) && is_array($flow['permission_id'])) {
				$permissionRows = [];
				foreach ($flow['permission_id'] as $permissionId) {
					$permissionRows[] = [
						'workflow_id' => $workflowId,
						'permission_id'       => $permissionId,
					];
				}

				DB::table('workflow_permissions')->insert($permissionRows);
			}
		}

		return true;
	}


	public function findById($id)
	{
		return Model::findOrFail($id);
	}


	public function findByIdAllWorkflow($id)
	{
		$selectedRow = Model::findOrFail($id);

		//$allRows= Model::where('service_department_id',$departmentID)->where('service_category_id',$serviceID)->get(); 

		// First, get distinct workflows only
		$workflows = DB::table('workflows as sw')
			->select('sw.*')
			->where('sw.workflow_type_id', $selectedRow->workflow_type_id)
			//->distinct()
			->get();

		$allRows = [];

		foreach ($workflows as $workflow) {
			$workflowId = $workflow->id;
			$workflowData = (array) $workflow;
			// Get unique permissions for this workflow
			$workflowData['permissions'] = DB::table('workflow_permissions')
				->where('workflow_id', $workflowId)
				->pluck('permission_id')
				->unique()
				->values()
				->toArray();

			$allRows[] = $workflowData;
		}


		return [
			'single' => $selectedRow,
			'all' => $allRows,
		];
	}

	public function getPermissionsByWorkFlowId($workFlowId)
	{
		return \DB::table('workflow_permissions')
			->where('workflow_id', $workFlowId)
			->pluck('permission_id')
			->toArray();
	}
}
