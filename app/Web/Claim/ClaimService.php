<?php

declare(strict_types=1);

namespace App\Web\Claim;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Collection;
use App\Domain\Claim\ClaimStatus;
use App\Domain\Batch\BatchStatus;


class ClaimService
{
    public function generateApplicationNumber(?int $index = 0): string
    {
        // // Generate a unique application number, e.g., using a UUID or a custom format
        // $lastNumber = DB::table('claims')->selectRaw('MAX(CAST(SUBSTRING(application_number, 7) AS UNSIGNED)) as max_num')->value('max_num');
        // dd($lastNumber);
        // $nextNumber = (int)$lastNumber + 1 + $index;
        // return 'CLAIM-' . str_pad((string)$nextNumber, 5, '0', STR_PAD_LEFT);

        $lastNumber = DB::table('claims')
            ->where('application_number', 'REGEXP', '^CLAIM-[0-9]+$')
            ->whereRaw('CAST(SUBSTRING(application_number, 7) AS UNSIGNED) BETWEEN 1 AND 999999')
            ->max(DB::raw('CAST(SUBSTRING(application_number, 7) AS UNSIGNED)'));

        $nextNumber = ((int) ($lastNumber ?? 0)) + 1 + $index;

        return 'CLAIM-' . str_pad(
            (string) $nextNumber,
            6,
            '0',
            STR_PAD_LEFT
        );
    }

    public function generateDGApplicationNumber(string $snpId, ?int $index = 0): string
    {
        $currentMonth = (int)date('n');
        $currentYear = (int)date('Y');

        if ($currentMonth >= 4) {
            $fy = $currentYear . '-' . substr((string)($currentYear + 1), -2);
        } else {
            $fy = ($currentYear - 1) . '-' . substr((string)$currentYear, -2);
        }

        $prefix = "DG/{$fy}/{$snpId}";

        $lastNumber = DB::table('claims')
            ->where('application_number', 'like', "{$prefix}/%")
            ->selectRaw("MAX(CAST(SUBSTRING_INDEX(application_number, '/', -1) AS UNSIGNED)) as max_num")
            ->value('max_num');

        $nextNumber = (int)$lastNumber + 1 + $index;
        return "{$prefix}/" . str_pad((string)$nextNumber, 5, '0', STR_PAD_LEFT);
    }

    public function getClaimDocuments(string $claimType): array
    {
        $query = DB::table('claim_type_document_mapping as ctdm')
            ->join('claim_types as ct', 'ct.id', '=', 'ctdm.claim_type_id')
            ->join('document_categories as dc', 'dc.id', '=', 'ctdm.document_category_id')
            ->select(
                'ctdm.document_category_id',
                'dc.name as document_category_name',
                'dc.slug as document_category_slug',
                'dc.file_size',
                'dc.informations'
            )
            ->where('ct.slug', $claimType);

        if ($claimType <> 'claim-for-demand-generation') {
            $query->where('dc.status', 1);
        }

        $query->orderBy('dc.sort_order', 'asc');

        $result = $query->get();

        return $result->toArray();
    }

    public function getMsmeDetails(string $id): ?object
    {
        return DB::table('team_msme_schemes')
            ->select(
                'team_msme_schemes.*',
                'av.attribute_value as transaction_type'
            )
            ->leftJoin('attribute_values as av', 'av.id', '=', 'team_msme_schemes.ondc_transaction_type_id')
            ->where('team_msme_schemes.team_id', $id)
            ->orWhere('team_msme_schemes.udyam_no', $id)
            ->first();
    }

    public function getClaimViewDetails(string $claimId): ?array
    {
        return [
            $this->getClaimById($claimId),
            $this->getClaimOrderDetails($claimId),
            $this->getClaimDocumentsByClaimId($claimId),
        ];
    }
    // public function getBatchClaimsViewDetails(string $claimId): ?array
    // {
    //     $authId = authId();

    //     $data = DB::table('batch_claims as bc')
    //     ->select('bc.*')
    //     ->leftjoin('batches as b','b.id','=','bc.batch_id')
    //     ->leftjoin('claims as c','c.id','=','bc.claim_id')
    //     ->leftjoin('claim_orders as co','','co.claim_id')
    //     ->leftjoin('claim_documents as cd')
    //     ->where('')
    //     ->orWhere('')
    //     ->get();
    // }

    public function getClaimById(string $claimId): ?array
    {
        return (array) DB::table('claims')
            ->select(
                'claims.*',
                'av.attribute_value as msme_transaction_type_name'
            )
            ->leftJoin('attribute_values as av', 'av.id', '=', 'claims.msme_transaction_type')
            ->where('claims.id', $claimId)
            ->first();
    }

    public function getClaimOrderDetails(string $claimId): ?array
    {
        return DB::table('claim_orders')
            ->select(
                'claim_orders.*'
            )
            ->where('claim_orders.claim_id', $claimId)
            ->get()
            ->toArray();
    }

    public function getClaimDocumentsByClaimId(string $claimId): array
    {
        return DB::table('claim_documents')
            ->select('claim_documents.*', 'fu.file_name', 'fu.file_system_name', 'dc.name as document_category_name', 'dc.slug as document_category_slug', 'dc.file_size', 'dc.informations')
            ->join('document_categories as dc', 'dc.id', '=', 'claim_documents.document_category_id')
            ->join('file_uploads as fu', 'fu.id', '=', 'claim_documents.file_upload_id')
            ->where('claim_documents.claim_id', $claimId)
            ->orderBy('dc.sort_order', 'asc')
            ->get()
            ->toArray();
    }


    public function getClaimTypeId($claimSlug)
    {
        return DB::table('claim_types')->where('slug', $claimSlug)->value('id');
    }


    public function getClaimsDataTableDetails(string $slug)
    {

        $claim_type_id = $this->getClaimTypeId($slug);
        if (hasRole('snp') || hasRole('lsp') || hasRole('bnp') || hasRole('nsic-maker')) {
            $draftCount = DB::table('claims')
                ->where('claim_type_id', $claim_type_id)
                ->where('claim_status', ClaimStatus::DRAFT->value)
                ->where(function ($query) {
                    $query
                        ->where('created_by', authId())
                        ->orWhere('created_by', auth()->user()->parent_user_id);
                })
                ->count();

            $pendingCount = DB::table('dy_batches')
                ->where(function ($query) {
                    $query
                        ->where('created_by', authId())
                        ->orWhere('created_by', auth()->user()->parent_user_id);
                })
                ->where('claim_type_id', $claim_type_id)
                ->whereIn(
                    'status',
                    [
                        BatchStatus::PENDING->value,
                        BatchStatus::SENT_TO_ONDC->value,
                        BatchStatus::SENT_TO_NSIC->value,
                        BatchStatus::SENT_TO_NSIC_BY_SNP->value,
                        BatchStatus::SENT_TO_NSIC_FINANCE->value,
                    ]
                )->count();


            $approvedCount = DB::table('dy_batches')
                ->where('claim_type_id', $claim_type_id)
                ->where(function ($query) {
                    $query
                        ->where('created_by', authId())
                        ->orWhere('created_by', auth()->user()->parent_user_id);
                })
                ->whereIn('status', [BatchStatus::APPROVED->value])->count();


            $rejectedCount = DB::table('claims as c')
                ->join('dy_batch_claims as bc', 'bc.claim_id', '=', 'c.id')
                ->where(function ($query) {
                    $query
                        ->where('c.created_by', authId())
                        ->orWhere('c.created_by', auth()->user()->parent_user_id);
                })
                ->where('c.claim_type_id', $claim_type_id)
                ->where('bc.is_deleted', 1)
                ->where('c.claim_status', '!=', ClaimStatus::DRAFT->value)->count();

            $paymentCompletedCount = DB::table('dy_batches')->where('claim_type_id', $claim_type_id)
                ->where(function ($query) {
                    $query
                        ->where('created_by', authId())
                        ->orWhere('created_by', auth()->user()->parent_user_id);
                })
                ->whereIn('status', [BatchStatus::PAYMENT_COMPLETED->value])->count();

            $uploadCACertificateCount = DB::table('dy_batches')->where('claim_type_id', $claim_type_id)
                ->where(function ($query) {
                    $query
                        ->where('created_by', authId())
                        ->orWhere('created_by', auth()->user()->parent_user_id);
                })
                ->whereIn('status', [BatchStatus::SENT_TO_SNP->value, BatchStatus::SENT_TO_CA->value])->count();

            $uploadInvoiceCount = DB::table('dy_batches')->where('claim_type_id', $claim_type_id)
                ->where(function ($query) {
                    $query
                        ->where('created_by', authId())
                        ->orWhere('created_by', auth()->user()->parent_user_id);
                })
                ->where(function ($query) {
                    $query
                        ->whereIn('status', [BatchStatus::SENT_TO_SNP_FOR_INVOICE->value])
                        ->orWhere('is_invoice_reupload_requested', 1);
                })
                ->count();





            return [[
                'claim_type_slug' => $slug,
                'status' => [
                    'Drafts' => $draftCount,
                    //'Shared With CA' => $sharedCount,
                    //'Certified By CA' => $certifiedCount,
                    'Pending' => $pendingCount,
                    //'Reverted' => $revertedCount,
                    'Approved' => $approvedCount,
                    'Rejected' => $rejectedCount,
                    'Payment Completed' => $paymentCompletedCount,
                    // 'Upload CA Certificate' => $uploadCACertificateCount,
                    'Upload Invoice' => $uploadInvoiceCount,
                ]
            ]];
        } elseif (hasRole('ca')) {

            $pendingCount = DB::table('dy_batches as b')
                ->join('team_snpca_mapping as scm', 'scm.snp_user_id', '=', 'b.created_by')
                ->where('b.claim_type_id', $claim_type_id)
                ->where('scm.ca_user_id', authId())
                ->whereIn('b.status', [BatchStatus::SENT_TO_CA->value])->count();

            $approvedCount = DB::table('dy_batches as b')
                ->join('team_snpca_mapping as scm', 'scm.snp_user_id', '=', 'b.created_by')
                ->where('b.claim_type_id', $claim_type_id)
                ->where('scm.ca_user_id', authId())
                ->whereIn('b.status', [
                    BatchStatus::CA_CERTIFICATE_UPLOADED->value,
                    BatchStatus::SENT_TO_SNP_FOR_INVOICE->value,
                    BatchStatus::SENT_TO_NSIC_BY_SNP->value,
                    BatchStatus::SENT_TO_NSIC_FINANCE->value,
                    BatchStatus::APPROVED->value,
                    BatchStatus::PAYMENT_COMPLETED->value,
                ])->count();

            /*$pendingCount = DB::table('batches as b')
                ->join('batch_review_statuses as brs', 'brs.batch_id', '=', 'b.id')
                ->where('brs.role_id', authRoleId())
                ->where('brs.user_id', authId())
                ->where('brs.status_id', ClaimReviewStatus::PENDING->value)
                ->count();

            $revertedCount = DB::table('batches as b')
                ->join('batch_review_statuses as brs', 'brs.batch_id', '=', 'b.id')
                ->where('brs.role_id', authRoleId())
                ->where('brs.user_id', authId())
                //->where('brs.status_id', ClaimReviewStatus::REVERTED->value)
                ->where(function ($query) {
                    $query
                        ->where('brs.status_id', ClaimReviewStatus::REVERTED->value)
                        ->orWhere('b.is_reverted_by_ca', true);
                })
                ->count();

            $approvedCount = DB::table('batches as b')
                ->join('batch_review_statuses as brs', 'brs.batch_id', '=', 'b.id')
                ->where('brs.role_id', authRoleId())
                ->where('brs.user_id', authId())
                ->where('brs.status_id', ClaimReviewStatus::APPROVED->value)
                ->count();*/

            return [[
                'claim_type_slug' => $slug,
                'status' => [
                    'Pending' => $pendingCount,
                    //'Reverted' => $revertedCount,
                    'Approved' => $approvedCount,
                ]
            ]];
        } elseif (hasRole('ondc-admin')) {

            $pendingCount = DB::table('dy_batches')->where('claim_type_id', $claim_type_id)->whereIn('status', [BatchStatus::SENT_TO_ONDC->value])->count();


            $approvedCount = DB::table('dy_batches')
                ->where('claim_type_id', $claim_type_id)
                ->whereIn('status', [
                    BatchStatus::SENT_TO_NSIC->value,
                    BatchStatus::SENT_TO_SNP->value,
                    BatchStatus::SENT_TO_CA->value,
                    BatchStatus::SENT_TO_SNP_FOR_INVOICE->value,
                    BatchStatus::SENT_TO_NSIC_BY_SNP->value,
                    BatchStatus::SENT_TO_NSIC_FINANCE->value,
                    BatchStatus::APPROVED->value,
                ])->count();

            $rejectedCount = DB::table('claims as c')
                ->join('dy_batch_claims as bc', 'bc.claim_id', '=', 'c.id')
                ->where('c.claim_type_id', $claim_type_id)
                ->where('bc.deleted_by', authId())
                ->count();

            $paymentCompletedCount = DB::table('dy_batches')
                ->where('claim_type_id', $claim_type_id)
                ->whereIn('status', [BatchStatus::PAYMENT_COMPLETED->value])->count();

            return [[
                'claim_type_slug' => $slug,
                'status' => [
                    'Pending' => $pendingCount,
                    //'Reverted' => $revertedCount,
                    'Rejected' => $rejectedCount,
                    'Approved' => $approvedCount,
                    'Payment Completed' => $paymentCompletedCount
                ]
            ]];
        } elseif (hasRole('nsic')) {



            $pendingCount = DB::table('dy_batches')
                ->where('claim_type_id', $claim_type_id)
                ->whereIn('status', [
                    BatchStatus::SENT_TO_NSIC->value,
                    BatchStatus::SENT_TO_NSIC_BY_SNP->value
                ])->count();


            $approvedCount = DB::table('dy_batches')
                ->where('claim_type_id', $claim_type_id)
                ->whereIn('status', [
                    BatchStatus::SENT_TO_SNP->value,
                    BatchStatus::SENT_TO_CA->value,
                    BatchStatus::SENT_TO_SNP_FOR_INVOICE->value,
                    BatchStatus::SENT_TO_NSIC_FINANCE->value,
                    BatchStatus::APPROVED->value,
                ])->count();

            $rejectedCount = DB::table('claims as c')
                ->join('dy_batch_claims as bc', 'bc.claim_id', '=', 'c.id')
                ->where('c.claim_type_id', $claim_type_id)
                ->where('bc.deleted_by', authId())
                ->count();

            $InvoiceAndCAUploadedCount = DB::table('dy_batches')
                ->where('claim_type_id', $claim_type_id)
                ->whereIn('status', [
                    BatchStatus::SENT_TO_NSIC_BY_SNP->value
                ])->count();

            $paymentCompletedCount = DB::table('dy_batches')
                ->where('claim_type_id', $claim_type_id)
                ->whereIn('status', [BatchStatus::PAYMENT_COMPLETED->value])->count();

            return [[
                'claim_type_slug' => $slug,
                'status' => [
                    'Pending' => $pendingCount,
                    //'Reverted' => $revertedCount,
                    'Rejected' => $rejectedCount,
                    'Approved' => $approvedCount,
                    // 'CA & Invoice Uploaded' => $InvoiceAndCAUploadedCount,
                    'Payment Completed' => $paymentCompletedCount
                ]
            ]];
        } elseif (hasRole('nsic-finance') || hasRole('nsic-checker')) {

            $pendingCount = DB::table('dy_batches')
                ->where('claim_type_id', $claim_type_id)
                ->whereIn('status', [BatchStatus::SENT_TO_NSIC_FINANCE->value])->count();


            $approvedCount = DB::table('dy_batches')
                ->where('claim_type_id', $claim_type_id)
                ->whereIn('status', [BatchStatus::APPROVED->value])->count();

            $rejectedCount = DB::table('claims as c')
                ->join('dy_batch_claims as bc', 'bc.claim_id', '=', 'c.id')
                ->where('c.claim_type_id', $claim_type_id)
                ->where('bc.is_deleted', 1)
                ->where('bc.status', BatchStatus::REJECTED_NSIC_FINANCE->value)
                ->where('c.claim_status', '!=', ClaimStatus::DRAFT->value)->count();

            $paymentCompletedCount = DB::table('dy_batches')
                ->where('claim_type_id', $claim_type_id)
                ->whereIn('status', [BatchStatus::PAYMENT_COMPLETED->value])->count();

            return [[
                'claim_type_slug' => $slug,
                'status' => [
                    'Pending' => $pendingCount,
                    //'Reverted' => $revertedCount,
                    'Approved' => $approvedCount,
                    'Rejected' => $rejectedCount,
                    'Payment Completed' => $paymentCompletedCount
                ]
            ]];
        }
    }


    public function getClaimsDataTableDetailsOld2()
    {
        $query = DB::table('batches')
            ->join('claim_types', 'batches.claim_type_id', '=', 'claim_types.id');

        $statusColumn = 'batches.status_id';
        $includeOndcFlags = false; // <— both reverted & rejected ONDC flags

        // ---- ROLE-BASED FILTERS ----
        if (hasRole('ca')) {
            $query->join('batch_review_statuses as brs', 'brs.batch_id', '=', 'batches.id');
            $query->where('brs.role_id', authRoleId());
            $query->where('brs.user_id', AuthId());
            $statusColumn = 'brs.status_id';
        } elseif (hasRole('ondc-admin') || hasRole('nsic') || hasRole('nsic-finance')) {
            $query->join('batch_review_statuses as brs', 'brs.batch_id', '=', 'batches.id');
            $query->where('brs.role_id', authRoleId());
            $statusColumn = 'brs.status_id';
        } elseif (hasRole('snp') || hasRole('bnp') || hasRole('administrator')) {
            $includeOndcFlags = true;
        }

        // ---- CLAIM STATUS ENUM VALUES ----
        $pendingStatuses = [
            ClaimReviewStatus::SUBMITTED->value,
            ClaimReviewStatus::FORWARDED->value,
            ClaimReviewStatus::PENDING->value,
        ];
        $revertedStatuses = [ClaimReviewStatus::REVERTED->value];
        $rejectedStatuses = [ClaimReviewStatus::REJECTED->value];
        $approvedStatuses = [ClaimReviewStatus::APPROVED->value];
        $paymentCompletedStatuses = [ClaimReviewStatus::PAYMENT_COMPLETED->value];

        // ---- SQL LISTS ----
        $pendingList = implode(',', array_map('intval', $pendingStatuses));
        $revertedList = implode(',', array_map('intval', $revertedStatuses));
        $rejectedList = implode(',', array_map('intval', $rejectedStatuses));
        $approvedList = implode(',', array_map('intval', $approvedStatuses));
        $paymentCompletedList = implode(',', array_map('intval', $paymentCompletedStatuses));

        // ---- CONDITIONAL AGGREGATES ----
        $pendingCase = "SUM(CASE WHEN {$statusColumn} IN ({$pendingList}) THEN 1 ELSE 0 END) as pending_count";

        // For SNP/Admin include is_reverted_by_ondc
        if ($includeOndcFlags) {
            $revertedCase = "SUM(CASE WHEN {$statusColumn} IN ({$revertedList}) OR batches.is_reverted_by_ondc = 1 THEN 1 ELSE 0 END) as reverted_count";
            $rejectedCase = "SUM(CASE WHEN {$statusColumn} IN ({$rejectedList}) OR batches.is_rejected_by_ondc = 1 THEN 1 ELSE 0 END) as rejected_count";
        } else {
            $revertedCase = "SUM(CASE WHEN {$statusColumn} IN ({$revertedList}) THEN 1 ELSE 0 END) as reverted_count";
            $rejectedCase = "SUM(CASE WHEN {$statusColumn} IN ({$rejectedList}) THEN 1 ELSE 0 END) as rejected_count";
        }

        $approvedCase = "SUM(CASE WHEN {$statusColumn} IN ({$approvedList}) THEN 1 ELSE 0 END) as approved_count";
        $paymentCase = "SUM(CASE WHEN {$statusColumn} IN ({$paymentCompletedList}) THEN 1 ELSE 0 END) as payment_completed_count";

        $selects = [
            'claim_types.slug as claim_type_slug',
            DB::raw($pendingCase),
            DB::raw($revertedCase),
            DB::raw($rejectedCase),
            DB::raw($approvedCase),
            DB::raw($paymentCase),
        ];

        // ---- RUN QUERY ----
        $results = $query
            ->select($selects)
            ->groupBy('claim_types.slug')
            ->orderBy('claim_types.slug')
            ->get();

        // ---- FORMAT OUTPUT ----
        $transformed = $results->map(function ($row) {
            return [
                'claim_type_slug' => $row->claim_type_slug,
                'status' => [
                    'Pending' => (int) $row->pending_count,
                    'Reverted' => (int) $row->reverted_count,
                    'Rejected' => (int) $row->rejected_count,
                    'Approved' => (int) $row->approved_count,
                    'Payment Completed' => (int) $row->payment_completed_count,
                ],
            ];
        })->values()->toArray();

        // ---- DRAFT / SHARED / CERTIFIED COUNTS ----
        $draftCount = DB::table('claims')
            ->where('created_by', authId())
            ->where('status', ClaimReviewStatus::DRAFT->value)
            ->count();

        $sharedCount = DB::table('batches')
            ->where('created_by', authId())
            ->where('is_sent_ca', 1)
            ->whereNull('is_ca_certified')
            ->count();

        $certifiedCount = DB::table('batches')
            ->where('created_by', authId())
            ->where('is_ca_certified', 1)
            ->whereNull('is_sent_ondc')
            ->count();

        if (isset($transformed[0]['status'])) {
            $transformed[0]['status'] = [
                'Drafts' => $draftCount,
                'Shared With CA' => $sharedCount,
                'Certified By CA' => $certifiedCount,
                ...$transformed[0]['status']
            ];
        } else {
            return [[
                'claim_type_slug' => 'claim-for-catalogue-creation',
                'status' => [
                    'Drafts' => $draftCount,
                    'Shared With CA' => $sharedCount,
                    'Certified By CA' => $certifiedCount,
                    'Pending' => 0,
                    'Reverted' => 0,
                    'Rejected' => 0,
                    'Approved' => 0,
                    'Payment Completed' => 0
                ]
            ]];
        }

        return $transformed;
    }


    public function claimMoveToDrafts($payload)
    {
        $claimId = $payload['claim_id'];
        // $batchId = $payload['batch_id'];
        return DB::transaction(function () use ($claimId) {
            DB::table('claims')
                ->where('id', $claimId)
                ->where('created_by', authId())
                ->update([
                    'claim_status' => ClaimStatus::DRAFT->value,
                    'status' => ClaimStatus::DRAFT->value,
                    'review_status' => ClaimStatus::DRAFT->value,

                ]);

            DB::table('dy_batch_claims')
                // ->where('batch_id', $batchId)
                ->where('claim_id', $claimId)
                ->update([
                    'is_deleted' => 0,
                ]);

            DB::table('dy_workflow_instances')
                ->where('entity_id', $claimId)
                ->delete();
        });
    }

    public function deleteClaim(string $id): void
    {
        DB::transaction(function () use ($id) {
            $claim = DB::table('claims')->where('id', $id)->first();
            if (!$claim) {
                return;
            }

            $snpId = $claim->snp_id;
            $claimTypeId = $claim->claim_type_id;


            // Rollback and rebuild cycle usages if it is a demand generation claim
            $claimTypeSlug = DB::table('claim_types')->where('id', $claimTypeId)->value('slug');

            if ($claimTypeSlug === 'claim-for-demand-generation') {
                $cycleUsages = DB::table('cycle_usages')
                    ->where('snp_id', $snpId)
                    ->where('claim_type_id', $claimTypeId)
                    ->first();
                $updatedConsumedTransactions = $cycleUsages->consumed_transactions - $claim->total_eligible_records;
                $updatedConsumedMseCount = $cycleUsages->consumed_mse_count - $claim->total_unique_mse_count;

                DB::table('cycle_usages')
                    ->where('snp_id', $snpId)
                    ->where('claim_type_id', $claimTypeId)
                    ->update([
                        'consumed_transactions' => $updatedConsumedTransactions,
                        'consumed_mse_count' => $updatedConsumedMseCount,
                    ]);
                DB::table('claim_documents')->where('claim_id', $id)->delete();
                DB::table('claim_orders')->where('claim_id', $id)->delete();
                DB::table('dy_batch_claims')->where('claim_id', $id)->delete();
                DB::table('dy_workflow_instances')->where('entity_id', $id)->delete();
                DB::table('claims')->where('id', $id)->delete();
            }
        });
    }

    public function getLowestClaimWorkflowDeclaration($claimTypeId)
    {
        return DB::table('workflows as w')
            ->select('w.is_declaration_required', 'w.declaration_text')
            ->join('workflow_types as wt', 'wt.id', '=', 'w.workflow_type_id')
            ->where('wt.slug', $claimTypeId)
            ->where('w.status', 1)
            ->orderBy('w.level', 'asc')
            ->first();
    }
}
