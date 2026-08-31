<?php

declare(strict_types=1);

namespace App\Domain\Batch;

use Illuminate\Support\Facades\DB;

final readonly class BatchClaimListQuery
{

    // public function execute(string $batchId, string $status)
    // {
    //     $query = DB::table('dy_batch_claims as bc')
    //         ->join('claims as c', 'bc.claim_id', '=', 'c.id')
    //         ->join('dy_workflow_instances as wi', 'wi.entity_id', '=', 'c.id')
    //         ->join('dy_workflow_states as ws', 'wi.current_state_id', '=', 'ws.id')
    //         ->select(
    //             'c.id',
    //             'c.application_number',
    //             'c.msme_name',
    //             'c.team_registration_id',
    //             'c.msme_udyam_number',
    //             'c.msme_classification',
    //             'c.msme_category',
    //             'c.onboarding_date',
    //             'c.total_unique_mse_count',
    //             'c.total_eligible_records as total_cumulative_transaction_count',
    //             'c.amount',
    //             'c.low_aov_unique_mse_count',
    //             'c.low_aov_eligible_records as low_aov_cumulative_transaction_count',
    //             'c.high_aov_unique_mse_count',
    //             'c.high_aov_eligible_records as high_aov_cumulative_transaction_count',
    //             'c.is_edited',
    //             'c.gst_charge',
    //             'c.gst_charge_amount',
    //             'bc.status as claim_status',
    //             'bc.batch_id'
    //         )
    //         ->whereNULL('bc.is_deleted')
    //         ->where('bc.batch_id', $batchId);

    //     if ((hasRole('snp') || hasRole('lsp') || hasRole('bnp'))) {
    //         $query->addSelect(DB::raw('bc.status as claim_status'));
    //     }

    //     if (hasRole('ondc-admin') || hasRole('nsic') || hasRole('nsic-finance')) {
    //         $query->addSelect(DB::raw('ws.state_value as claim_status'));
    //     }


    //     return BatchClaimResource::collection($query->get());
    // }

    public function execute(string $batchId, string $status)
    {
        $query = DB::table('dy_batch_claims as bc')
            ->join('claims as c', 'bc.claim_id', '=', 'c.id')

            // Get latest workflow instance per claim
            ->join(DB::raw("
            (
                SELECT MAX(id) as id, entity_id
                FROM dy_workflow_instances
                GROUP BY entity_id
            ) as latest_wi
        "), 'latest_wi.entity_id', '=', 'c.id')

            // Join actual workflow instance
            ->join('dy_workflow_instances as wi', 'wi.id', '=', 'latest_wi.id')

            // Join workflow state
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
                'c.total_unique_mse_count',
                'c.total_eligible_records as total_cumulative_transaction_count',
                'c.amount',
                'c.gst_percentage',
                'c.gst_amount',
                'c.tds_percentage',
                'c.tds_amount',
                'c.sgst_percentage',
                'c.sgst_amount',
                'c.cgst_percentage',
                'c.cgst_amount',

                'c.sgst_tds_percentage',
                'c.sgst_tds_amount',
                'c.cgst_tds_percentage',
                'c.cgst_tds_amount',
                'c.igst_tds_amount',

                'c.low_aov_unique_mse_count',
                'c.low_aov_eligible_records as low_aov_cumulative_transaction_count',
                'c.high_aov_unique_mse_count',
                'c.high_aov_eligible_records as high_aov_cumulative_transaction_count',
                'c.is_edited',
                'bc.status as batch_claim_status',
                'bc.batch_id',
                'c.total_claimed_amount',
                'c.bpp_id as provider_id',
                DB::raw('EXISTS(
                    SELECT 1 FROM dy_queries dq 
                    JOIN qms_queries qq ON dq.query_id = qq.id 
                    WHERE dq.claim_id = c.id 
                      AND qq.status = "open" 
                      AND qq.deleted_at IS NULL
                ) as is_query_open')
            )

            ->whereNull('bc.is_deleted')
            ->where('bc.batch_id', $batchId);

        // Role-based claim status
        if (hasRole('snp') || hasRole('lsp') || hasRole('bnp') || hasRole('nsic-maker')) {
            $query
                ->join('dy_batches as b', 'b.id', '=', 'bc.batch_id')
                ->addSelect(DB::raw('b.is_invoice_reupload_requested'));
            $query->addSelect(DB::raw('bc.status as claim_status'));
            $query->addSelect(DB::raw('c.claim_status as claim_status_from_claims'));
        }

        if (hasRole('ondc-admin') || hasRole('nsic') || hasRole('nsic-finance') || hasRole('nsic-checker')) {
            $query->addSelect(DB::raw('ws.state_value as claim_status'));
        }

        return BatchClaimResource::collection(
            $query->distinct()->get()
        );
    }
}
