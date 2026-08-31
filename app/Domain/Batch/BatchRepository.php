<?php

declare(strict_types=1);

namespace App\Domain\Batch;

use Illuminate\Support\Facades\DB;

class BatchRepository
{
    public function getWorkflowInitialStateByClaimSlug(string $claimTypeSlug)
    {
        $workflowTypeId = DB::table('workflow_types')
            ->where('slug', $claimTypeSlug)
            ->value('id');

        return DB::table('dy_workflow_states')
            ->where('is_initial', true)
            ->where('workflow_type_id', $workflowTypeId)
            ->first();
    }

    public function getPendingClaimsForOndc(string $batchId)
    {
        return DB::table('dy_batch_claims as bc')
            ->join('claims as c', 'bc.claim_id', '=', 'c.id')
            ->where('bc.batch_id', $batchId)
            ->whereIn('c.claim_status', [
                BatchStatus::PENDING->value,
                BatchStatus::SENT_TO_ONDC->value,
            ])
            ->count();
    }

    public function getPendingClaimsForNsic(string $batchId)
    {
        return DB::table('dy_batch_claims as bc')
            ->join('claims as c', 'bc.claim_id', '=', 'c.id')
            ->where('bc.batch_id', $batchId)
            ->whereIn('c.claim_status', [
                BatchStatus::PENDING->value,
                BatchStatus::SENT_TO_NSIC->value,
            ])
            ->count();
    }

    public function getPendingClaimsForNsicChecker(string $batchId)
    {
        return DB::table('dy_batch_claims as bc')
            ->join('claims as c', 'bc.claim_id', '=', 'c.id')
            ->where('bc.batch_id', $batchId)
            ->whereIn('c.claim_status', [
                BatchStatus::PENDING->value,
                BatchStatus::SENT_TO_ONDC->value,
            ])
            ->count();
    }

    public function getPendingClaimsForCA(string $batchId)
    {
        return DB::table('dy_batch_claims as bc')
            ->join('claims as c', 'bc.claim_id', '=', 'c.id')
            ->where('bc.batch_id', $batchId)
            ->whereIn('c.claim_status', [
                BatchStatus::PENDING->value,
                BatchStatus::SENT_TO_CA->value,
            ])
            ->count();
    }

    public function getPendingClaimsForSNP(string $batchId)
    {
        return DB::table('dy_batch_claims as bc')
            ->join('claims as c', 'bc.claim_id', '=', 'c.id')
            ->where('bc.batch_id', $batchId)
            ->whereIn('c.claim_status', [
                BatchStatus::PENDING->value,
                BatchStatus::SENT_TO_SNP_FOR_INVOICE->value,
                BatchStatus::SENT_TO_SNP->value,
            ])
            ->count();
    }

    public function getPendingClaimsForNSICFinance(string $batchId)
    {
        return DB::table('dy_batch_claims as bc')
            ->join('claims as c', 'bc.claim_id', '=', 'c.id')
            ->where('bc.batch_id', $batchId)
            ->whereIn('c.claim_status', [
                BatchStatus::PENDING->value,
                BatchStatus::SENT_TO_NSIC_FINANCE->value,
            ])
            ->count();
    }
}
