<?php

declare(strict_types=1);

namespace App\Web\ApplicationWorkflow;

use App\Web\Claim\ClaimReviewStatus;
use App\Web\ServiceApplication\ReviewStatus;
use Illuminate\Support\Facades\DB;

class ApplicationWorkflowService
{
	public function getApplicationWorkflow(WorkflowType $workflowType, int $level): mixed
	{
		return DB::table('workflows')
			->select('workflows.*', 'workflow_types.id as workflow_type_id')
			->join('workflow_types', 'workflows.workflow_type_id', '=', 'workflow_types.id')
			->where('workflow_types.slug', $workflowType->value)
			->where('level', $level)
			->first();
	}

	public function getNextServiceWorkflow(string $userId): mixed
	{
		$subQuery = DB::table('workflows as sw')
			->select(DB::raw('level + 1'))
			->whereIn('sw.role_id', function ($query) use ($userId) {
				$query->select('ur.role_id')
					->from('user_roles as ur')
					->where('ur.user_id', $userId);
			})
			->orderBy('level')
			->limit(1);

		return DB::table('workflows')
			->where('level', '=', $subQuery)
			->get();
	}

	public function isForwarded(string $applicationId): bool
	{
		$currentLevel = $this->getUserWorkflowLevel(auth()->user()->id);

		$result = DB::table('workflow_logs as swl')
			->join('workflows as sw', 'swl.workflow_id', '=', 'sw.id')
			->where('swl.application_id', $applicationId)
			->where('swl.workflow_level', '>', $currentLevel)
			->whereNull('swl.is_obselete')
			->whereIn('swl.status', [ClaimReviewStatus::PENDING->value, ClaimReviewStatus::FORWARDED->value])
			->select('swl.*')
			->first();
		
		return !empty($result) ? true : false;
	}

	public function getUserWorkflowLevel(string $userId): int
	{
		return (int) DB::table('workflows')
			->whereIn('role_id', function ($query) use ($userId) {
				$query->select('role_id')
					->from('user_roles')
					->where('user_id', $userId);
			})
			->value('level');
	}

	public function getUserWorkflow(string $userId): mixed
	{
		return DB::table('workflows')
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
			->select('department_id', 'service_category_id')
			->where('id', $applicationId)
			->first();


		return DB::table('service_workflow_permissions as swp')
			->where('swp.service_workflow_id', function ($query) use ($application, $userId) {
				$query->select('id')
					->from('service_workflows')
					->where('service_department_id', $application->department_id)
					->where('service_category_id', $application->service_category_id)
					->where('role_id', function ($subQuery) use ($userId) {
						$subQuery->select('role_id')
							->from('user_roles')
							->where('user_id', $userId)
							->limit(1);
					});
			})
			->pluck('swp.permission_id')
			->toArray();
	}

	public function getWorkflowsPermissionByID(string $serviceId, string $permissionId)
	{
		return DB::table('service_workflow_permissions as swp')
			->join('service_workflows as sw', 'swp.service_workflow_id', '=', 'sw.id')
			->select('sw.*')
			->where('swp.permission_id', $permissionId)
			->where('sw.service_category_id', $serviceId)
			->get();
	}
}
