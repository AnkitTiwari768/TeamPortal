<?php

declare(strict_types=1);

namespace App\Web\BatchWorkflow;

use App\Web\Claim\ClaimReviewStatus;
use Illuminate\Support\Facades\DB;
use App\Domain\Batch\BatchStatus;

trait HasTimeline
{
    public function createTimeline(array $data)
    {
        DB::transaction(function () use ($data) {

            $batchDetails = DB::table('dy_batches')->where('id', $data['batch_id'])->first();
            $subject = sprintf(
                'Batch - %s has been processed by %s',
                $batchDetails->batch_number,
                authRoleName() . "-" . trim(ucwords(auth()->user()->first_name . ' ' . auth()->user()->last_name))
            );

            $batchTimelineId =  uuid();
            DB::table('batch_timelines')->insert([
                'id' => $batchTimelineId,
                'batch_id' => $data['batch_id'],
                'batch_number' => $batchDetails->batch_number,
                'subject' => $subject,
                'comments' => $data['comments'],
                'status' => $data['status'],
                'is_shown_to_ca' => $data['is_shown_to_ca'] ?? 0,
                'created_at' => now(),
                'created_by' => authId(),
                'created_by_role' => authRoleName()
            ]);


            $claimsData = [];

            if ($data['claims']) {
                $claimIds = array_column($data['claims'], 'claim_id');

                foreach ($data['claims'] as $claim) {
                    $claimsData[] = [
                        'id' => uuid(),
                        'batch_timeline_id' => $batchTimelineId,
                        'batch_id' => $data['batch_id'],
                        'claim_id' => $claim['claim_id'],
                        'claim_number' => $claim['claim_number'],
                        'subject' => $claim['subject'],
                        'comments' => $claim['comments'],
                        'status' => $claim['status'],
                        'created_at' => now(),
                        'created_by' => authId(),
                        'created_by_role' => authRoleName()
                    ];
                }
            }

            if ($claimsData) {
                DB::table('batch_timeline_details')->insert($claimsData);
            }
        });
    }



    public function createBatchClaimTimelineDetails(string $batchId, array $claimIds, ?string $status = null)
    {
        $claimDetails = DB::table('batch_claim_workflow as bcw')
            ->join('claims as c', 'bcw.claim_id', '=', 'c.id')
            ->selectRaw('bcw.claim_id, MAX(bcw.created_at) as latest_created_at, MAX(bcw.status_id) as status_id, MAX(bcw.comments) as comments, c.application_number')
            ->where('bcw.batch_id', $batchId)
            ->where('bcw.created_by', authId())
            ->whereIn('bcw.claim_id', $claimIds)
            ->groupBy('bcw.claim_id', 'c.application_number')
            ->get();

        $timelineDetails = [];
        if ($claimDetails->isNotEmpty()) {

            $authRole = authRoleName() ?? 'User';

            foreach ($claimDetails as $claim) {

                $claimStatus = BatchStatus::getLabelByValue($claim->status_id);

                // using sprintf for cleaner formatting
                $subject = sprintf(
                    'Claim %s has been %s by %s.',
                    $claim->application_number,
                    strtolower($status ?? $claimStatus),
                    $authRole
                );

                $timelineDetails[] = [
                    'batch_id' => $batchId,
                    'claim_id' => $claim->claim_id,
                    'claim_number' => $claim->application_number,
                    'comments' => $claim->comments,
                    'status' => $status ?? $claimStatus,
                    'subject' => $subject,
                ];
            }
        }

        return $timelineDetails ?? [];
    }


    public function createBatchClaimWorkflowTimeline(
        string $batchId,
        array|string $claims,
        BatchStatus $status,
        ?string $comments
    ): void {

        $data = [];

        if (is_array($claims)) {
            foreach ($claims as $claimId) {
                $data[] = [
                    'id' => uuid(),
                    'batch_id' => $batchId,
                    'claim_id' => $claimId,
                    'from_role_id' => authRoleId(),
                    'from_user_id' => authId(),
                    'status_id'  => $status->value,
                    'comments' => $comments ?? null,
                    'created_at' => now(),
                    'created_by' => authId()
                ];
            }
        } else {
            $data = [
                'id' => uuid(),
                'batch_id' => $batchId,
                'claim_id' => $claims,
                'from_role_id' => authRoleId(),
                'from_user_id' => authId(),
                'status_id'  => $status->value,
                'comments' => $comments ?? null,
                'created_at' => now(),
                'created_by' => authId()
            ];
        }

        if ($data) {
            DB::table('batch_claim_workflow')->insert($data);
        }
    }
}
