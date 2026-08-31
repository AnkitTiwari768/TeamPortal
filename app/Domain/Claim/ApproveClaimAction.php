<?php

declare(strict_types=1);

namespace App\Domain\Claim;

use App\Domain\Batch\BatchStatus;
use App\Domain\Batch\EntityType;
use App\Domain\Workflow\WorkflowService;
use App\Web\BatchWorkflow\HasTimeline;
use Illuminate\Support\Facades\DB;

class ApproveClaimAction
{
    use HasTimeline;

    public function execute(array $data)
    {
        DB::transaction(function () use ($data) {

            $claimIds = $data['claim_id'];
            // Ensure claim_ids is always an array
            $claimIds = is_array($claimIds) ? $claimIds : [$claimIds];
            $batchId = $data['batch_id'];

            $workflowService = app(WorkflowService::class);
            $workflowTypeId = $workflowService->getWorkflowTypeIdBySlug($data['claim_type']);

            foreach ($claimIds as $claimId) {
                $workflowService->transition(
                    workflowTypeId: $workflowTypeId,
                    entityType: EntityType::CLAIM->value,
                    entityId: $claimId,
                    action: 'approve',
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
                
                $claim = DB::table('claims')->where('id', $claimId)->first();

                $updateData = [
                    'claim_status' => BatchStatus::getStatusByKey($workflowState->state_key),
                ];

                if (array_key_exists('tds', $data) && $data['tds'] !== null && $data['tds'] !== '') {
                    $tdsPercentage = (float) $data['tds'];
                    $baseAmount = (float) ($claim->amount ?? 0);
                    $gstAmount = (float) ($claim->gst_amount ?? 0);

                    $tdsAmount = round(($baseAmount * $tdsPercentage) / 100, 2);
                    $totalClaimedAmount = round($baseAmount + $gstAmount - $tdsAmount, 2);

                    $updateData['tds_percentage'] = $tdsPercentage;
                    $updateData['tds_amount'] = $tdsAmount;
                    $updateData['total_claimed_amount'] = $totalClaimedAmount;
                }

                DB::table('claims')
                    ->where('id', $claimId)
                    ->update($updateData);

                $this->createBatchClaimWorkflowTimeline(
                    batchId: $batchId,
                    claims: $claimId,
                    status: BatchStatus::from(17),
                    comments: request('comments') ?? null
                );
            }
        });
    }
}