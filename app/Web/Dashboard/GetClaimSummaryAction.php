<?php

declare(strict_types=1);

namespace App\Web\Dashboard;

use App\Domain\Batch\BatchStatus;
use App\Domain\Claim\ClaimStatus;

use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class GetClaimSummaryAction
{
    public function execute(?string $claimTypeSlug = null, ?int $year = null, ?string $fromDate = null, ?string $toDate = null, ?string $createdBy = null, ?int $type = null, bool $isCa = false)
    {
        $claimType = null;

        if ($claimTypeSlug) {
            $claimType = $this->getClaimTypeBySlug($claimTypeSlug);
        }

        $claimAgg = $this->getClaimAggregatedSummary($claimType?->id ?? null, $year, $fromDate, $toDate, $type, $createdBy);
        $batchAgg = $this->getBatchAggregatedSummary($claimType?->id ?? null, $year, $fromDate, $toDate, $type, $createdBy);

        $pendingCount = (int) ($claimAgg->total_pending ?? 0);
        $approvedCount = (int) ($batchAgg->total_approved ?? 0);

        if ($isCa) {
            return [
                'claimType' => $claimTypeSlug,
                'claimTypeName' => $claimType?->name ?? null,
                'totalClaim' => $pendingCount + $approvedCount,
                'pendingCount' => $pendingCount,
                'approvedCount' => $approvedCount,
                'paymentCompletedCount' => 0,
                'totalAmount' => 0,
                'rejectedCount' => 0,
                'totalDraft' => 0
            ];
        }

        return [
            'claimType' => $claimTypeSlug,
            'claimTypeName' => $claimType?->name ?? null,
            'totalClaim' => $claimAgg->total_claims ?? 0,
            'totalDraft' => $claimAgg->total_draft ?? 0,
            'pendingCount' => $pendingCount,
            'paymentCompletedCount' => $claimAgg->total_payment_completed ?? 0,
            'approvedCount' => $approvedCount,
            'rejectedCount' => $this->getClaimRejectedCount($claimType?->id, $year, $fromDate, $toDate, $type, $createdBy),
            'totalAmount' => $this->getClaimTotalReimbursedAmount($claimType?->id, $year, $fromDate, $toDate, $type, $createdBy)
        ];
    }

    public function getBatchAggregatedSummary($claimTypeId, $year, $fromDate, $toDate, $type, $createdBy)
    {
        $query = DB::table('claims as c')
            ->join('dy_batch_claims as bc', 'bc.claim_id', '=', 'c.id')
            ->join('dy_batches as b', 'b.id', '=', 'bc.batch_id')
            ->selectRaw("
                SUM(CASE WHEN bc.status = ? THEN 1 ELSE 0 END) as total_approved
            ", [
                BatchStatus::APPROVED->value,
            ]);

        if ($claimTypeId) {
            $query->where('b.claim_type_id', $claimTypeId);
        }

        // if ($createdBy) {
        //     $query->where('c.created_by', $createdBy);
        // }
        if ($createdBy) {
            $query->where(function ($q) use ($createdBy) {
                $q->where('c.created_by', $createdBy);
                if (auth()->user()->parent_user_id) {
                    $q->orWhere('c.created_by', auth()->user()->parent_user_id);
                }
            });
        }

        if ($fromDate && $toDate) {
            $query->whereBetween('c.created_at', [
                Carbon::parse($fromDate)->startOfDay(),
                Carbon::parse($toDate)->endOfDay(),
            ]);
        } else {
            if ($type != 1 && $year) {
                $query->whereYear('c.created_at', $year);
            }
        }

        return $query->first();
    }

    public function getClaimAggregatedSummary($claimTypeId, $year, $fromDate, $toDate, $type, $createdBy)
    {
        $query = DB::table('claims')
            ->selectRaw("
            SUM(CASE WHEN claim_status != ? THEN 1 ELSE 0 END) as total_claims,
            SUM(CASE WHEN claim_status = ? THEN 1 ELSE 0 END) as total_draft,
            (SUM(CASE WHEN claim_status NOT IN (?, ?, ?, ?, ?, ?) THEN 1 ELSE 0 END) + SUM(CASE WHEN claim_status IN (?,?) AND temporary_state=1 THEN 1 ELSE 0 END )) as total_pending,
            SUM(CASE WHEN claim_status = ? THEN 1 ELSE 0 END) as total_payment_completed,
            SUM(CASE WHEN claim_status = ? THEN amount ELSE 0 END) as total_reimbursed_amount
        ", [
                ClaimStatus::DRAFT->value, // Added for total_claims condition
                ClaimStatus::DRAFT->value,
                ClaimStatus::DRAFT->value,
                BatchStatus::APPROVED->value,
                BatchStatus::PAYMENT_COMPLETED->value,
                BatchStatus::REJECTED_BY_ONDC->value,
                BatchStatus::REJECTED_BY_NSIC->value,
                BatchStatus::REJECTED_NSIC_FINANCE->value,
                BatchStatus::REJECTED_BY_ONDC->value,
                BatchStatus::REJECTED_BY_NSIC->value,
                BatchStatus::PAYMENT_COMPLETED->value,
                BatchStatus::PAYMENT_COMPLETED->value
            ]);

        if ($claimTypeId) {
            $query->where('claim_type_id', $claimTypeId);
        }

        if ($createdBy) {
            $query->where(function ($q) use ($createdBy) {
                $q->where('created_by', $createdBy);
                if (auth()->user()->parent_user_id) {
                    $q->orWhere('created_by', auth()->user()->parent_user_id);
                }
            });
        }

        if ($fromDate && $toDate) {
            $query->whereBetween('created_at', [
                Carbon::parse($fromDate)->startOfDay(),
                Carbon::parse($toDate)->endOfDay(),
            ]);
        } else {
            if ($type != 1 && $year) {
                $query->whereYear('created_at', $year);
            }
        }

        return $query->first();
    }

    public function getClaimRejectedCount(?string $claimTypeId = null, $year, $fromDate, $toDate, $type, $createdBy = null)
    {
        $query = DB::table('claims as c')
            ->join('dy_batch_claims as bc', 'bc.claim_id', '=', 'c.id')
            ->where('bc.is_deleted', 1)
            ->where('c.claim_status', '!=', ClaimStatus::DRAFT->value);

        if ($claimTypeId) {
            $query->where('c.claim_type_id', $claimTypeId);
        }

        // if ($createdBy) {
        //     $query->where('c.created_by', $createdBy);
        // } elseif (hasRole('snp')) {
        //     $query->where('c.created_by', AuthId());
        // }
        if ($createdBy) {
            $query->where(function ($q) use ($createdBy) {
                $q->where('c.created_by', $createdBy);
                if (auth()->user()->parent_user_id) {
                    $q->orWhere('c.created_by', auth()->user()->parent_user_id);
                }
            });
        } elseif (hasRole('snp')) {
            $authId = AuthId();
            $query->where(function ($q) use ($authId) {
                $q->where('c.created_by', $authId);
                if (auth()->user()->parent_user_id) {
                    $q->orWhere('c.created_by', auth()->user()->parent_user_id);
                }
            });
        }



        if ($fromDate && $toDate) {
            $query->whereBetween('c.created_at', [
                Carbon::parse($fromDate)->startOfDay(),
                Carbon::parse($toDate)->endOfDay(),
            ]);
        } else {
            if ($type != 1) {
                $query->whereYear('c.created_at', $year);
            }
        }

        return $query->count();
    }

    public function getClaimTypeBySlug(string $slug)
    {
        return DB::table('claim_types')->where('slug', $slug)->first();
    }

    public function getClaimTotalReimbursedAmount(?string $claimTypeId = null, $year, $fromDate, $toDate, $type, $createdBy = null)
    {

        $query = DB::table('dy_batches as dbc')
            ->selectRaw('
                SUM(dbc.batch_total_claimed_amount)
                - SUM(dbc.tds_amount)
                - SUM(dbc.cgst_tds_amount)
                - SUM(dbc.sgst_tds_amount)
                - SUM(dbc.igst_tds_amount) AS totalamount
            ')
            ->where('dbc.status', BatchStatus::PAYMENT_COMPLETED->value);

        if ($claimTypeId) {
            $query->where('dbc.claim_type_id', $claimTypeId);
        }

        if ($createdBy) {
            $query->where(function ($q) use ($createdBy) {
                $q->where('dbc.created_by', $createdBy);
                if (auth()->user()->parent_user_id) {
                    $q->orWhere('dbc.created_by', auth()->user()->parent_user_id);
                }
            });
        }

        if ($fromDate && $toDate) {
            $query->whereBetween('dbc.created_at', [
                Carbon::parse($fromDate)->startOfDay(),
                Carbon::parse($toDate)->endOfDay(),
            ]);
        } else {
            if ($type != 1 && $year) {
                $query->whereYear('dbc.created_at', $year);
            }
        }

        return (float) $query->value('totalamount');
    }
}
