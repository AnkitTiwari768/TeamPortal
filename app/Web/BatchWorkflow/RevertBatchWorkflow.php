<?php

declare(strict_types=1);

namespace App\Web\BatchWorkflow;

use App\Web\Claim\ClaimReviewStatus;
use Illuminate\Support\Facades\DB;
use App\Web\ApplicationWorkflow\WorkflowType;
use Carbon\Carbon;

class RevertBatchWorkflow
{
    use HasUser, HasTimeline;

    public function __construct(private BatchClaimWorkflowService $batchWorkflowService) {}

    public function execute(array $data)
    {
        DB::transaction(function () use ($data) {

            $status = ClaimReviewStatus::REVERTED;

            $userId = auth()->id();

            $fromRoleId = $authRoleId = $this->fetchRoleId($userId);

            $currentWorkflow = $this->batchWorkflowService->getCurrentBatchWorkflowLevel($data['batch_id']);

            $workflowTypeId = $this->batchWorkflowService->getWorkflowTypeId(WorkflowType::CLAIM_CATALOGUE);

            $batchWorkflow = [];

            $createdBy = $this->batchWorkflowService->getBatchCreatedUserId($data['batch_id']);

            $batchWorkflow[] = [
                'id' => uuid(),
                'batch_id' => $data['batch_id'],
                'workflow_id' => $currentWorkflow->id,
                'workflow_level' => $currentWorkflow->workflow_level,
                'from_role_id' => $fromRoleId,
                'from_user_id' => $userId,
                'to_role_id' => $this->fetchRoleId($createdBy),
                'to_user_id' => $createdBy,
                'comments' => $data['comments'] ?? null,
                'status_id' => $status->value,
                'created_at' => Carbon::now(),
                'created_by' => $userId
            ];

            if ($batchWorkflow) {
                DB::table('batch_workflow')->insert($batchWorkflow);
            }

            $updatedBatchReviewStatus = [
                'batch_id' => $data['batch_id'],
                'status_id' => $status->value,
                'updated_at' => Carbon::now(),
                'updated_by' => $userId
            ];

            DB::table('batch_review_statuses')
                ->where('batch_id', $data['batch_id'])
                ->where('role_id', $authRoleId)
                ->where('user_id', $userId)
                ->update($updatedBatchReviewStatus);

            DB::table('batches')->where('id', $data['batch_id'])->update(['status_id' => $status->value]);


            $revertedBatchClaims = DB::table('batch_claims')
                ->where('batch_id', $data['batch_id'])
                ->where('status_id', $status->value)
                ->pluck('claim_id')
                ->toArray();

            DB::table('claims')->whereIn('id', $revertedBatchClaims)->update([
                'status' => $status->value,
                'review_status' => $status->value
            ]);


            $this->createTimeline([
                'batch_id' => $data['batch_id'],
                'comments' => $data['comments'],
                'status' => 'reverted',
                'is_shown_to_ca' => true,
                'claims' => $this->createBatchClaimTimelineDetails(
                    batchId: $data['batch_id'],
                    claimIds: $revertedBatchClaims
                )
            ]);
        });
    }
}
