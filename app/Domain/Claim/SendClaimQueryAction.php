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

class SendClaimQueryAction
{
    use HasTimeline, Notify;

    public function __construct(private QueryService $queryService) {}

    public function execute(array $data)
    {
        DB::transaction(function () use ($data) {

            $claimId = $data['claim_id'];
            $batchId = $data['batch_id'];

            $nsicRoleId = DB::table('roles')->where('slug', 'nsic')->value('id');
            $senderRoleId = DB::table('user_roles')->where('user_id', AuthId())->value('role_id');

            $queryDTO = QueryDTO::fromRequest(
                payload: [
                    'receiver_role_id' => $nsicRoleId,
                    'subject' => 'New Query Raised by ' . authRoleName(),
                    'message' => $data['comments']
                ],
                senderId: authId(),
                senderRoleId: $senderRoleId
            );
            
            $query = $this->queryService->createQuery($queryDTO);

            DB::table('dy_queries')->insert([
                'id' => uuid(),
                'batch_id' => $batchId,
                'claim_id' => $claimId,
                'query_id' => $query->id,
                'created_at' => now(),
                'created_by' => authId()
            ]);

            DB::table('dy_batches')->where('id', $batchId)->update([
                'is_query_open' => true
            ]);

             if (
                acl('demand-generation-view') 
                || acl('transport-and-logistic-view')
                || acl('claim-packaging-view')
                || acl('claim-account-view')
                || acl('packaging-claim-view')
                || acl('demand-generation-claim-view')
                || acl('catalogue-created-claim-view')
                || acl('logistic-transportation-claim-view')
                || acl('account-management-claim-view')
                || acl('claim-view')
            ) {

                $batch = DB::table('dy_batches')->where('id', $batchId)->first();

                $this->notify(
                    toRoleSlug: 'nsic',
                    templateKey: 'claim-query-received-nsic-finance',
                    type: 2,
                    message: [
                        'BATCH_NUMBER' => $batch->batch_number
                    ]
                );
            }
        });
    }
}
