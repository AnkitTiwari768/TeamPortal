<?php

declare(strict_types=1);

namespace App\Web\Claim;

use App\Domain\Claim\ClaimStatus;
use App\Traits\DataTable;
use App\Web\Claim\ClaimResource;
use App\Web\User\UserService;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Redis;

class ListClaimAction
{
    use DataTable;

    public function __construct(
        private UserService $userService
    ) {}


    // public function execute(?string $claimTypeSlug = null): AnonymousResourceCollection|array
    // {
    //     [$limit, $order, $dir, $search, $page, $filters] = $this->getDataTableParams();
    //     $search ??= $this->escape_special_characters($search);
    //     $user = auth()->user();


    //     $claimTypeId = DB::table('claim_types')->where('slug', $claimTypeSlug ?? 'claim-for-catalogue-creation')->value('id');

    //     $query = DB::table('claims as c')
    //         ->leftJoin('team_snp_scheme as tss', 'tss.snp_id', '=', 'c.snp_id')
    //         ->leftJoin('team_msme_schemes as tms', 'tms.udyam_no', '=', 'c.msme_udyam_number')
    //         ->leftJoin('attribute_values as atr', 'atr.id', '=', 'c.msme_transaction_type');

    //     if (hasRole('lsp')) {
    //         $query->leftJoin('users as u', 'u.id', '=', 'c.created_by');

    //         $query->select(
    //             'c.id',
    //             'c.snp_id',
    //             DB::raw('COALESCE(tss.snp_name, CONCAT(u.first_name, " ", IFNULL(u.last_name,""))) as snp_name'),
    //             'c.application_number',
    //             'c.team_id as team_registration_id',
    //             'tms.udyam_no as msme_udyam_number',
    //             'tms.entrepreneur_name as msme_name',
    //             'tms.msme_classification',
    //             'tms.major_activity',
    //             'atr.attribute_value as target_customer',
    //             'c.bpp_id',
    //             'c.onboarding_date',
    //             'c.status',
    //             'c.is_bulk',
    //             'c.amount',
    //             'c.gst_charge',
    //             'c.gst_charge_amount',
    //             'c.date_of_sku_update',
    //             DB::raw('"NA" as subdomain_names')
    //         );
    //     } else {
    //         $query->select(
    //             'c.id',
    //             'c.snp_id',
    //             'tss.snp_name as snp_name', // Directly use tss.snp_name without COALESCE
    //             'c.application_number',
    //             'tms.team_id as team_registration_id',
    //             'tms.udyam_no as msme_udyam_number',
    //             'tms.entrepreneur_name as msme_name',
    //             'tms.msme_classification',
    //             'tms.major_activity',
    //             'atr.attribute_value as target_customer',
    //             'c.bpp_id',
    //             'c.onboarding_date',
    //             'c.status',
    //             'c.is_bulk',
    //             'c.amount',
    //             'c.gst_charge',
    //             'c.gst_charge_amount',
    //             'c.date_of_sku_update',
    //             DB::raw('"NA" as subdomain_names')
    //         );
    //     }



    //     // if (isset($filters)) {
    //     //     if (hasRole('ondc-admin')) {
    //     //         if (isset($filters['review_status']) && !empty($filters['review_status'])) {
    //     //             if ($filters['review_status'] === 'Pending') {
    //     //                 $query->whereIn('c.ondc_review_status', [
    //     //                     ClaimReviewStatus::SUBMITTED->value,
    //     //                     ClaimReviewStatus::PENDING->value,
    //     //                     ClaimReviewStatus::FORWARDED->value,
    //     //                 ]);
    //     //             } else {
    //     //                 $action = ClaimReviewStatus::getIdByName($filters['review_status']);
    //     //                 $query->where('c.ondc_review_status', $action);
    //     //             }
    //     //         }
    //     //     }


    //     //     if (hasRole('nsic')) {
    //     //         if (isset($filters['review_status']) && !empty($filters['review_status'])) {
    //     //             if ($filters['review_status'] === 'Pending') {
    //     //                 $query->whereIn('c.nsic_review_status', [
    //     //                     ClaimReviewStatus::SUBMITTED->value,
    //     //                     ClaimReviewStatus::PENDING->value,
    //     //                     ClaimReviewStatus::FORWARDED->value,
    //     //                 ]);
    //     //             } else {
    //     //                 $action = ClaimReviewStatus::getIdByName($filters['review_status']);
    //     //                 $query->where('c.nsic_review_status', $action);
    //     //             }
    //     //         }
    //     //     }

    //     //     if (hasRole('nsic-finance')) {
    //     //         if (isset($filters['review_status']) && !empty($filters['review_status'])) {
    //     //             if ($filters['review_status'] === 'Pending') {
    //     //                 $query->whereIn('c.nsicfinance_review_status', [
    //     //                     ClaimReviewStatus::SUBMITTED->value,
    //     //                     ClaimReviewStatus::PENDING->value,
    //     //                     ClaimReviewStatus::FORWARDED->value,
    //     //                 ]);
    //     //             } else {

    //     //                 $action = ClaimReviewStatus::getIdByName($filters['review_status']);
    //     //                 $query->where('c.nsicfinance_review_status', $action);
    //     //             }
    //     //         }
    //     //     }

    //     //     if (hasRole('snp') || hasRole('bnp') || hasRole('administrator') || hasRole('lsp')) {
    //     //         if (isset($filters['review_status']) && !empty($filters['review_status'])) {
    //     //             if ($filters['review_status'] === 'Pending') {

    //     //                 $query->whereIn('c.claim_status', [
    //     //                     //ClaimReviewStatus::SUBMITTED->value,
    //     //                     ClaimStatus::PENDING->value,
    //     //                     //ClaimReviewStatus::FORWARDED->value,
    //     //                 ]);
    //     //             } else {
    //     //                 // $action = ClaimReviewStatus::getIdByName($filters['review_status']);
    //     //                 // $query->where('c.status', $action);

    //     //                 $query->where('c.claim_status', ClaimStatus::getIdByLabel($filters['review_status']));
    //     //             }
    //     //         }
    //     //     }

    //     //     if (isset($filters['is_bulk'])) {
    //     //         $query->where('is_bulk', $filters['is_bulk']);
    //     //     }
    //     // }


    //     // if ($search) {
    //     //     $query->where(function ($query) use ($search) {
    //     //         $query
    //     //             ->where('c.snp_id', 'like', "$search%")
    //     //             ->orWhere('c.team_registration_id', 'like', "$search%")
    //     //             ->orWhere('c.msme_udyam_number', 'like', "$search%")
    //     //             ->orWhere('c.msme_name', 'like', "$search%")
    //     //             ->orWhere('c.amount', 'like', "$search%")
    //     //             ->orWhere('c.gst_charge', 'like', "$search%")
    //     //             ->orWhere('c.gst_charge_amount', 'like', "$search%")
    //     //             ->orWhere('c.application_number', 'like', "$search%")
    //     //             ->orWhere('tss.snp_name', 'like', "$search%")
    //     //             ->orWhere('atr.attribute_value', 'like', "$search%")
    //     //             ->orWhere('tms.major_activity', 'like', "$search%")
    //     //             ->orWhere('c.bpp_id', 'like', "$search%")
    //     //             ->orWhere('co.item_consolidated_category', 'like', "$search%")
    //     //             ->orWhere('c.amount', 'like', "$search%")

    //     //             ->orWhereRaw("DATE_FORMAT(c.onboarding_date, '%d-%m-%Y') like ?", ["$search%"]);
    //     //     });
    //     // }




    //     $query->where('c.claim_type_id', $claimTypeId);
    //     $query->where('c.status', '!=', ClaimReviewStatus::TEMPORARY->value);
    //     $query->where('c.created_by', authId());
    //     $query->orderBy('c.created_at', 'desc');

    //     dd($query->paginate($limit));

    //     if ($page) {
    //         return $this->getDataTableResult(
    //             ClaimResource::collection($query->paginate($limit))
    //         );
    //     }

    //     return ClaimResource::collection($query->get());
    // }

    public function execute(?string $claimTypeSlug = null): AnonymousResourceCollection|array
    {
        //dd($claimTypeSlug);
        [$limit, $order, $dir, $search, $page, $filters] = $this->getDataTableParams();
        $search ??= $this->escape_special_characters($search);
        $user = auth()->user();

        // $workflowTypeId = DB::table('workflow_types')
        //     ->where('slug', $claimTypeSlug ?? 'claim-for-catalogue-creation')
        //     ->value('id');

        $claimTypeId = DB::table('claim_types')->where('slug', $claimTypeSlug ?? 'claim-for-catalogue-creation')->value('id');
        //  dd($claimTypeId , $claimTypeSlug);
        // Start building the query

        $query = DB::table('claims as c')
            ->leftJoin('attribute_values as atr', 'atr.id', '=', 'c.msme_transaction_type')
            ->leftJoin('claim_orders as co', 'c.id', '=', 'co.claim_id');




        // Conditionally join users table and select user fields for LSP users
        if (hasRole('lsp') || hasRole('bnp')) {
            $query->leftJoin('users as u', 'u.id', '=', 'c.created_by');
            $query->leftJoin('team_snp_scheme as tss', 'tss.snp_id', '=', 'c.snp_id');
            $query->leftJoin('team_msme_schemes as tms', 'tms.udyam_no', '=', 'c.msme_udyam_number');
            $query->leftJoin('sub_domains as sud', function ($join) {
                $join->whereRaw("JSON_CONTAINS(tms.product_category_id, JSON_QUOTE(sud.id))");
            });


            // For LSP users, include user name in snp_name
            $query->select(
                'c.id',
                'c.snp_id',
                // DB::raw('COALESCE(tss.snp_name, CONCAT(u.first_name, " ", IFNULL(u.last_name,""))) as snp_name'),
                "tss.organization_name as snp_name",
                'c.application_number',
                'tms.team_id as team_registration_id',
                'tms.udyam_no as msme_udyam_number',
                'tms.entrepreneur_name as msme_name',
                'tms.msme_classification',
                'tms.major_activity',
                'atr.attribute_value as target_customer',
                'c.bpp_id',
                'c.onboarding_date',
                'c.status',
                'c.is_bulk',
                'c.amount',
                'c.gst_percentage',
                'c.gst_amount',
                'c.tds_percentage',
                'c.tds_amount',
                'c.sgst_percentage',
                'c.sgst_amount',
                'c.cgst_percentage',
                'c.cgst_amount',
                'c.cgst_tds_amount',
                'c.sgst_tds_amount',
                'c.date_of_sku_update',
                DB::raw("GROUP_CONCAT(co.item_consolidated_category) as subdomain_names")
            );
        } else {
            // For non-LSP users, only use tss.snp_name without user fields
            $query
                ->join('team_snp_scheme as tss', 'tss.snp_id', '=', 'c.snp_id')
                ->join('team_msme_schemes as tms', 'tms.udyam_no', '=', 'c.msme_udyam_number')
                ->leftJoin('sub_domains as sud', function ($join) {
                    $join->whereRaw("JSON_CONTAINS(tms.product_category_id, JSON_QUOTE(sud.id))");
                })
                ->select(
                    'c.id',
                    'c.snp_id',
                    'tss.organization_name as snp_name', // Directly use tss.snp_name without COALESCE
                    'c.application_number',
                    'c.team_id as team_registration_id',
                    'c.msme_udyam_number as msme_udyam_number',
                    'c.msme_name as msme_name',
                    'c.msme_classification',
                    'c.msme_category as major_activity',
                    'atr.attribute_value as target_customer',
                    'c.bpp_id',
                    'c.onboarding_date',
                    'c.status',
                    'c.is_bulk',
                    'c.amount',
                    'c.gst_percentage',
                    'c.gst_amount',
                    'c.tds_percentage',
                    'c.tds_amount',
                    'c.sgst_percentage',
                    'c.sgst_amount',
                    'c.cgst_percentage',
                    'c.cgst_amount',
                    'c.cgst_tds_amount',
                    'c.sgst_tds_amount',
                    'c.date_of_sku_update',
                    'tms.udyam_no as udyam_number_2',
                    'tms.enterprise_name as msme_name_2',
                    'tms.major_activity as major_activity_2',

                    DB::raw("GROUP_CONCAT(co.item_consolidated_category) as subdomain_names")
                );
        }

        $query->groupBy(
            'c.id',
            'c.snp_id',
            'tss.organization_name',
            'c.application_number',
            'c.team_id',
            'c.msme_udyam_number',
            'c.msme_name',
            'c.msme_classification',
            'c.msme_category',
            'atr.attribute_value',
            'c.bpp_id',
            'c.onboarding_date',
            'c.status',
            'c.is_bulk',
            'c.amount',
            'c.gst_percentage',
            'c.gst_amount',
            'c.tds_percentage',
            'c.tds_amount',
            'c.sgst_percentage',
            'c.sgst_amount',
            'c.cgst_percentage',
            'c.cgst_amount',
             'c.cgst_tds_amount',
            'c.sgst_tds_amount',
            'c.date_of_sku_update',
            'tms.udyam_no',
            'tms.enterprise_name',
            'tms.major_activity',
        );

        // ->distinct();

        //if (acl('claim-view') && !hasRole('snp')) {
        // $userRoles = $this->userService->getUserRoles($user->id);
        //$query->join('workflow_logs AS swl', 'swl.application_id', '=', 'c.id');
        //$query->join('workflows AS sw', 'swl.workflow_id', '=', 'sw.id');
        //$query->where('sw.workflow_type_id', $workflowTypeId);
        // $query->whereIn('sw.role_id', $userRoles);
        //}

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

            //if(hasRole('nsic-pmu')){

            //if (isset($filters['review_status'])) {
            // if ($filters['review_status'] === 'Pending') {
            //$query->whereIn('c.nsicpmu_review_status', [
            //ClaimReviewStatus::SUBMITTED->value,
            // ClaimReviewStatus::PENDING->value,
            // ClaimReviewStatus::FORWARDED->value,
            //]);
            // } else {
            //$action = ClaimReviewStatus::getIdByName($filters['review_status']);
            //$query->where('c.nsicpmu_review_status', $action);
            //}
            //}
            //}
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

            if (hasRole('snp') || hasRole('bnp') || hasRole('administrator') || hasRole('lsp')) {
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

        //New Code end

        //if (isset($filters)) {
        //if (isset($filters['review_status'])) {
        //if ($filters['review_status'] === 'Pending') {
        //$query->whereIn('c.status', [
        // ClaimReviewStatus::SUBMITTED->value,
        //ClaimReviewStatus::PENDING->value,
        //ClaimReviewStatus::FORWARDED->value,
        //]);
        //} else {
        //  $action = ClaimReviewStatus::getIdByName($filters['review_status']);
        // $query->where('c.status', $action);
        //}
        //}


        //}

        if ($search) {
            $query->where(function ($query) use ($search) {
                $query
                    ->where('c.snp_id', 'like', "$search%")
                    ->orWhere('c.team_registration_id', 'like', "$search%")
                    ->orWhere('c.msme_udyam_number', 'like', "$search%")
                    ->orWhere('c.msme_name', 'like', "$search%")
                    ->orWhere('c.amount', 'like', "$search%")
                    // ->orWhere('c.gst_charge', 'like', "$search%")
                    // ->orWhere('c.gst_charge_amount', 'like', "$search%")
                    ->orWhere('c.application_number', 'like', "$search%")
                    ->orWhere('tss.organization_name', 'like', "$search%")
                    ->orWhere('tms.msme_classification', 'like', "$search%")
                    ->orWhere('atr.attribute_value', 'like', "$search%")
                    ->orWhere('tms.major_activity', 'like', "$search%")
                    ->orWhere('c.bpp_id', 'like', "$search%")
                    ->orWhere('co.item_consolidated_category', 'like', "$search%")
                    ->orWhere('c.amount', 'like', "$search%")

                    ->orWhereRaw("DATE_FORMAT(c.onboarding_date, '%d-%m-%Y') like ?", ["$search%"]);
            });
        }


        $query->where('c.claim_type_id', $claimTypeId);
        $query->where('c.status', '!=', ClaimReviewStatus::TEMPORARY->value);

        if (hasRole('snp') || hasRole('bnp') || hasRole('lsp')) {
            $query->where(function ($query) {
                $query
                    ->where('c.created_by', authId())
                    ->orWhere('c.created_by', auth()->user()->parent_user_id);
            });
        }

        $query->orderBy('c.id', 'desc');
        // dd($query->toSql());


        if ($page) {
            return $this->getDataTableResult(
                ClaimResource::collection($query->paginate($limit))
            );
        }

        return ClaimResource::collection($query->get());
    }
}
