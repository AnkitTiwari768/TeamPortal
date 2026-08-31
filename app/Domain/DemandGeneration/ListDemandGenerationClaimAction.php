<?php

declare(strict_types=1);

namespace App\Domain\DemandGeneration;

use App\Domain\Claim\ClaimStatus;
use App\Web\Claim\ClaimReviewStatus;
use App\Traits\DataTable;
use App\Web\Claim\ClaimResource;
use App\Web\User\UserService;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Redis;

class ListDemandGenerationClaimAction
{
    use DataTable;

    public function __construct(
        private UserService $userService
    ) {}

    public function execute(): AnonymousResourceCollection|array
    {
        [$limit, $order, $dir, $search, $page, $filters] = $this->getDataTableParams();
        $search ??= $this->escape_special_characters($search);
        $user = auth()->user();

        $claimTypeId = DB::table('claim_types')->where('slug', 'claim-for-demand-generation')->value('id');

        $query = DB::table('claims as c')
            ->join('network_providers as np', 'c.snp_id', '=', 'np.np_team_id')
            ->join('users as u', 'np.user_id', '=', 'u.id')
            ->select(
                'c.id',
                'c.snp_id',
                'c.application_number',
                DB::raw('CONCAT(u.first_name, " ", IFNULL(u.last_name,"")) as snp_name'),
                'np.bppid_providerid as bpp_id',
                'c.status',
                'c.is_bulk',
                'c.amount',
                'c.low_aov_eligible_records',
                'c.high_aov_eligible_records',
                'c.low_aov_unique_mse_count',
                'c.high_aov_unique_mse_count',
                'c.gst_percentage',
                'c.gst_amount',
                'c.sgst_percentage',
                'c.sgst_amount',
                'c.cgst_percentage',
                'c.cgst_amount',
                'c.tds_percentage',
                'c.sgst_tds_percentage',
                'c.sgst_tds_amount',
                'c.cgst_tds_percentage',
                'c.cgst_tds_amount',
                'c.tds_amount'
            );



        //New Code
        if (isset($filters)) {
            if (hasRole('ondc-admin')) {
                if (isset($filters['review_status']) && !empty($filters['review_status'])) {
                    if ($filters['review_status'] === 'Pending') {
                        $query->whereIn('c.ondc_review_status', [
                            ClaimReviewStatus::SUBMITTED->value,
                            ClaimReviewStatus::PENDING->value,
                            ClaimReviewStatus::FORWARDED->value,
                        ]);
                    } else {
                        $action = ClaimReviewStatus::getIdByName($filters['review_status']);
                        $query->where('c.ondc_review_status', $action);
                    }
                }
            }


            if (hasRole('nsic')) {
                if (isset($filters['review_status']) && !empty($filters['review_status'])) {
                    if ($filters['review_status'] === 'Pending') {
                        $query->whereIn('c.nsic_review_status', [
                            ClaimReviewStatus::SUBMITTED->value,
                            ClaimReviewStatus::PENDING->value,
                            ClaimReviewStatus::FORWARDED->value,
                        ]);
                    } else {
                        $action = ClaimReviewStatus::getIdByName($filters['review_status']);
                        $query->where('c.nsic_review_status', $action);
                    }
                }
            }

            if (hasRole('nsic-finance')) {
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

            if (hasRole('bnp') || hasRole('administrator')) {
                if (isset($filters['review_status']) && !empty($filters['review_status'])) {
                    if ($filters['review_status'] === 'Pending') {

                        $query->whereIn('c.claim_status', [
                            //ClaimReviewStatus::SUBMITTED->value,
                            ClaimStatus::PENDING->value,
                            //ClaimReviewStatus::FORWARDED->value,
                        ]);
                    } else {
                        // $action = ClaimReviewStatus::getIdByName($filters['review_status']);
                        // $query->where('c.status', $action);

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
                $query
                    ->where('c.snp_id', 'like', "$search%")
                    ->orWhere('c.application_number', 'like', "$search%")
                    ->orWhere('c.amount', 'like', "$search%")
                    ->orWhere('u.first_name', 'like', "$search%")
                    ->orWhere('u.last_name', 'like', "$search%")
                    ->orWhere('np.bppid_providerid', 'like', "$search%")
                    ->orWhere('c.low_aov_unique_mse_count', 'like', "$search%")
                    ->orWhere('c.high_aov_unique_mse_count', 'like', "$search%")
                    ->orWhere('c.gst_percentage', 'like', "$search%")
                    ->orWhere('c.gst_amount', 'like', "$search%")
                    ->orWhere('c.sgst_percentage', 'like', "$search%")
                    ->orWhere('c.sgst_amount', 'like', "$search%")
                    ->orWhere('c.cgst_percentage', 'like', "$search%")
                    ->orWhere('c.cgst_amount', 'like', "$search%")
                    ->orWhere('c.tds_percentage', 'like', "$search%")
                    ->orWhere('c.tds_amount', 'like', "$search%");
            });
        }


        $query->where('c.claim_type_id', $claimTypeId);
        $query->where('c.status', '!=', ClaimReviewStatus::TEMPORARY->value);
        $query->where('c.created_by', authId());
        $query->orderBy('c.id', 'desc');

        if ($page) {
            return $this->getDataTableResult(
                DemandGenerationClaimListResource::collection($query->paginate($limit))
            );
        }

        return DemandGenerationClaimListResource::collection($query->get());
    }
}
