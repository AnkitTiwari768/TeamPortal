<?php

declare(strict_types=1);

namespace App\Web\ApplicationWorkflow;

use App\Web\ServiceApplication\ReviewStatus;
use Illuminate\Support\Facades\DB;

class ApplicationWorkflowService
{
	public function getApplicationWorkflow(string $departmentId, string $serviceCategoryId, int $level, ?string $isMetroServiceId = null): mixed
	{	
		$query= DB::table('service_workflows')
			->where('service_department_id', $departmentId)
			->where('service_category_id', $serviceCategoryId);
			if($isMetroServiceId){
			$query= $query->where('metro_alignment_id', $isMetroServiceId);
			}
		$query= $query->where('level', $level);
		$query= $query->first();
		 
		return $query;
	}

	// public function getNextServiceWorkflow(string $userId,?string $serviceCategoryId = null): mixed
	// { 

	// 	$subQuery = DB::table('service_workflows as sw')
	// 		->select(DB::raw('level + 1'))
	// 		->whereIn('sw.role_id', function ($query) use ($userId) {
	// 			$query->select('ur.role_id')
	// 				->from('user_roles as ur')
	// 				->where('ur.user_id', $userId);
	// 		});
	// 		if ($serviceCategoryId) {
	// 			$subQuery->where('sw.service_category_id', $serviceCategoryId);
	// 		}
	// 		$subQuery->orderBy('level')->limit(1);
			
	// 	$dd= DB::table('service_workflows')
	// 		->where('level', '=', $subQuery)
	// 		->where('service_category_id', $serviceCategoryId)
	// 		->where('is_deleted', 0)
	// 		;//->get();
	// dd($dd);
	// 		//->toSql();
	// 		//dd($dd);
	// }

	public function getNextServiceWorkflow(string $userId,?string $serviceCategoryId = null,?string $metroAlignment = null): mixed
	{
		$subQuery = DB::table('service_workflows as sw')
			->select(DB::raw('level + 1'))
			->whereIn('sw.role_id', function ($query) use ($userId) {
				$query->select('ur.role_id')
					->from('user_roles as ur')
					->where('ur.user_id', $userId);
			});
			if ($serviceCategoryId) {
				$subQuery->where('sw.service_category_id', $serviceCategoryId);
			}
			if($metroAlignment){
				$subQuery->where('sw.metro_alignment_id', $metroAlignment);
			}
			$subQuery->orderBy('level')->limit(1);
 //dd($subQuery);
		return DB::table('service_workflows')
			->where('level', '=', $subQuery)
			->where('service_category_id', $serviceCategoryId)
			->when(!empty($metroAlignment), function ($query) use ($metroAlignment) {
		        $query->where('metro_alignment_id', $metroAlignment);
		    })
			->where('is_deleted', 0)
			->get(); 
	}

	public function isForwarded(string $applicationId,?string $serviceCategoryId = null,?string $isMetroAlignment = null): bool
	{
		$currentLevel = $this->getUserWorkflowLevel(auth()->user()->id, $serviceCategoryId, $isMetroAlignment);
		if($isMetroAlignment){
			$result = DB::table('service_workflow_logs as swl')
			->join('service_workflows as sw', 'swl.workflow_id', '=', 'sw.id')
			->where('swl.rts_service_id', $applicationId)
			->where('swl.workflow_level', '>', $currentLevel)
			->where('swl.metro_alignment_id', $isMetroAlignment)
			->where('swl.status', ReviewStatus::Forward->value)
			->select('swl.*')
			->first();
		}else{ 
		$result = DB::table('service_workflow_logs as swl')
			->join('service_workflows as sw', 'swl.workflow_id', '=', 'sw.id')
			->where('swl.rts_service_id', $applicationId)
			->where('swl.workflow_level', '>', $currentLevel)
			->where('swl.status', ReviewStatus::Forward->value)
			->select('swl.*')
			->first();
		}
//dd($serviceCategoryId,$result);
		return !empty($result) ? true : false;
	}

	public function getUserWorkflowLevel(string $userId, ?string $serviceCategoryId = null, ?string $isMetroAlignment = null): int
	{
		$query = DB::table('service_workflows')
			->whereIn('role_id', function ($query) use ($userId) {
				$query->select('role_id')
					->from('user_roles')
					->where('user_id', $userId);
			});

		if ($serviceCategoryId) {
			$query->where('service_category_id', $serviceCategoryId);
		}
		if($isMetroAlignment){
			$query->where('metro_alignment_id', $isMetroAlignment);
		}
		return (int) $query->value('level');
	}

	public function getUserWorkflow(string $userId): mixed
	{
		return DB::table('service_workflows')
			->whereIn('role_id', function ($query) use ($userId) {
				$query->select('role_id')
					->from('user_roles')
					->where('user_id', $userId);
			})
			->first();
	}

	public function getServiceWorkflowPermissions(string $applicationId, string $userId): array
	{
		$application = DB::table('rts_services')
			->select('department_id', 'service_category_id','property_geo_locations')
			->where('id', $applicationId)
			->first();
		//dd(json_decode($application->property_geo_locations)); 
		$isMetroAlignment = false;
		$metro_alignment_id = null;

		$geo = json_decode($application->property_geo_locations ?? '', true);
		if (json_last_error() === JSON_ERROR_NONE) {
		    $metro_alignment_id = $geo['metro_alignment'] ?? null;
		    $isMetroAlignment = !empty($metro_alignment_id);
		}
		//dd($metro_alignment_id,$isMetroAlignment);
		$userRoles = DB::table('user_roles')
			->where('user_id', auth()->user()->id)
			->pluck('role_id')
			->toArray();
		//dd($userRoles);
		if($isMetroAlignment){ //dd($metro_alignment_id);
			return DB::table('service_workflow_permissions')
			->join('permissions', 'service_workflow_permissions.permission_id', '=', 'permissions.id')
			->where('service_category_id', $application->service_category_id)
			->where('metro_alignment_id', $metro_alignment_id)
			->whereIn('role_id', $userRoles)
			->pluck('permissions.slug')
			->toArray();
		}else{
			return DB::table('service_workflow_permissions')
			->join('permissions', 'service_workflow_permissions.permission_id', '=', 'permissions.id')
			->where('service_category_id', $application->service_category_id)
			->whereIn('role_id', $userRoles)
			->pluck('permissions.slug')
			->toArray();
		}
	}

	public function getWorkflowsPermissionByID(string $serviceId, string $permissionId, string $applicationId)
	{
		$currentLevel = DB::table('service_workflow_logs')
			->where('rts_service_id', $applicationId)
			->orderBy('workflow_level', 'desc')
			->first()
			?->workflow_level;


		return DB::table('service_workflows as sw')
			->join('service_workflow_permissions as swp', 'sw.role_id', '=', 'swp.role_id')
			->where('sw.service_category_id', $serviceId)
			->where('sw.level', '=', $currentLevel + 1)
			->where('swp.permission_id', $permissionId)
			->get();

		// return DB::table('service_workflow_permissions as swp')
		// 	// ->join('service_workflows as sw', 'swp.service_workflow_id', '=', 'sw.id')
		// 	->select('sw.*')
		// 	->where('swp.permission_id', $permissionId)
		// 	->where('swp.service_category_id', $serviceId)
		// 	->get();
	}
}
