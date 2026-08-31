<?php

declare(strict_types=1);

namespace App\Web\Claim;

use App\Domain\Claim\ClaimStatus;
use App\Traits\DataTable;
use App\Web\Claim\RejectClaimResource;
use App\Web\User\UserService;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Redis;


class RejectListClaimAction
{
    use DataTable;

    public function __construct(
        private UserService $userService
    ) {}

    public function execute(?string $claimTypeSlug = null): AnonymousResourceCollection|array
    {
        //dd($claimTypeSlug);
        [$limit, $order, $dir, $search, $page, $filters] = $this->getDataTableParams();
        $search ??= $this->escape_special_characters($search);
        $user = auth()->user();
        $claimTypeId = DB::table('claim_types')->where('slug', $claimTypeSlug ?? 'claim-for-catalogue-creation')->value('id');

        if ($claimTypeSlug === 'claim-for-ai-cataloguing') {
            $query = DB::table('claims as c')
                ->select(
                    'c.id',
                    'c.snp_id',
                    'c.application_number',
                    'c.status',
                    'c.is_bulk',
                    'c.amount',
                    'c.team_registration_id',
                    'c.gst_percentage',
                    'c.gst_amount',
                    'c.sgst_percentage',
                    'c.sgst_amount',
                    'c.cgst_percentage',
                    'c.cgst_amount',
                    'b.batch_number',
                    'b.id as batch_id',
                    'c.bpp_id',
                    'c.total_claimed_amount',
                    'c.msme_udyam_number',
                    'c.msme_name',
                    'c.catalogue_id',
                    'c.catalogue_finalization_date'
                );
            $query->join('network_providers as np', 'c.snp_id', '=', 'np.np_team_id');
        } else {
            if ($claimTypeSlug === 'claim-for-transportation-and-logistic' || $claimTypeSlug === 'claim-for-demand-generation') {
                $snpIdColumn = 'tss.np_team_id as snp_id';
                $snpNameColumn = 'tss.organization_name as snp_name';
            } else {
                $snpIdColumn = 'tss.snp_id';
                $snpNameColumn = 'tss.organization_name as snp_name';
            }

            $query = DB::table('claims as c')
                ->select(
                    'c.id',
                    $snpIdColumn,
                    $snpNameColumn,
                    'c.application_number',
                    'c.status',
                    'c.is_bulk',
                    'c.amount',
                    'c.team_registration_id',
                    'c.total_unique_mse_count',
                    'c.total_eligible_records as total_cumulative_transaction_count',
                    'c.low_aov_unique_mse_count',
                    'c.low_aov_eligible_records as low_aov_cumulative_transaction_count',
                    'c.high_aov_unique_mse_count',
                    'c.high_aov_eligible_records as high_aov_cumulative_transaction_count',
                    'c.gst_percentage',
                    'c.gst_amount',
                    'c.sgst_percentage',
                    'c.sgst_amount',
                    'c.cgst_percentage',
                    'c.cgst_amount',
                    'c.tds_percentage',
                    'c.tds_amount',
                    'c.cgst_tds_amount',
                    'c.sgst_tds_amount',
                    'c.date_of_sku_update',
                    'b.batch_number',
                    'b.id as batch_id',
                    'c.bpp_id',
                    'c.total_claimed_amount'
                );

            if ($claimTypeSlug === 'claim-for-transportation-and-logistic' || $claimTypeSlug === 'claim-for-demand-generation') {
                $query->join('network_providers as tss', 'tss.np_team_id', '=', 'c.snp_id');
            } else {
                $query->addSelect(
                    'tms.udyam_no as msme_udyam_number',
                    'tms.entrepreneur_name as msme_name',
                    'tms.msme_classification',
                    'tms.major_activity',
                    'atr.attribute_value as target_customer',
                    'tms.seller_provider_id',
                    'c.onboarding_date',
                    DB::raw('"" as subdomain_names')
                );

                $query->join('team_snp_scheme as tss', 'tss.snp_id', '=', 'c.snp_id');
                $query
                    ->join('team_msme_schemes as tms', 'tms.udyam_no', '=', 'c.msme_udyam_number')
                    ->join('attribute_values as atr', 'atr.id', '=', 'c.msme_transaction_type')
                    ->join('sub_domains as sud', function ($join) {
                        $join->whereRaw("JSON_CONTAINS(tms.product_category_id, JSON_QUOTE(sud.id))");
                    })
                    ->groupBy('c.id');
            }
        }

        $query
            ->join('dy_batch_claims as bc', 'bc.claim_id', '=', 'c.id')
            ->join('dy_batches as b', 'b.id', '=', 'bc.batch_id');

        // ->leftJoin('sub_domains as sud', function ($join) {
        //	$join->whereRaw("JSON_CONTAINS(tms.product_category_id, JSON_QUOTE(sud.id))");
        //})

        //->distinct();


        //New Code
        if (isset($filters)) {
            if ((hasRole('snp') || hasRole('lsp')) || hasRole('administrator') || hasRole('bnp')) {
                if (isset($filters['review_status']) && !empty($filters['review_status'])) {
                    $query->where(function ($query) {
                        $query
                            ->where('c.created_by', authId())
                            ->orWhere('c.created_by', auth()->user()->parent_user_id);
                    });
                }
            } else {
                $query->where('bc.deleted_by', authId());
            }
        }


        if ($search) {
            $query->where(function ($query) use ($search) {
                $query
                    ->where('b.batch_number', 'like', "$search%")
                    ->orWhere('c.snp_id', 'like', "$search%")
                    ->orWhere('c.team_registration_id', 'like', "$search%")
                    ->orWhere('c.msme_udyam_number', 'like', "$search%")
                    ->orWhere('c.msme_name', 'like', "$search%")
                    ->orWhere('c.amount', 'like', "$search%")
                    // ->orWhere('c.gst_charge', 'like', "$search%")
                    // ->orWhere('c.gst_charge_amount', 'like', "$search%")
                    ->orWhere('c.gst_percentage', 'like', "$search%")
                    ->orWhere('c.gst_amount', 'like', "$search%")
                    ->orWhere('c.sgst_percentage', 'like', "$search%")
                    ->orWhere('c.sgst_amount', 'like', "$search%")
                    ->orWhere('c.cgst_percentage', 'like', "$search%")
                    ->orWhere('c.cgst_amount', 'like', "$search%")
                    ->orWhere('c.tds_percentage', 'like', "$search%")
                    ->orWhere('c.tds_amount', 'like', "$search%")
                    ->orWhere('c.total_claimed_amount', 'like', "$search%")
                    ->orWhereRaw("DATE_FORMAT(c.onboarding_date, '%d-%m-%Y') like ?", ["$search%"]);
            });
        }

        if (hasRole('snp') || hasRole('lsp') || hasRole('bnp')) {
            $query->where('c.claim_type_id', $claimTypeId);
            $query->where('bc.is_deleted', 1); //is_deleted means rejected
            $query->where('c.claim_status', '!=', ClaimStatus::DRAFT->value); //if always display rejected claims  for ondc,nsic and finanace
            if (hasRole('snp') || hasRole('bnp') || hasRole('lsp')) {
                $query->where(function ($query) {
                    $query
                        ->where('c.created_by', authId())
                        ->orWhere('c.created_by', auth()->user()->parent_user_id);
                });
            }
            $query->orderBy('c.created_at', 'desc');
        } else {
            $query->where('c.claim_type_id', $claimTypeId);
            $query->where('bc.deleted_by', authId()); //if rejected by ondc,nsic and finanace
            $query->orderBy('c.created_at', 'desc');
        }

        //dd($query->toSql());

        if ($page) {
            return $this->getDataTableResult(
                RejectClaimResource::collection($query->paginate($limit))
            );
        }

        return RejectClaimResource::collection($query->get());
    }
}
