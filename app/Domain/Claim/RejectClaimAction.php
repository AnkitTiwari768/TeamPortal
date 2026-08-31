<?php

declare(strict_types=1);

namespace App\Domain\Claim;

use App\Domain\Batch\BatchStatus;
use App\Domain\Batch\EntityType;
use App\Domain\Workflow\WorkflowService;
use App\Web\BatchWorkflow\HasTimeline;
use Illuminate\Support\Facades\DB;

class RejectClaimAction
{
    use HasTimeline;

    public function execute(array $data)
    {
        DB::transaction(function () use ($data) {

            // Handle both single and multiple claim IDs
            $claimIds = is_array($data['claim_id']) ? $data['claim_id'] : [$data['claim_id']];
            $batchId = $data['batch_id'];

            $workflowService = app(WorkflowService::class);
            $workflowTypeId = $workflowService->getWorkflowTypeIdBySlug($data['claim_type']);

            // Process each claim individually
            foreach ($claimIds as $claimId) {
                $workflowService->transition(
                    workflowTypeId: $workflowTypeId,
                    entityType: EntityType::CLAIM->value,
                    entityId: $claimId,
                    action: 'reject',
                    userRole: authRoleName()
                );

                $workflowService->autoTransition(
                    workflowTypeId: $workflowTypeId,
                    entityType: EntityType::CLAIM->value,
                    entityId: $claimId,
                    userRole: authRoleName()
                );

                $currentInstance = $workflowService->getWorkflowInstance(
                    workflowTypeId: $workflowTypeId,
                    entityType: EntityType::CLAIM->value,
                    entityId: $claimId,
                );

                $workflowState = $workflowService->getWorkflowStateById($currentInstance->current_state_id);

                // Update individual claim status
                DB::table('claims')
                    ->where('id', $claimId)
                    ->update([
                        'claim_status' => BatchStatus::getStatusByKey($workflowState->state_key),
                    ]);

                // Create timeline entry for each claim
                $this->createBatchClaimWorkflowTimeline(
                    batchId: $batchId,
                    claims: $claimId,
                    status: BatchStatus::from(18),
                    comments: request('comments') ?? null
                );
            }

            // Check if all claims in the batch are rejected or if it's a bulk action
            if (hasRole('nsic-finance')) {
                // Count total claims in the batch
                $totalClaims = DB::table('dy_batch_claims')
                    ->where('batch_id', $batchId)
                    ->count();

                // Count rejected claims in the batch
                $rejectedClaims = DB::table('claims')
                    ->whereIn('id', function ($query) use ($batchId) {
                        $query->select('claim_id')
                            ->from('dy_batch_claims')
                            ->where('batch_id', $batchId);
                    })
                    ->where('claim_status', 'Rejected')
                    ->count();


                // If all claims are rejected or we're rejecting the last claim, update batch
                if ($totalClaims == $rejectedClaims) {
                    DB::table('dy_batches')->where('id', $batchId)->update([
                        'is_invoice_uploaded' => false,
                        'is_query_open' => false,
                    ]);
                }

                if (is_array($claimIds) && count($claimIds) > 1) {
                    DB::table('dy_batches')->where('id', $batchId)->update([
                        'is_invoice_uploaded' => false,
                        'is_query_open' => false,
                    ]);
                }

                DB::table('dy_batches')->where('id', $batchId)->update([
                    'is_invoice_uploaded' => false,
                ]);
            }
        });
    }
}
