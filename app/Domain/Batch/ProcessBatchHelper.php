<?php 

declare(strict_types=1);

namespace App\Domain\Batch;

use App\Domain\Workflow\WorkflowService;
use Illuminate\Support\Facades\DB;

class ProcessBatchHelper
{
    public function __construct(
        private WorkflowService $workflowService
    ) {}

    public function getApprovedClaims($batchClaims, string|int $status): array
    {
        return DB::table('claims')
            ->whereIn('id', $batchClaims->pluck('claim_id')->toArray())
            ->where('claim_status', $status)
            ->pluck('id')
            ->toArray();
    }

    public function handleTransitions($workflowTypeId, $batchId, $approved, $rejected, $data): void
    {
        if ($data['action'] !== 'proceed-batch-workflow-sent-to-finance') {
            $this->transitionBatch($workflowTypeId, $batchId, 'approve');
        }

        if (!empty($approved)) {
            $this->transitionBatch($workflowTypeId, $batchId, 'send');
        }

        foreach ($approved as $id) {
            $this->transitionClaim($workflowTypeId, $id, 'approve');
        }

        foreach ($rejected as $id) {
            $this->transitionClaim($workflowTypeId, $id, 'reject');
        }
    }

    public function finalizeBatch($batchId, $workflowTypeId, $approved, $rejected): void
    {
        $instance = $this->workflowService->getWorkflowInstance(
            workflowTypeId: $workflowTypeId,
            entityType: EntityType::BATCH->value,
            entityId: $batchId
        );

        $state = $this->workflowService->getWorkflowStateById($instance->current_state_id);

        DB::table('dy_batches')->where('id', $batchId)->update([
            'status' => BatchStatus::getStatusByKey($state->state_key),
            'current_status' => $state->state_key,
            'updated_at' => now(),
            'is_query' => !empty($rejected) ? 1 : null
        ]);

        if (!empty($approved)) {
            DB::table('claims')
                ->whereIn('id', $approved)
                ->update(['claim_status' => BatchStatus::getStatusByKey($state->state_key)]);
        }

        if (!empty($rejected)) {
            DB::table('dy_batch_claims')
                ->whereIn('claim_id', $rejected)
                ->update(['status' => BatchStatus::REJECTED_BY_ONDC->value]);
        }
    }

    private function transitionBatch($workflowTypeId, $batchId, $action)
    {
        $this->workflowService->transition(
            workflowTypeId: $workflowTypeId,
            entityType: EntityType::BATCH->value,
            entityId: $batchId,
            action: $action,
            userRole: authRoleName()
        );
    }

    private function transitionClaim($workflowTypeId, $claimId, $action)
    {
        $this->workflowService->transition(
            workflowTypeId: $workflowTypeId,
            entityType: EntityType::CLAIM->value,
            entityId: $claimId,
            action: $action,
            userRole: authRoleName()
        );
    }
}