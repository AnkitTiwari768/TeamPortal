<?php

declare(strict_types=1);

namespace App\Domain\MSE;

use App\Traits\DataTable;
use Illuminate\Support\Facades\DB;

class ListOnboardedMseAction
{
    use DataTable;

    protected array $columns = [
        1 => 'team_id',
        2 => 'udyam_no',
        3 => 'mobile',
        4 => 'email',
        5 => 'entrepreneur_name',
        6 => 'enterprise_name',
        7 => 'organisation_type',
        8 => 'created_at'
    ];

    public function execute(
        ?bool $isClaimForCatalogueCreation = false,
        ?bool $isClaimForAccountManagement = false,
        ?bool $isClaimForPackaging = false
    ) {

        [$limit, $order, $dir, $search, $page, $filters] = $this->getDataTableParams();

        $startDate = $filters['from_date'] ?? null;
        $endDate = $filters['to_date'] ?? null;

        $search ??= $this->escape_special_characters($search);

        $query = DB::table('team_msme_schemes as ms')
            ->select(
                'ms.id',
                'ms.team_id',
                'ms.udyam_no',
                'ms.mobile',
                'ms.email',
                'ms.entrepreneur_name',
                'ms.enterprise_name',
                'ms.organisation_type',
                'ms.msme_classification',
                'ms.social_category',
                'ms.created_at',
                's.name as state_name',
                'ms.is_msme_registration',
                'ms.bpp_id',
                'ms.major_activity',
                'ia.organization_name as association_name',
                'creator_snp.organization_name as creator_snp_name',
                'av.attribute_value as transaction_type'
            )
            ->join('team_snpmsme_mapping as tsm', 'tsm.msme_id', '=', 'ms.id')
            ->join('team_snp_scheme as tss', 'tsm.snp_id', '=', 'tss.id')
            ->leftJoin('states as s', 's.id', '=', 'ms.state_id')
            ->leftJoin('industrial_associations as ia', 'ia.user_id', '=', 'ms.created_by')
            ->leftJoin('attribute_values as av', 'av.id', '=', 'ms.ondc_transaction_type_id')
            ->leftJoin('team_snp_scheme as creator_snp', 'creator_snp.user_id', '=', 'ms.created_by');

        if (hasRole('snp')) {

            $query->where(function ($q) {

                $q->where('tss.user_id', (string) authId());

                if (auth()->user()->parent_user_id) {

                    $q->orWhere(
                        'tss.user_id',
                        (string) auth()->user()->parent_user_id
                    );
                }
            });
        }

        $query->where('tsm.status', 1);

        $query->whereNotNull('ms.major_activity')
            ->where('ms.major_activity', '!=', '');

        if ($startDate) {

            $startDate = date('Y-m-d', strtotime($startDate));

            $query->where('ms.created_at', '>=', $startDate);
        }

        if ($endDate) {

            $endDate = (new \DateTime($endDate))
                ->modify('+1 days')
                ->format('Y-m-d');

            $query->where('ms.created_at', '<', $endDate);
        }

        if (
            isset($filters['ondc_transaction_type_id']) &&
            !empty($filters['ondc_transaction_type_id'])
        ) {

            $query->where(
                'ondc_transaction_type_id',
                $filters['ondc_transaction_type_id']
            );
        }

        if (
            !empty($filters['product_category_id']) &&
            is_array($filters['product_category_id'])
        ) {

            $query->where(function ($q) use ($filters) {

                foreach ($filters['product_category_id'] as $categoryId) {

                    $q->orWhereJsonContains(
                        'ms.product_category_id',
                        $categoryId
                    );
                }
            });
        }

        if ($search) {

            $query->where(function ($query) use ($search) {

                $query->where('ms.team_id', 'like', "%$search%")
                    ->orWhere('ms.udyam_no', 'like', "%$search%")
                    ->orWhere('ms.mobile', 'like', "%$search%")
                    ->orWhere('ms.email', 'like', "%$search%")
                    ->orWhere('ms.bpp_id', 'like', "%$search%")
                    ->orWhere('ms.entrepreneur_name', 'like', "%$search%")
                    ->orWhere('ms.enterprise_name', 'like', "%$search%")
                    ->orWhere('ms.organisation_type', 'like', "%$search%")
                    ->orWhere('ms.created_at', 'like', "%$search%")
                    ->orWhere('s.name', 'like', "%$search%")
                    ->orWhere('ms.msme_classification', 'like', "%$search%")
                    ->orWhereRaw(
                        "DATE_FORMAT(ms.created_at, '%d-%m-%Y') LIKE ?",
                        ["%$search%"]
                    )
                    ->orWhere('ia.organization_name', 'like', "%$search%")
                    ->orWhere('creator_snp.organization_name', 'like', "%$search%");

                if (str_contains(strtolower('Self'), strtolower($search))) {

                    $query->orWhere(function ($q) {

                        $q->whereNull('ia.organization_name')
                            ->whereNull('creator_snp.organization_name');
                    });
                }
            });
        }

        if ($isClaimForCatalogueCreation) {

            $query->whereNull('ms.is_catalogue_claim_generated');
        }

        if ($isClaimForAccountManagement) {

            $query->whereNull('ms.is_account_claim_generated');
        }

        if ($isClaimForPackaging) {

            $query->whereNull('ms.is_packaging_claim_generated');
        }

        $query->orderBy('ms.updated_at', 'desc');

        if ($page) {

            return $this->getDataTableResult(
                MseResource::collection($query->paginate($limit))
            );
        }

        return MseResource::collection($query->get());
    }
}