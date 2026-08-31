<?php
declare(strict_types = 1);

namespace App\Domain\MIS;

use App\Traits\DataTable;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use App\Domain\NetworkProvider\NetworkProviderStatus;

class SNPWiseMSMEMISReportAction
{
    use DataTable;

    private const CACHE_TTL_MINUTES = 5;

    private const MSME_CHUNK_SIZE = 1000;

    protected array $columns = [
        1 => 'snp_id',
        2 => 'organization_name',
        3 => 'msme_id',
        4 => 'mobile',
        5 => 'email',
        6 => 'udyam_no',
        7 => 'enterprise_name',
        8 => 'entrepreneur_name',
        9 => 'enterprise_type',
        10 => 'major_activity',
        11 => 'product_category',
        12 => 'state_name',
        13 => 'gender',
        14 => 'created_at',
    ];

    public function execute(? string $fromDate = null, ? string $toDate = null) : array
    {
        [$limit, $order, $dir, $search, , , $start] = $this->getDataTableParams();

        $rows = $this->buildReportRows($fromDate, $toDate);

        if ($search)
        {
            $needle = mb_strtolower($search);

            $rows = $rows->filter(function ($row) use ($needle)
            {
                foreach ($row as $value)
                {
                    if (is_scalar($value) && str_contains(mb_strtolower((string) $value), $needle))
                    {
                        return true;
                    }
                }

                return false;
            })
                ->values();
        }

        $total = $rows->count();

        if (in_array($order, $this->columns, true))
        {
            $rows = $rows->sortBy($order, SORT_REGULAR, strtolower($dir) === 'desc')
                ->values();
        }

        $page = $limit > 0 ? (int) floor($start / $limit) + 1 : 1;

        $items = $limit > 0 ? $rows->forPage($page, $limit)->values() : $rows;

        $data = $items->values()
            ->map(fn ($row, $i) => array_merge(['sn' => $start + $i + 1], $row))
            ->all();

        return ['draw' => intval(request()->input('draw')), 'recordsTotal' => $total, 'recordsFiltered' => $total, 'data' => $data, ];
    }

    /**
     * The MSME x SNP match computation below is the expensive part of this
     * report (bounded by MSME count, not by page size), so its result is
     * cached per from_date/to_date combination for a few minutes. Every
     * subsequent page/length/sort/search request against the same filter
     * then just slices the cached collection instead of recomputing it.
     */
    private function buildReportRows(? string $fromDate, ? string $toDate) : Collection
    {
        $cacheKey = 'snp_wise_msme_mis_report_' . md5(($fromDate ?? '') . '|' . ($toDate ?? ''));

        return Cache::remember($cacheKey, now()->addMinutes(self::CACHE_TTL_MINUTES), fn () => $this->computeReportRows($fromDate, $toDate));
    }

    private function computeReportRows(? string $fromDate, ? string $toDate) : Collection
    {
        $snps = $this->getMatchableSnps();

        $subDomainNames = DB::table('sub_domains')->pluck('name', 'id')
            ->toArray();

        $rows = [];

        $msmeQuery = DB::table('team_msme_schemes as ms')->leftJoin('states as st', 'st.id', '=', 'ms.state_id')
            ->select('ms.team_id', 'ms.mobile', 'ms.email', 'ms.udyam_no', 'ms.enterprise_name', 'ms.entrepreneur_name', 'ms.gender', 'ms.state_id', 'st.name as state_name', 'ms.major_activity', 'ms.product_category_id', 'ms.ondc_transaction_type_id', 'ms.enterprise_details', 'ms.created_at')
            ->whereNotNull('ms.state_id');

        if (!empty($fromDate))
        {

            $msmeQuery->whereDate('ms.created_at', '>=', date('Y-m-d', strtotime($fromDate)));
        }

        if (!empty($toDate))
        {

            $msmeQuery->whereDate('ms.created_at', '<=', date('Y-m-d', strtotime($toDate)));
        }

        // Chunk by primary key so scanning a large MSME set never pulls the
        // whole result into memory at once - each chunk is matched against
        // the (already decoded) SNP list and flattened into $rows.
        $msmeQuery->chunkById(self::MSME_CHUNK_SIZE, function (Collection $msmes) use (&$rows, $snps, $subDomainNames)
        {
            foreach ($msmes as $msme)
            {
                foreach ($this->matchRowsForMsme($msme, $snps, $subDomainNames) as $row)
                {
                    $rows[] = $row;
                }
            }
        }, 'ms.team_id', 'team_id');

        return collect($rows);
    }

    /**
     * Each SNP's state/transaction-type JSON is decoded exactly once here,
     * not once per MSME. The previous implementation re-decoded both JSON
     * columns for every SNP on every MSME iteration (O(msmes x snps) decode
     * calls), which dominated the report's runtime.
     */
    private function getMatchableSnps() : array
    {
        return DB::table('team_snp_scheme as ss')->join('network_providers as np', function ($join)
        {
            $join->on('np.id', '=', 'ss.network_provider_id')
                ->where('np.status', NetworkProviderStatus::APPROVE->value);
        })
            ->select('np.np_team_id as snp_id', 'ss.snp_name', 'ss.organization_name', 'ss.state_id', 'ss.transaction_type')
            ->get()
            ->map(fn ($snp) => ['snp_id' => $snp->snp_id, 'snp_name' => $snp->snp_name, 'organization_name' => $snp->organization_name, 'state_ids' => array_map('strval', json_decode((string) $snp->state_id, true) ? : []), 'txn_ids' => array_map('strval', json_decode((string) $snp->transaction_type, true) ? : []), ])
            ->all();
    }

    private function matchRowsForMsme($msme, array $snps, array $subDomainNames) : array
    {
        $stateIds = $msme->state_id ? [(string) $msme->state_id] : [];

        $txnTypeIds = $msme->ondc_transaction_type_id ? [(string) $msme->ondc_transaction_type_id] : [];

        $enterpriseType = '';

        if (!empty($msme->enterprise_details))
        {

            $details = json_decode($msme->enterprise_details, true);

            $enterpriseType = data_get($details, 'EType.0.@attributes.EnterpriseTypee', '');
        }

        $productNames = $this->getProductCategories($msme->product_category_id, $subDomainNames);

        $baseData = ['msme_id' => $msme->team_id, 'team_id' => $msme->team_id, 'mobile' => $msme->mobile, 'email' => $msme->email, 'udyam_no' => $msme->udyam_no, 'entrepreneur_name' => $msme->entrepreneur_name, 'enterprise_name' => $msme->enterprise_name, 'enterprise_type' => $enterpriseType, 'major_activity' => $msme->major_activity, 'state_name' => $msme->state_name, 'gender' => $msme->gender, 'created_at' => $msme->created_at, 'product_category' => $productNames, ];

        $matchedSnps = [];

        foreach ($snps as $snp)
        {

            $stateMatch = empty($stateIds) || !empty(array_intersect($stateIds, $snp['state_ids']));

            $txnMatch = empty($txnTypeIds) || !empty(array_intersect($txnTypeIds, $snp['txn_ids']));

            if ($stateMatch && $txnMatch)
            {

                $matchedSnps[] = $snp;
            }
        }

        if (empty($matchedSnps))
        {
            return [array_merge($baseData, ['snp_id' => 'No SNP Found', 'snp_name' => '-', 'organization_name' => '-', ])];
        }

        return array_map(fn ($snp) => array_merge($baseData, ['snp_id' => $snp['snp_id'], 'snp_name' => $snp['snp_name'], 'organization_name' => $snp['organization_name'], ]), $matchedSnps);
    }

    private function getProductCategories($productCategoryIds, array $subDomainNames) : string
    {
        if (empty($productCategoryIds))
        {
            return '';
        }

        $ids = json_decode($productCategoryIds, true) ? : [];

        $names = [];

        foreach ($ids as $id)
        {

            if (isset($subDomainNames[$id]))
            {
                $names[] = $subDomainNames[$id];
            }
        }

        return implode(', ', array_unique($names));
    }
}
