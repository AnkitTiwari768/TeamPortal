<?php

namespace App\Web\Msme;

use Illuminate\Support\Facades\DB;
use App\Domain\NetworkProvider\NetworkProviderStatus;

class MsmeSnpService
{
    public function getMsmeWithSnpList(): array
    {
        $results = [];

        // ✅ 1. MSME DATA (single query)
        $msmes = DB::table('team_msme_schemes')
            ->select(
                'team_id',
                'mobile',
                'udyam_no',
                'enterprise_name',
                'state_id',
                'product_category_id',
                'ondc_transaction_type_id',
                'created_at'
            )
            ->whereNotNull('state_id')
            ->get();

        // ✅ 2. SNP DATA (single query only ONCE)
        $snps = DB::table('team_snp_scheme as ss')
            ->select(
                'np.np_team_id as snp_id',
                'ss.id',
                'ss.snp_name',
                'ss.organization_name',
                'ss.created_at',
                'ss.state_id',
                'ss.transaction_type',
                'ss.sub_domain'
            )
            ->join('network_providers as np', function ($join) {
                $join->on('np.id', '=', 'ss.network_provider_id')
                    ->where('np.status', NetworkProviderStatus::APPROVE->value);
            })
            ->get();

        // ✅ 3. PRELOAD PRODUCT CATEGORY NAMES (NO LOOP QUERY)
        $subDomainNames = DB::table('sub_domains')
            ->pluck('name', 'id')
            ->toArray();

        foreach ($msmes as $msme) {

            // MSME filters
            $stateIds = $msme->state_id ? [$msme->state_id] : [];
            $txnTypeIds = $msme->ondc_transaction_type_id ? [$msme->ondc_transaction_type_id] : [];

            $matchedSnps = [];

            foreach ($snps as $snp) {

                // decode once
                $snpStates = json_decode($snp->state_id, true) ?? [];
                $snpTxns   = json_decode($snp->transaction_type, true) ?? [];

                // ✅ FAST PHP MATCHING (NO SQL JSON_CONTAINS)
                $stateMatch = empty($stateIds) || array_intersect($stateIds, $snpStates);
                $txnMatch   = empty($txnTypeIds) || array_intersect($txnTypeIds, $snpTxns);

                if ($stateMatch && $txnMatch) {
                    $matchedSnps[] = [
                        'snp_id' => $snp->snp_id,
                        'snp_name' => $snp->snp_name,
                        'organization_name' => $snp->organization_name,
                        'created_at' => $snp->created_at,
                    ];
                }
            }

            // ✅ PRODUCT CATEGORY NAME MAP
            $productNames = $this->getProductCategories($msme->product_category_id, $subDomainNames);

            $results[] = [
                'msme_id' => $msme->team_id,
                'mobile' => $msme->mobile,
                'udyam_no' => $msme->udyam_no,
                'enterprise_name' => $msme->enterprise_name,
                'product_category_id' => $productNames,
                'created_at' => $msme->created_at,
                'snps' => $matchedSnps,
                
            ];
        }

        return $results;
    }

    // ✅ FAST CATEGORY RESOLVER (NO DB CALL INSIDE LOOP)
    public function getProductCategories($productCategoryIds, array $subDomainNames)
    {
        if (!$productCategoryIds) {
            return '';
        }

        $ids = json_decode($productCategoryIds, true) ?? [];

        $names = [];

        foreach ($ids as $id) {
            if (isset($subDomainNames[$id])) {
                $names[] = $subDomainNames[$id];
            }
        }

        return implode(', ', $names);
    }
}