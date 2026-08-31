<?php

declare(strict_types=1);

namespace App\Web\BatchWorkflow;

use App\Enums\Status;
use App\Traits\HasResponses;
use App\Web\Claim\ClaimReviewStatus;
use Illuminate\Http\Request;

class BatchClaimWorkflowController
{
    use HasResponses;

    public function forward(StoreBatchClaimWorkflowRequest $request, StoreBatchClaimWorkflow $action)
    {
        $action->execute($request->validated(), ClaimReviewStatus::APPROVED);
        return $this->success('Claim has been approved successfully');
    }

    public function revert(StoreBatchClaimWorkflowRequest $request, StoreBatchClaimWorkflow $action)
    {
        $action->execute($request->validated(), ClaimReviewStatus::REVERTED);
        return $this->success('Claim has been reverted successfully');
    }

    public function reject(StoreBatchClaimWorkflowRequest $request, StoreBatchClaimWorkflow $action)
    {
        $action->execute($request->validated(), ClaimReviewStatus::REJECTED);
        return $this->success('Claim has been rejected successfully');
    }

    public function revertToOndc(Request $request, StoreBatchClaimWorkflow $action)
    {
        $validated = $request->validate([
            'batch_id' => 'required|uuid|exists:batches,id',
            'claim_id' => 'required|uuid|exists:claims,id',
            'comments' => 'nullable|max:1000'
        ]);

        $validated['is_revert_to_ondc'] = true;

        $action->execute($validated, ClaimReviewStatus::REVERTED);

        return $this->success('Claim has been reverted to ONDC successfully');
    }

    public function revertToSnp(Request $request, StoreBatchClaimWorkflow $action)
    {
        $validated = $request->validate([
            'batch_id' => 'required|uuid|exists:batches,id',
            'claim_id' => 'required|uuid|exists:claims,id',
            'comments' => 'nullable|max:1000'
        ]);

        $validated['is_revert_to_snp'] = true;

        $action->execute($validated, ClaimReviewStatus::REVERTED);

        return $this->success('Claim has been rejected to SNP successfully');
    }

    public function revertToNsic(Request $request, StoreBatchClaimWorkflow $action)
    {
        $validated = $request->validate([
            'batch_id' => 'required|uuid|exists:batches,id',
            'claim_id' => 'required|uuid|exists:claims,id',
            'comments' => 'nullable|max:1000'
        ]);

        $validated['is_revert_to_nsic'] = true;

        $action->execute($validated, ClaimReviewStatus::REVERTED);

        return $this->success('Claim has been rejected to SNP successfully');
    }

    public function resendSnpToNsic(Request $request, ProcessBySnpToNsic $action)
    {
        $validated = $request->validate([
            'batch_id' => 'required|uuid|exists:batches,id',
            'claim_id' => 'required|uuid|exists:claims,id',
            'comments' => 'nullable|max:1000'
        ]);

        $action->execute($validated);

        return $this->success('Claim has been sent to NSIC successfully');
    }

    public function resendNsicToFinance(Request $request, ProcessByNsicToFinance $action)
    {
        $validated = $request->validate([
            'batch_id' => 'required|uuid|exists:batches,id',
            'claim_id' => 'required|uuid|exists:claims,id',
            'comments' => 'nullable|max:1000'
        ]);

        $action->execute($validated);

        return $this->success('Claim has been sent to NSIC Finance successfully');
    }
}
