<?php

declare(strict_types=1);

namespace App\Domain\Batch;

use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Facades\DB;
use App\Traits\HasClaimNetAmountPayable;

class BatchResource extends JsonResource
{
    use HasClaimNetAmountPayable;
    /**
     * Transform the resource into an array.
     *
     * @param \Illuminate\Http\Request $request
     * @return array<string, mixed>
     */
    public function toArray($request): array
    {
        $totalClaims = $this->getTotalClaims();

        [
            $totalAmount,
            $totalGstAmount,
            $totalSgstAmount,
            $totalCgstAmount,
            // $totalTdsAmount,
            // $totalSgstTdsAmount,
            // $totalCgstTdsAmount,
            // $totalIgstTdsAmount,
            $totalClaimedAmount
        ] = $this->getClaimAmounts();

        if (isset($this->batch_total_claimed_amount) && (float)$this->batch_total_claimed_amount > 0) {
            $totalAmount = (float)$this->batch_total_base_amount;
            $totalGstAmount = (float)$this->batch_gst_amount;
            $totalSgstAmount = (float)$this->batch_sgst_amount;
            $totalCgstAmount = (float)$this->batch_cgst_amount;
            $totalClaimedAmount = (float)$this->batch_total_claimed_amount;
        }

        return [
            'id' => $this->id,
            'batch_number' => $this->batch_number,
            'financial_year' => $this->financial_year,
            'month' => $this->month,
            'month_name' => $this->month ? date('F', mktime(0, 0, 0, $this->month, 1)) : null,
            'description' => $this->description,
            'status' => BatchStatus::getLabelByValue($this->status),
            'created_at' => $this->created_at ?  date('d-m-Y', strtotime($this->created_at)) : null,
            'total_claims' => $totalClaims,
            'claim_amount' => $totalAmount,
            'snp_name'   => $this->snp_name,
            'snp_id'   => $this->snp_id,
            'is_query'   => $this->is_query,
            'is_query_open' => $this->is_query_open,
            'is_invoice_query_open' => $this->is_invoice_query_open,
            'mark_payment_completed' => $this->actionIsShownPaymentCompleted(),
            'show_move_to_draft_in_rejected' => $this->show_move_to_draft_in_rejected ?? null,
            'show_sent_to_ondc' => $this->show_sent_to_ondc ?? null,
            'show_proceed_action' => $this->actionIsShown(),
            'sent_to_ca' => $this->actionSentToCA(),
            'sent_to_nsic_finance' => $this->actionSentToNsicFinance(),
            're_upload_invoice_query_request' => $this->showReUploadInvoiceQueryRequest($totalClaims),
            'latest_invoice_url' => $this->getLatestInvoiceUrl(),
            'current_status' => $this->current_status,
            'is_invoice_uploaded' => $this->is_invoice_uploaded,
            'gst_amount' => $totalGstAmount,
            'sgst_amount' => $totalSgstAmount,
            'cgst_amount' => $totalCgstAmount,
            'tds_cgst_amount' => $this->cgst_tds_amount, //$totalCgstTdsAmount,
            'tds_sgst_amount' => $this->sgst_tds_amount, //$totalSgstTdsAmount,
            'tds_igst_amount' => $this->igst_tds_amount, //$totalIgstTdsAmount,
            'tds_amount' => $this->tds_amount, //$totalTdsAmount,
            'total_claimed_amount' => round($totalClaimedAmount - $this->tds_amount - $this->sgst_tds_amount - $this->cgst_tds_amount - $this->igst_tds_amount, 2),
        ];
    }

    protected function showReUploadInvoiceQueryRequest($totalClaims)
    {
        $approvedClaims = $this->getApprovedClaimsForNsicFinance();
        $rejectedClaims = $this->getRejectedClaimsForNsicFinance();

        if (
            $totalClaims === ($approvedClaims + $rejectedClaims)
            && $approvedClaims > 0
            && $rejectedClaims > 0
        ) {
            return true;
        }

        return false;
    }

    protected function getApprovedClaimsForNsicFinance()
    {
        return DB::table('dy_batch_claims as bc')
            ->join('claims as c', 'c.id', '=', 'bc.claim_id')
            ->where('bc.batch_id', $this->id)
            ->where('c.claim_status', BatchStatus::APPROVED->value)
            ->whereNull('bc.is_deleted')
            ->count();
    }

    protected function getRejectedClaimsForNsicFinance()
    {
        return DB::table('dy_batch_claims as bc')
            ->join('claims as c', 'c.id', '=', 'bc.claim_id')
            ->where('bc.batch_id', $this->id)
            ->where('c.claim_status', BatchStatus::REJECTED_NSIC_FINANCE->value)
            ->count();
    }

    protected function getLatestInvoiceUrl()
    {
        $attachment = DB::table('dy_attachments')
            ->where('entity_type', 'batch')
            ->where('entity_id', $this->id)
            ->orderBy('created_at', 'desc')
            ->first();

        if ($attachment) {
            return url('files/' . $attachment->file_path . '/download');
        }

        return null;
    }

    protected function getTotalClaims()
    {
        return DB::table('dy_batch_claims')->where('batch_id', $this->id)->whereNUll('is_deleted')->count();
    }

    protected function getTotalAmount()
    {
        $result = DB::table('dy_batch_claims as bc')
            ->join('claims as c', 'c.id', '=', 'bc.claim_id')
            ->where('bc.batch_id', $this->id)
            ->whereNull('bc.is_deleted')
            ->select(DB::raw('SUM(c.amount) as total_amount'))
            ->first();

        return $result->total_amount ?? 0;
    }

    protected function getClaimAmounts()
    {
        $result = DB::table('dy_batch_claims as bc')
            ->join('claims as c', 'c.id', '=', 'bc.claim_id')
            ->where('bc.batch_id', $this->id)
            ->whereNull('bc.is_deleted')
            ->select(
                DB::raw('SUM(c.amount) as total_amount'),
                DB::raw('SUM(c.gst_amount) as total_gst_amount'),
                DB::raw('SUM(c.sgst_amount) as total_sgst_amount'),
                DB::raw('SUM(c.cgst_amount) as total_cgst_amount'),
                // DB::raw('SUM(c.tds_amount) as total_tds_amount'),
                // DB::raw('SUM(c.cgst_tds_amount) as total_cgst_tds_amount'),
                // DB::raw('SUM(c.sgst_tds_amount) as total_sgst_tds_amount'),
                // DB::raw('SUM(c.igst_tds_amount) as total_igst_tds_amount'),
                DB::raw('SUM(c.total_claimed_amount) as total_claimed_amount')
            )
            ->first();

        return [
            $result->total_amount ?? 0,
            $result->total_gst_amount ?? 0,
            $result->total_sgst_amount ?? 0,
            $result->total_cgst_amount ?? 0,
            // $result->total_tds_amount ?? 0,
            // $result->total_sgst_tds_amount ?? 0,
            // $result->total_cgst_tds_amount ?? 0,
            // $result->total_igst_tds_amount ?? 0,
            $result->total_claimed_amount ?? 0
        ];
    }




    protected function actionIsShownPaymentCompleted(): bool
    {
        $currentStatus = DB::table('dy_batches')->where('id', $this->id)->value('current_status');

        if (hasRole('nsic-finance')) {
            $pendingClaims = app(BatchRepository::class)->getPendingClaimsForNSICFinance($this->id);
            return ($pendingClaims === 0 && $currentStatus === 'approved') ? true : false;
        }

        return false;
    }


    protected function actionSentToCA(): bool
    {
        $currentStatus = DB::table('dy_batches')->where('id', $this->id)->value('current_status');
        if (hasRole('snp') || hasRole('lsp') || hasRole('bnp')) {
            return ($currentStatus === 'sent_to_snp') ? true : false;
        }

        return false;
    }


    protected function actionSentToNsicFinance(): bool
    {
        $currentStatus = DB::table('dy_batches')->where('id', $this->id)->value('current_status');
        if (hasRole('nsic')) {
            return ($currentStatus === 'sent_to_nsic_by_snp') ? true : false;
        }

        return false;
    }


    protected function actionIsShown(): bool
    {
        $currentStatus = DB::table('dy_batches')->where('id', $this->id)->value('current_status');

        if (hasRole('ondc-admin')) {
            $pendingClaims = app(BatchRepository::class)->getPendingClaimsForOndc($this->id);
            return ($pendingClaims === 0 && $currentStatus === 'sent_to_onddc') ? true : false;
        }

        if (hasRole('nsic-checker')) {
            $pendingClaims = app(BatchRepository::class)->getPendingClaimsForNsicChecker($this->id);
            return ($pendingClaims === 0 && $currentStatus === 'sent_to_nsic_checker') ? true : false;
        }

        if (hasRole('nsic')) {
            $pendingClaims = app(BatchRepository::class)->getPendingClaimsForNsic($this->id);
            return ($pendingClaims === 0 && $currentStatus === 'sent_to_nsic') ? true : false;
        }

        if (hasRole('ca')) {
            $pendingClaims = app(BatchRepository::class)->getPendingClaimsForCA($this->id);
            return ($pendingClaims != 0 && $currentStatus === 'sent_to_ca') ? true : false;
        }

        if (hasRole('snp') || hasRole('lsp') || hasRole('bnp')) {
            $pendingClaims = app(BatchRepository::class)->getPendingClaimsForSNP($this->id);
            if ($this->is_invoice_reupload_requested === 1) return true;
            return ($pendingClaims != 0 && ($currentStatus === 'sent_to_snp_for_invoice')) ? true : false;
        }

        if (hasRole('nsic-finance')) {
            $pendingClaims = app(BatchRepository::class)->getPendingClaimsForNSICFinance($this->id);
            return ($pendingClaims === 0 && $currentStatus === 'sent_to_nsic_finance') ? true : false;
        }

        return false;
    }
}
