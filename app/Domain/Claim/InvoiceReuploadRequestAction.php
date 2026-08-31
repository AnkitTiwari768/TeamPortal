<?php

declare(strict_types=1);

namespace App\Domain\Claim;

use App\Domain\QMS\QueryService;
use App\Domain\QMS\QueryDTO;
use App\Domain\Batch\BatchStatus;
use App\Domain\Batch\EntityType;
use App\Domain\Workflow\WorkflowService;
use App\Web\BatchWorkflow\HasTimeline;
use Illuminate\Support\Facades\DB;
use App\Domain\Batch\Notify;

final readonly class InvoiceReuploadRequestAction
{
    use Notify;
    public function __construct(private QueryService $queryService) {}

    public function execute(array $data)
    {
        DB::transaction(function () use ($data) {
            $batchId = $data['batch_id'];
            $batchCreatedByUserId = DB::table('dy_batches')->where('id', $batchId)->value('created_by');

            DB::table('dy_batches')->where('id', $batchId)->update([
                'is_invoice_reupload_requested' => true,
                'updated_at' => now(),
                'updated_by' => authId()
            ]);

            // $queryDTO = QueryDTO::fromRequest(
            //     payload: [
            //         'receiver_id' => $batchCreatedByUserId,
            //         'subject' => 'Invoice Reupload Request',
            //         'message' => $data['comments']
            //     ],
            //     senderId: authId(),
            // );

            // $query = $this->queryService->createQuery($queryDTO);

            // DB::table('dy_queries')->insert([
            //     'id' => uuid(),
            //     'batch_id' => $batchId,
            //     'query_id' => $query->id,
            //     'created_at' => now(),
            //     'created_by' => authId()
            // ]);

            // DB::table('dy_batches')->where('id', $batchId)->update([
            //     'is_invoice_query_open' => true
            // ]);

            $batch = DB::table('dy_batches')->where('id', $batchId)->first();

            $this->notify(
                toUserId: $batch->created_by,
                templateKey: 'claim-query-received-nsic-finance',
                type: 1,
                message: [
                    'BATCH_NUMBER' => $batch->batch_number
                ]
            );
        });
    }
}
