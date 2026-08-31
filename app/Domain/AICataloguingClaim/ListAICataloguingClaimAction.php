<?php

declare(strict_types=1);

namespace App\Domain\AICataloguingClaim;

use App\Domain\Claim\ClaimStatus;
use App\Web\Claim\ClaimReviewStatus;
use App\Traits\DataTable;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Support\Facades\DB;

class ListAICataloguingClaimAction
{
    use DataTable;

    public function execute(): AnonymousResourceCollection|array
    {
        [$limit, $order, $dir, $search, $page, $filters] = $this->getDataTableParams();
        $search ??= $this->escape_special_characters($search);

        $claimTypeId = DB::table('claim_types')->where('slug', 'claim-for-ai-cataloguing')->value('id');

        $query = DB::table('claims as c')
            // ->join('network_providers as np', 'c.snp_id', '=', 'np.np_team_id')
            // ->join('users as u', 'np.user_id', '=', 'u.id')
            ->select(
                'c.id',
                'c.snp_id',
                'c.application_number',
                // DB::raw('CONCAT(u.first_name, " ", IFNULL(u.last_name,"")) as snp_name'),
                // 'np.bppid_providerid as bpp_id',
                'c.status',
                'c.is_bulk',
                'c.amount',
                'c.gst_percentage',
                'c.gst_amount',
                'c.sgst_percentage',
                'c.sgst_amount',
                'c.cgst_percentage',
                'c.cgst_amount',
                'c.tds_percentage',
                'c.tds_amount',
                'c.sgst_tds_percentage',
                'c.sgst_tds_amount',
                'c.cgst_tds_percentage',
                'c.cgst_tds_amount',
                'c.total_claimed_amount',
                'c.team_registration_id',
                'c.msme_name',
                'c.msme_udyam_number',
                'c.catalogue_id',
                'c.catalogue_finalization_date',
                'c.catalogue_completion_status',
                'c.digital_catalogue_footprint',
                'c.amount_claimed_with_financial_reconciliation'
            );

        if (isset($filters)) {

            if (hasRole('nsic-checker')) {
                if (isset($filters['review_status']) && !empty($filters['review_status'])) {
                    if ($filters['review_status'] === 'Pending') {
                        $query->whereIn('c.nsicfinance_review_status', [
                            ClaimReviewStatus::SUBMITTED->value,
                            ClaimReviewStatus::PENDING->value,
                            ClaimReviewStatus::FORWARDED->value,
                        ]);
                    } else {
                        $action = ClaimReviewStatus::getIdByName($filters['review_status']);
                        $query->where('c.nsicfinance_review_status', $action);
                    }
                }
            }

            if (hasRole('administrator') || hasRole('nsic-maker')) {

                if (isset($filters['review_status']) && !empty($filters['review_status'])) {
                    if ($filters['review_status'] === 'Pending') {
                        $query->whereIn('c.claim_status', [
                            ClaimStatus::PENDING->value,
                            0,
                        ]);
                    } else {
                        $query->where('c.claim_status', ClaimStatus::getIdByLabel($filters['review_status']));
                    }
                }
            }

            if (isset($filters['is_bulk'])) {
                $query->where('is_bulk', $filters['is_bulk']);
            }
        }

        if ($search) {
            $query->where(function ($query) use ($search) {
                $query->where('c.snp_id', 'like', "$search%")
                    ->orWhere('c.application_number', 'like', "$search%")
                    ->orWhere('c.amount', 'like', "$search%")
                    ->orWhere('c.team_registration_id', 'like', "$search%")
                    ->orWhere('c.msme_name', 'like', "$search%")
                    ->orWhere('c.msme_udyam_number', 'like', "$search%");
                // ->orWhere('u.first_name', 'like', "$search%")
                // ->orWhere('u.last_name', 'like', "$search%");
            });
        }

        $query->where('c.claim_type_id', $claimTypeId);
        $query->where('c.status', '!=', ClaimReviewStatus::TEMPORARY->value);
        if (hasRole('nsic-maker')) {
            $query->where(function ($query) {
                $query->where('c.created_by', authId())
                    ->orWhere('c.created_by', auth()->user()->parent_user_id);
            });
        }
        $query->orderBy('c.id', 'desc');
        // dd($query->get());
        if ($page) {
            return $this->getDataTableResult(
                AICataloguingClaimListResource::collection($query->paginate($limit))
            );
        }

        return AICataloguingClaimListResource::collection($query->get());
    }
}
