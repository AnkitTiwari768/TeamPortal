<?php

declare(strict_types=1);

namespace App\Web\Claim;

use Illuminate\Support\Facades\DB;
use App\Web\Claim\BatchClaimQueryListResource;
use App\Domain\Batch\BatchStatus;

final readonly class BatchClaimQueryListAction
{

    public function execute(string $batchId)
    {
        dd(1);
        $query = DB::table('dy_batch_claims as bc')
            ->join('claims as c', 'bc.claim_id', '=', 'c.id')
            ->join('dy_workflow_instances as wi', 'wi.entity_id', '=', 'c.id')
            ->join('dy_workflow_states as ws', 'wi.current_state_id', '=', 'ws.id')
            ->select(
                'c.id',
                'c.application_number',
                'c.msme_name',
                'c.team_registration_id',
                'c.msme_udyam_number',
                'c.msme_classification',
                'c.msme_category',
                'c.onboarding_date',
                'c.amount',
                'c.is_edited',
                'c.gst_percentage',
                'c.gst_amount',
                'c.tds_percentage',
                'c.tds_amount',
                'bc.status as claim_status',
                'bc.batch_id'
            )
            ->where('bc.is_deleted', 1);

        if (hasRole('snp')) {
            $query->where('c.created_by', authId());
        }

        $query->where('bc.status', BatchStatus::REJECTED_NSIC_FINANCE->value);
        $query->where('bc.batch_id', $batchId);


        return BatchClaimQueryListResource::collection($query->get());
    }
}
