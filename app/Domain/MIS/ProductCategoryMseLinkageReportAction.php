<?php

declare(strict_types=1);

namespace App\Domain\MIS;

use App\Traits\DataTable;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

/**
 * Product-category-wise MIS report: onboarded MSE count per product category,
 * broken down by how many of those onboarded MSEs are linked to a network
 * provider carrying the SNP, BNP or LSP role (network_providers.role_names).
 *
 * Deliberately avoids joining sub_domains to team_msme_schemes via
 * JSON_CONTAINS(product_category_id, ...): that predicate isn't indexable, so
 * evaluating it once per category (57 times) against the whole msme table was
 * the dominant cost (~3-4s). Onboarded MSEs are always a small set (bounded by
 * team_snpmsme_mapping, a few thousand rows at most), so instead this fetches
 * just those MSEs' category arrays via a single indexed join, decodes the JSON
 * once per row, and aggregates per category in PHP.
 */
class ProductCategoryMseLinkageReportAction
{
    use DataTable;

    protected array $columns = [
        1 => 'product_category',
        2 => 'onboarded_mse',
        3 => 'linked_snp',
        4 => 'linked_bnp',
        5 => 'linked_lsp',
    ];

    public function execute()
    {
        [$limit, $order, $dir, $search, $page, $filters] = $this->getDataTableParams();
        $filters ??= [];

        $rows = $this->buildReportRows($filters, $search);

        $order = in_array($order, $this->columns, true) ? $order : 'product_category';
        $rows = $rows->sortBy($order, SORT_REGULAR, strtolower($dir) === 'desc')->values();

        if ($page) {
            $currentPage = max((int) request()->input('page', 1), 1);
            $total = $rows->count();
            $items = $rows->forPage($currentPage, $limit)->values();
            $lastPage = max((int) ceil($total / max($limit, 1)), 1);

            return [
                'draw' => intval(request()->input('draw')),
                'recordsTotal' => $total,
                'recordsFiltered' => $total,
                'data' => $items->all(),
                'current_page' => $currentPage,
                'next' => $currentPage < $lastPage ? request()->fullUrlWithQuery(['page' => $currentPage + 1]) : null,
                'previous' => $currentPage > 1 ? request()->fullUrlWithQuery(['page' => $currentPage - 1]) : null,
                'per_page' => $limit,
            ];
        }

        return $rows;
    }

    public function exportRows()
    {
        [, , , , , $filters] = $this->getDataTableParams();

        return $this->buildReportRows($filters ?? [], null)->sortBy('product_category')->values();
    }

    /**
     * One row per active (status = 1) SNP mapping, pre-joined to its role
     * flags in a single pass over tsm/tss/np.
     *
     * Deliberately NOT deduped by msme_id: the "MSME Registration MIS Report"
     * (App\Web\MisReport\MisReportService::getMsmeMisList, msme_status =
     * onboarded) counts every active mapping row via a plain inner join, so an
     * MSME with two active SNP mappings counts as 2 there. Counting mapping
     * rows here too keeps this report's "Onboarded MSE" figure reconcilable
     * with that report for the same product category.
     */
    private function linkedMseSubquery()
    {
        // groupBy(tsm.id) collapses nothing (tsm.id is the mapping's own
        // primary key, so every group has exactly one row) - it's here only
        // to force MySQL to materialize this subquery instead of merging it
        // into whatever it's joined to. Without it, MySQL inlines the
        // tsm/tss/np join chain into the outer query and re-evaluates it per
        // outer row instead of once, which is what made this ~8s instead of
        // the sub-second join it should be.
        return DB::table('team_snpmsme_mapping as tsm')
            ->leftJoin('team_snp_scheme as tss', 'tss.id', '=', 'tsm.snp_id')
            ->leftJoin('network_providers as np', 'np.id', '=', 'tss.network_provider_id')
            ->where('tsm.status', 1)
            ->groupBy('tsm.id', 'tsm.msme_id')
            ->select('tsm.id as mapping_id', 'tsm.msme_id')
            ->selectRaw("
                (np.role_names LIKE '%SNP%') AS is_linked_snp,
                (np.role_names LIKE '%BNP%') AS is_linked_bnp,
                (np.role_names LIKE '%LSP%') AS is_linked_lsp
            ");
    }

    /**
     * Every active mapping joined to its parent msme's category array - a
     * single indexed join (ms.id = lm.msme_id), no JSON scanning involved.
     */
    private function onboardedMappingRows(array $filters): Collection
    {
        $fromDate = $filters['from_date'] ?? null;
        $toDate = $filters['to_date'] ?? null;

        $query = DB::table('team_msme_schemes as ms')
            ->joinSub($this->linkedMseSubquery(), 'lm', 'lm.msme_id', '=', 'ms.id')
            ->whereNotNull('ms.product_category_id')
            ->whereNotNull('ms.major_activity')
            ->where('ms.major_activity', '<>', '')
            ->select('ms.product_category_id', 'lm.is_linked_snp', 'lm.is_linked_bnp', 'lm.is_linked_lsp');

        if ($fromDate) {
            $query->where('ms.created_at', '>=', date('Y-m-d', strtotime($fromDate)));
        }

        if ($toDate) {
            $query->where('ms.created_at', '<', (new \DateTime($toDate))->modify('+1 day')->format('Y-m-d'));
        }

        return $query->get();
    }

    /**
     * Decodes each mapping row's category array once and tallies counts per
     * category id in plain PHP - this replaces the O(categories x all msmes)
     * JSON_CONTAINS join with an O(mapping rows x categories per msme) loop
     * over a dataset that's orders of magnitude smaller.
     */
    private function aggregateByCategory(Collection $mappingRows): array
    {
        $counts = [];

        foreach ($mappingRows as $row) {
            $categoryIds = json_decode((string) $row->product_category_id, true);

            if (!is_array($categoryIds)) {
                continue;
            }

            foreach (array_unique($categoryIds) as $categoryId) {
                $counts[$categoryId] ??= ['onboarded_mse' => 0, 'linked_snp' => 0, 'linked_bnp' => 0, 'linked_lsp' => 0];
                $counts[$categoryId]['onboarded_mse']++;
                $counts[$categoryId]['linked_snp'] += (int) $row->is_linked_snp;
                $counts[$categoryId]['linked_bnp'] += (int) $row->is_linked_bnp;
                $counts[$categoryId]['linked_lsp'] += (int) $row->is_linked_lsp;
            }
        }

        return $counts;
    }

    private function buildReportRows(array $filters, ?string $search): Collection
    {
        $counts = $this->aggregateByCategory($this->onboardedMappingRows($filters));

        $categories = DB::table('sub_domains as sd')->select('sd.id', 'sd.name');

        if (!empty($filters['product_category_id']) && is_array($filters['product_category_id'])) {
            $categories->whereIn('sd.id', $filters['product_category_id']);
        }

        if ($search) {
            $categories->where('sd.name', 'like', "%$search%");
        }

        return $categories->get()->map(function ($category) use ($counts) {
            $c = $counts[$category->id] ?? ['onboarded_mse' => 0, 'linked_snp' => 0, 'linked_bnp' => 0, 'linked_lsp' => 0];

            return (object) [
                'id' => $category->id,
                'product_category' => $category->name,
                'onboarded_mse' => $c['onboarded_mse'],
                'linked_snp' => $c['linked_snp'],
                'linked_bnp' => $c['linked_bnp'],
                'linked_lsp' => $c['linked_lsp'],
            ];
        });
    }
}
