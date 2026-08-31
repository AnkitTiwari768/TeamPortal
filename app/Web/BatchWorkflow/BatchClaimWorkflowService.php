<?php

declare(strict_types=1);

namespace App\Web\BatchWorkflow;

use Illuminate\Support\Facades\DB;
use App\Web\ApplicationWorkflow\WorkflowType;

class BatchClaimWorkflowService
{
    public function getNextBatchWorkflow(string $userId, ?string $workflowTypeId = null)
    {
        $subQuery = DB::table('workflows as w')
            ->select(DB::raw('w.level + 1'))
            ->whereIn('w.role_id', function ($query) use ($userId) {
                $query->select('ur.role_id')
                    ->from('user_roles as ur')
                    ->where('ur.user_id', $userId);
            });

        if ($workflowTypeId) {
            $subQuery->where('w.workflow_type_id', $workflowTypeId);
        }

        $subQuery->orderBy('level')->limit(1);

        return DB::table('workflows')
            ->where('level', '=', $subQuery)
            ->get();
    }

    public function getCurrentBatchWorkflowLevel(string $batchId)
    {
        return DB::table('batch_workflow')
            ->where('batch_id', $batchId)
            ->orderBy('created_at', 'desc')
            ->limit(1)
            ->first();
    }

    public function getBatchWorkflowByLevel(string $workflowTypeId, int $level)
    {
        return DB::table('workflows')
            ->where('workflow_type_id', $workflowTypeId)
            ->where('level', $level)
            ->get();
    }

    public function getWorkflowTypeId(WorkflowType $workflowType): ?string
    {
        return DB::table('workflow_types')->where('slug', $workflowType->value)->value('id');
    }

    public function getBatchCreatedUserId(string $batchId): string
    {
        return DB::table('batches')->where('id', $batchId)->value('created_by');
    }
}
