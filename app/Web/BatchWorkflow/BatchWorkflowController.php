<?php

declare(strict_types=1);

namespace App\Web\BatchWorkflow;

use Illuminate\Http\Request;
use App\Web\Claim\ClaimReviewStatus;
use App\Traits\HasResponses;

class BatchWorkflowController
{
    use HasResponses;

    // public function approve(Request $request, ApproveBatchWorkflow $action)
    public function approve(Request $request, BatchProcessByCA $action)
    {
        $validated = $request->validate([
            'batch_id' => 'required|uuid|exists:batches,id',
            'comments' => 'nullable|max:1000',
            'file_upload_id' => 'required|exists:file_uploads,file_system_name',
            'document_category_id' => 'required|uuid|exists:document_categories,id',
        ]);

        $action->execute($validated);

        return $this->success('Batch has been approved successfully');
    }

    // public function revert(Request $request, RevertBatchWorkflow $action)
    public function revert(Request $request, BatchProcessByCA $action)
    {
        $validated = $request->validate([
            'batch_id' => 'required|uuid|exists:batches,id',
            'comments' => 'nullable|max:1000'
        ]);

        $action->execute($validated);

        return $this->success('Batch has been reverted successfully');
    }

    public function processByOndc(Request $request, BatchProcessByOndc $action)
    {
        $validated = $request->validate([
            'batch_id' => 'required|uuid|exists:batches,id',
            'comments' => 'nullable|max:1000'
        ]);

        $action->execute($validated);

        return $this->success('Batch has been approved successfully');
    }

    public function processByNsic(Request $request, BatchProcessByNsic $action)
    {
        $validated = $request->validate([
            'batch_id' => 'required|uuid|exists:batches,id',
            'comments' => 'nullable|max:1000'
        ]);

        $action->execute($validated);

        return $this->success('Batch has been processed successfully');
    }

    public function processByNsicFinance(Request $request, BatchProcessByNsicFinance $action)
    {
        $validated = $request->validate([
            'batch_id' => 'required|uuid|exists:batches,id',
            'comments' => 'nullable|max:1000'
        ]);

        $action->execute($validated);

        return $this->success('Batch has been processed successfully');
    }
}
