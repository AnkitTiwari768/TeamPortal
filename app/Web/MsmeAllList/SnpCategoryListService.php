<?php

declare(strict_types=1);

namespace App\Web\MsmeAllList;

use App\Core\BaseService;
use App\Domain\NetworkProvider\NetworkProviderStatus;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

/**
 * network_providers.role_selection_details stores one JSON array per
 * network provider, each element describing a single role/category/
 * transaction-type/serviceability combination. MySQL on this environment
 * is 5.7 (no JSON_TABLE), so the array is decoded and flattened in PHP -
 * one output row per JSON element - then filtered, sorted and paginated
 * here rather than in SQL.
 */
class SnpCategoryListService extends BaseService
{
    protected array $columns = [
        1 => 'np_team_id',
        2 => 'organization_name',
        3 => 'role_name',
        4 => 'category',
        5 => 'transaction_type',
        6 => 'serviceability',
    ];

    /**
     * Maps the Role filter's short code to the exact role_name text stored
     * inside role_selection_details (e.g. "Seller Network Participant (SNP)").
     */
    private const ROLE_LABELS = [
        'BNP' => 'Buyer Network Participant (BNP)',
        'SNP' => 'Seller Network Participant (SNP)',
        'LSP' => 'Logistics Service Provider (LSP)',
    ];

    /**
     * "BOTH" is not a real transaction_type_name value inside
     * role_selection_details - every JSON entry is either B2B or B2C.
     * It means "don't filter by transaction type" (same as leaving the
     * dropdown on "All").
     */
    private const TRANSACTION_TYPES = [
        'B2C' => 'Business to Consumer (B2C)',
        'B2B' => 'Business to Business (B2B)',
        'BOTH' => 'Both',
    ];

    public function getDropdownList(): array
    {
        return [
            'roles' => self::ROLE_LABELS,
            'transaction_types' => self::TRANSACTION_TYPES,
            'categories' => $this->getDistinctCategories(),
        ];
    }

    public function getSnpCategoryList()
    {
        [$limit, $order, $dir, $search, $page, $filters] = $this->getDataTableParams();
        $search = $search ? $this->escape_special_characters($search) : null;

        $records = $this->buildFlattenedRecords($filters ?? []);

        if ($search) {
            $needle = mb_strtolower($search);
            $records = $records->filter(function (array $row) use ($needle) {
                foreach (['np_team_id', 'organization_name', 'role_name', 'category', 'transaction_type', 'serviceability'] as $field) {
                    if (str_contains(mb_strtolower((string) ($row[$field] ?? '')), $needle)) {
                        return true;
                    }
                }

                return false;
            })->values();
        }

        $records = $this->sortRecords($records, $order, $dir);
        $openMsmeTotal = $records->sum('open_msme_count');

        if ($page) {
            $result = $this->getDataTableResult(
                SnpCategoryListResource::collection($this->paginate($records, $limit))
            );
            $result['open_msme_total'] = $openMsmeTotal;

            return $result;
        }

        return SnpCategoryListResource::collection($records->values());
    }

    /**
     * Filtered dataset (no pagination, no search) for the Excel/PDF exports,
     * so a download always reflects the same filters as the on-screen list.
     * $limit caps how many rows are fetched - used by the PDF export, since
     * dompdf's table renderer runs out of memory on very large datasets.
     */
    public function getExportRows(?int $limit = null): Collection
    {
        [, , , , , $filters] = $this->getDataTableParams();

        $records = $this->sortRecords($this->buildFlattenedRecords($filters ?? []), 'np_team_id', 'ASC');

        return $limit ? $records->take($limit)->values() : $records;
    }

    public function getExportRowCount(): int
    {
        [, , , , , $filters] = $this->getDataTableParams();

        return $this->buildFlattenedRecords($filters ?? [])->count();
    }

    private function sortRecords(Collection $records, string $order, string $dir): Collection
    {
        $sortKey = in_array($order, $this->columns, true) ? $order : 'np_team_id';

        $sorted = $records->sortBy(
            fn (array $row) => (string) ($row[$sortKey] ?? ''),
            SORT_NATURAL | SORT_FLAG_CASE
        );

        return strtoupper($dir) === 'DESC' ? $sorted->reverse()->values() : $sorted->values();
    }

    private function paginate(Collection $records, int $perPage): LengthAwarePaginator
    {
        $perPage = $perPage > 0 ? $perPage : max($records->count(), 1);
        $currentPage = LengthAwarePaginator::resolveCurrentPage();
        $items = $records->slice(($currentPage - 1) * $perPage, $perPage)->values();

        return new LengthAwarePaginator($items, $records->count(), $perPage, $currentPage, [
            'path' => LengthAwarePaginator::resolveCurrentPath(),
        ]);
    }

    /**
     * Flattens every approved network provider's role_selection_details
     * JSON array into one record per role/category/transaction-type entry,
     * applies the Role / Category / Transaction Type filters, and drops
     * exact duplicate combinations (the same np_team_id + role + category +
     * transaction type + serviceability can legitimately repeat inside the
     * source JSON).
     */
    private function buildFlattenedRecords(array $filters): Collection
    {
        $role = strtoupper((string) ($filters['role'] ?? ''));
        $category = trim((string) ($filters['category'] ?? ''));
        $transactionType = strtoupper((string) ($filters['transaction_type'] ?? ''));
        $npTeamId = trim((string) ($filters['np_team_id'] ?? ''));
        $openMsmeCounts = $this->getOpenMsmeCountsByCategory();

        $query = DB::table('network_providers')
            ->where('status', NetworkProviderStatus::APPROVE->value)
            ->whereNotNull('role_selection_details')
            ->where('role_selection_details', '!=', '[]');

        if ($npTeamId !== '') {
            // np_team_id is a plain column (not inside the JSON), so this is
            // an exact SQL filter - no post-decode re-check needed.
            $query->where('np_team_id', 'like', '%' . str_replace(['%', '_'], ['\\%', '\\_'], $npTeamId) . '%');
        }

        if ($role !== '' && isset(self::ROLE_LABELS[$role])) {
            // Coarse pre-filter on the plain-text role_names column so fewer
            // rows need JSON-decoding below; the exact match against the
            // decoded role_name still happens per item further down.
            $query->where('role_names', 'like', '%(' . $role . ')%');
        }

        if ($category !== '') {
            // Coarse pre-filter on the raw JSON text; the exact
            // case-insensitive match happens per item further down.
            $query->where('role_selection_details', 'like', '%' . $category . '%');
        }

        $rows = $query->get(['np_team_id', 'organization_name', 'role_selection_details']);

        $seen = [];
        $records = collect();

        foreach ($rows as $row) {
            $items = json_decode((string) $row->role_selection_details, true);

            if (!is_array($items)) {
                continue;
            }

            foreach ($items as $item) {
                if (!is_array($item)) {
                    continue;
                }

                $itemRoleName = trim((string) ($item['role_name'] ?? ''));
                $itemCategory = trim((string) ($item['domain_name'] ?? ''));
                $itemTransactionType = trim((string) ($item['transaction_type_name'] ?? ''));

                if ($itemRoleName === '' || $itemCategory === '') {
                    continue;
                }

                if ($role !== '' && !str_contains(strtoupper($itemRoleName), "($role)")) {
                    continue;
                }

                if ($category !== '' && strcasecmp($itemCategory, $category) !== 0) {
                    continue;
                }

                if ($transactionType !== '' && $transactionType !== 'BOTH'
                    && !str_contains(strtoupper($itemTransactionType), $transactionType)) {
                    continue;
                }

                $roleCode = $this->extractRoleCode($itemRoleName);

                $dedupeKey = implode('|', [
                    $row->np_team_id,
                    $roleCode,
                    $itemCategory,
                    $itemTransactionType,
                    $item['serviceability_name'] ?? '',
                ]);

                if (isset($seen[$dedupeKey])) {
                    continue;
                }
                $seen[$dedupeKey] = true;

                $records->push([
                    'np_team_id' => $row->np_team_id,
                    'organization_name' => $row->organization_name,
                    'role_code' => $roleCode,
                    'role_name' => $itemRoleName,
                    'category' => $itemCategory,
                    'transaction_type' => $itemTransactionType,
                    'ondc_domain_mapping' => $item['ondc_domain_mapping'] ?? null,
                    'serviceability' => $item['serviceability_name'] ?? null,
                    'status_name' => $item['status_name'] ?? null,
                    'open_msme_count' => $openMsmeCounts[$itemCategory] ?? 0,
                ]);
            }
        }

        return $records;
    }

    private function extractRoleCode(string $roleName): string
    {
        foreach (self::ROLE_LABELS as $code => $label) {
            if (strcasecmp($roleName, $label) === 0 || str_contains(strtoupper($roleName), "($code)")) {
                return $code;
            }
        }

        return '';
    }

    /**
     * Open MSME count per category (sub_domains.name), keyed by category
     * name so it can be looked up against the domain_name values decoded
     * from role_selection_details. Mirrors the "open_msme" definition used
     * by App\Domain\MIS\ProductCategoryMISReportAction and
     * CategoryWiseCountService: not yet chosen by an SNP (select_snp = 0),
     * not already picked up by a BPP (bpp_id IS NULL), with a major_activity
     * set.
     */
    private function getOpenMsmeCountsByCategory(): array
    {
        return DB::table('sub_domains as sd')
            ->leftJoin('team_msme_schemes as ms', function ($join) {
                $join->whereRaw('JSON_CONTAINS(ms.product_category_id, JSON_QUOTE(CAST(sd.id AS CHAR)))');
            })
            ->selectRaw("
                sd.name AS category,
                COUNT(DISTINCT CASE
                    WHEN ms.select_snp = 0
                     AND ms.bpp_id IS NULL
                     AND ms.major_activity IS NOT NULL
                     AND ms.major_activity <> ''
                    THEN ms.id
                END) AS open_msme_count
            ")
            ->groupBy('sd.name')
            ->pluck('open_msme_count', 'category')
            ->map(fn ($count) => (int) $count)
            ->toArray();
    }

    private function getDistinctCategories(): array
    {
        $categories = [];

        DB::table('network_providers')
            ->where('status', NetworkProviderStatus::APPROVE->value)
            ->whereNotNull('role_selection_details')
            ->where('role_selection_details', '!=', '[]')
            ->pluck('role_selection_details')
            ->each(function ($json) use (&$categories) {
                $items = json_decode((string) $json, true);

                if (!is_array($items)) {
                    return;
                }

                foreach ($items as $item) {
                    $name = trim((string) ($item['domain_name'] ?? ''));
                    if ($name !== '') {
                        $categories[$name] = $name;
                    }
                }
            });

        ksort($categories, SORT_NATURAL | SORT_FLAG_CASE);

        return $categories;
    }
}
