<?php

declare(strict_types=1);

namespace App\Domain\Batch;

use App\Domain\Workflow\WorkflowService;
use Illuminate\Support\Facades\DB;

class ProcessBatchAction
{
    public function __construct(
        private WorkflowService $workflowService,
        private BatchWorkflowStrategyResolver $resolver
    ) {}

    public function execute(array $data)
    {
        DB::transaction(function () use ($data) {

            $batchId        = $data['batch_id'];
            $workflowTypeId = $this->workflowService
                ->getWorkflowTypeIdBySlug($data['claim_type']);

            $batchClaims = DB::table('dy_batch_claims')
                ->where('batch_id', $batchId)
                ->get();

            $strategy = $this->resolver->resolve();

            $strategy->process(
                $batchId,
                $workflowTypeId,
                $batchClaims,
                $data
            );
        });
    }
}