<?php

declare(strict_types=1);

namespace App\Domain\MIS;

use App\Domain\NetworkProvider\NetworkProviderStatus;
use Illuminate\Support\Facades\DB;

class ProductCategoryMISReportAction
{
    public function execute(?string $tier = null)
    {
        $snpId = request()->input('snp_id') ?: null;

        return [
            'rows' => $this->buildCategoryRows($snpId),
            'summary' => $this->buildSummaryCounts($snpId),
            'snp_options' => $this->getApprovedSnpOptions(),
        ];
    }

    /**
     * Per-category breakdown. The optional SNP filter is applied inside each
     * COUNT(...CASE...) rather than as a query-level WHERE, so every product
     * category still gets a row (with counts of 0) even when none of its
     * MSMEs are mapped to the selected SNP - a WHERE on tsm.snp_id would
     * instead drop those categories from the result entirely.
     */
    private function buildCategoryRows(?string $snpId)
    {
        $snpCondition = $snpId ? 'AND tsm.snp_id = ?' : '';
        $bindings = $snpId ? array_fill(0, 4, $snpId) : [];

        return DB::table('sub_domains as sd')

            ->leftJoin('team_msme_schemes as ms', function ($join) {
                $join->whereRaw("
                    JSON_CONTAINS(
                        ms.product_category_id,
                        JSON_QUOTE(CAST(sd.id AS CHAR))
                    )
                ");
            })

            ->leftJoin('team_snpmsme_mapping as tsm', 'tsm.msme_id', '=', 'ms.id')

            ->selectRaw("
                sd.id,
                sd.name AS product_category,

                COUNT(DISTINCT CASE
                    WHEN ms.major_activity IS NOT NULL
                     AND ms.major_activity <> ''
                     $snpCondition
                    THEN ms.id
                END) AS total_msme,

                COUNT(DISTINCT CASE
                    WHEN ms.select_snp = 0
                     AND ms.bpp_id IS NULL
                     AND ms.major_activity IS NOT NULL
                     AND ms.major_activity <> ''
                     $snpCondition
                    THEN ms.id
                END) AS open_msme,

                COUNT(DISTINCT CASE
                    WHEN ms.select_snp = 1
                     AND tsm.status = 0
                     AND ms.major_activity IS NOT NULL
                     AND ms.major_activity <> ''
                     $snpCondition
                    THEN ms.id
                END) AS direct_selection_msme,

                COUNT(DISTINCT CASE
                    WHEN tsm.status = 1
                     AND ms.major_activity IS NOT NULL
                     AND ms.major_activity <> ''
                     $snpCondition
                    THEN ms.id
                END) AS onboarded_msme
            ", $bindings)

            ->groupBy('sd.id', 'sd.name')

            ->orderBy('sd.name', 'ASC')

            ->get();
    }

    /**
     * Global (not per-category) counts for the dashboard cards. Computed
     * directly off team_msme_schemes rather than by summing the per-category
     * rows above, since an MSME can carry multiple product categories and
     * summing per-category rows would double count it.
     */
    private function buildSummaryCounts(?string $snpId): array
    {
        $snpCondition = $snpId ? 'AND tsm.snp_id = ?' : '';
        $bindings = $snpId ? array_fill(0, 3, $snpId) : [];

        $summary = DB::table('team_msme_schemes as ms')
            ->leftJoin('team_snpmsme_mapping as tsm', 'tsm.msme_id', '=', 'ms.id')
            ->whereNotNull('ms.major_activity')
            ->where('ms.major_activity', '!=', '')
            ->selectRaw("
                COUNT(DISTINCT CASE
                    WHEN ms.select_snp = 0
                     AND ms.bpp_id IS NULL
                     $snpCondition
                    THEN ms.id
                END) AS open_msme,

                COUNT(DISTINCT CASE
                    WHEN ms.select_snp = 1
                     AND tsm.status = 0
                     $snpCondition
                    THEN ms.id
                END) AS direct_selection_msme,

                COUNT(DISTINCT CASE
                    WHEN tsm.status = 1
                     $snpCondition
                    THEN ms.id
                END) AS onboarded_msme
            ", $bindings)
            ->first();

        return [
            'open_msme' => (int) ($summary->open_msme ?? 0),
            'direct_selection_msme' => (int) ($summary->direct_selection_msme ?? 0),
            'onboarded_msme' => (int) ($summary->onboarded_msme ?? 0),
        ];
    }

    /**
     * SNP Approved dropdown options - approved SNPs (team_snp_scheme.status
     * = NetworkProviderStatus::APPROVE), matched by id against
     * team_snpmsme_mapping.snp_id.
     */
    private function getApprovedSnpOptions()
    {
        return DB::table('team_snp_scheme')
            ->whereNotNull('snp_id')
            ->where('status', NetworkProviderStatus::APPROVE->value)
            ->orderBy('organization_name', 'ASC')
            ->get(['id', 'snp_id', 'organization_name'])
            ->map(fn ($snp) => [
                'id' => $snp->id,
                'label' => $snp->organization_name ?: $snp->snp_id,
            ])
            ->values();
    }
}