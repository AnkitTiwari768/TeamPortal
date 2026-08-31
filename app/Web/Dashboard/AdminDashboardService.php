<?php

declare(strict_types=1);

namespace App\Web\Dashboard;

use App\Domain\Batch\BatchStatus;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class AdminDashboardService
{
    // use DashboardIaTrait;
    /*
    |--------------------------------------------------------------------------
    | Date Filter Helper
    |--------------------------------------------------------------------------
    | Returns a tuple [$dateFilter string, $dateBindings array] for use in
    | raw SQL queries based on the provided year / date range.
    */
    private function buildDateFilter(
        ?string $fromDate,
        ?string $toDate,
        ?string $year,
        string $column = 'ms.created_at',
        ?int $type = null
    ): array {
        $dateFilter = '';
        $dateBindings = [];

        if (!empty($fromDate) && !empty($toDate)) {
            $dateFilter = " AND {$column} BETWEEN ? AND ?";
            $dateBindings[] = Carbon::parse($fromDate)->startOfDay();
            $dateBindings[] = Carbon::parse($toDate)->endOfDay();
        } elseif (!empty($year)) {
            $dateFilter = " AND YEAR({$column}) = ?";
            $dateBindings[] = (int) $year;
        } elseif ($type != 1) {
            $dateFilter = " AND YEAR({$column}) = ?";
            $dateBindings[] = (int) date('Y');
        }

        return [$dateFilter, $dateBindings];
    }

    /*
    |--------------------------------------------------------------------------
    | Eloquent-style date scope helper (for query builder)
    |--------------------------------------------------------------------------
    */
    private function applyDateScope($query, ?string $fromDate, ?string $toDate, ?string $year, string $column, ?int $type = null): void
    {
        if (!empty($fromDate) && !empty($toDate)) {
            $query->whereBetween($column, [
                Carbon::parse($fromDate)->startOfDay(),
                Carbon::parse($toDate)->endOfDay(),
            ]);
        } elseif (!empty($year)) {
            $query->whereYear($column, (int) $year);
        } elseif ($type != 1) {
            $query->whereYear($column, (int) date('Y'));
        }
    }

    /*
    |--------------------------------------------------------------------------
    | 1. Total Registered MSE (Manufacturing / Services / Trading)
    |--------------------------------------------------------------------------
    */
    public function getTotalRegisteredMse(?string $year, ?string $fromDate, ?string $toDate, ?int $type = null): array
    {
        $defaultActivities = [
            'Manufacturing' => 0,
            'Services' => 0,
            'Trading' => 0,
        ];

        $query = DB::table('team_msme_schemes as ms')
            ->whereNotNull('ms.major_activity')
            ->where('ms.major_activity', '!=', '');

        $this->applyDateScope($query, $fromDate, $toDate, $year, 'ms.created_at', $type);

        $totalCount = (clone $query)->count();

        $majorActivityCounts = (clone $query)
            ->select('ms.major_activity', DB::raw('COUNT(*) as total_count'))
            ->groupBy('ms.major_activity')
            ->pluck('total_count', 'ms.major_activity')
            ->toArray();

        $normalized = [];
        foreach ($majorActivityCounts as $key => $value) {
            $normalized[ucfirst(strtolower(trim($key)))] = (int) $value;
        }

        return [
            'total_msme' => (int) $totalCount,
            'major_activities' => array_merge($defaultActivities, $normalized),
        ];
    }

    /*
    |--------------------------------------------------------------------------
    | 2. Open MSE (select_snp = 0 and bpp_id is null — not yet picked by SNP)
    |--------------------------------------------------------------------------
    */
    public function getOpenMse(?string $year, ?string $fromDate, ?string $toDate, ?int $type = null): array
    {
        $defaultActivities = [
            'Manufacturing' => 0,
            'Services' => 0,
            'Trading' => 0,
        ];

        $query = DB::table('team_msme_schemes as ms')
            ->where('ms.select_snp', 0)
            ->whereNull('ms.bpp_id')
            ->whereNotNull('ms.major_activity')
            ->where('ms.major_activity', '!=', '');

        $this->applyDateScope($query, $fromDate, $toDate, $year, 'ms.created_at', $type);

        $totalCount = (clone $query)->count();

        $majorActivityCounts = (clone $query)
            ->select('ms.major_activity', DB::raw('COUNT(*) as total_count'))
            ->groupBy('ms.major_activity')
            ->pluck('total_count', 'ms.major_activity')
            ->toArray();

        $normalized = [];
        foreach ($majorActivityCounts as $key => $value) {
            $normalized[ucfirst(strtolower(trim($key)))] = (int) $value;
        }

        return [
            'total_msme_open' => (int) $totalCount,
            'major_activities' => array_merge($defaultActivities, $normalized),
        ];
    }

    /*
    |--------------------------------------------------------------------------
    | 3. Direct Selection by MSE (select_snp = 1, tsm.status = 0)
    |--------------------------------------------------------------------------
    */
    public function getDirectSelectionMse(?string $year, ?string $fromDate, ?string $toDate, ?int $type = null): array
    {
        $defaultActivities = [
            'Manufacturing' => 0,
            'Services' => 0,
            'Trading' => 0,
        ];

        [$dateFilter, $dateBindings] = $this->buildDateFilter($fromDate, $toDate, $year, 'ms.created_at', $type);

        $result = DB::select("
            SELECT
                COUNT(*) AS total_chossen
            FROM team_msme_schemes ms
            INNER JOIN team_snpmsme_mapping tsm ON tsm.msme_id = ms.id
            WHERE ms.select_snp = 1
              AND tsm.status = 0
              AND ms.major_activity IS NOT NULL
              AND ms.major_activity != ''
              {$dateFilter}
        ", $dateBindings);

        $total = (int) ($result[0]->total_chossen ?? 0);

        $majorActivity = DB::select("
            SELECT
                ms.major_activity,
                COUNT(*) AS chossen_count
            FROM team_msme_schemes ms
            INNER JOIN team_snpmsme_mapping tsm ON tsm.msme_id = ms.id
            WHERE ms.select_snp = 1
              AND tsm.status = 0
              AND ms.major_activity IS NOT NULL
              AND ms.major_activity != ''
              {$dateFilter}
            GROUP BY ms.major_activity
        ", $dateBindings);

        $majorActivityCounts = collect($majorActivity)->mapWithKeys(function ($item) {
            return [ucfirst(strtolower(trim($item->major_activity))) => (int) $item->chossen_count];
        })->toArray();

        return [
            'total_chossen' => $total,
            'major_activities' => array_merge($defaultActivities, $majorActivityCounts),
        ];
    }

    /*
    |--------------------------------------------------------------------------
    | 4. Onboarded MSE (tsm.status = 1)
    |--------------------------------------------------------------------------
    */
    public function getOnboardedMse(?string $year, ?string $fromDate, ?string $toDate, ?int $type = null): array
    {
        $defaultActivities = [
            'Manufacturing' => 0,
            'Services' => 0,
            'Trading' => 0,
        ];

        [$dateFilter, $dateBindings] = $this->buildDateFilter($fromDate, $toDate, $year, 'ms.created_at', $type);

        $result = DB::select("
            SELECT COUNT(*) AS total_onboarded
            FROM team_msme_schemes ms
            INNER JOIN team_snpmsme_mapping tsm ON tsm.msme_id = ms.id
            WHERE tsm.status = 1
              AND ms.major_activity IS NOT NULL
              AND ms.major_activity != ''
              {$dateFilter}
        ", $dateBindings);

        $total = (int) ($result[0]->total_onboarded ?? 0);

        $majorActivity = DB::select("
            SELECT
                ms.major_activity,
                COUNT(*) AS onboarded_count
            FROM team_msme_schemes ms
            INNER JOIN team_snpmsme_mapping tsm ON tsm.msme_id = ms.id
            WHERE tsm.status = 1
              AND ms.major_activity IS NOT NULL
              AND ms.major_activity != ''
              {$dateFilter}
            GROUP BY ms.major_activity
        ", $dateBindings);

        $majorActivityCounts = collect($majorActivity)->mapWithKeys(function ($item) {
            return [ucfirst(strtolower(trim($item->major_activity))) => (int) $item->onboarded_count];
        })->toArray();

        return [
            'total_onboarded' => $total,
            'major_activities' => array_merge($defaultActivities, $majorActivityCounts),
        ];
    }

    /*
    |--------------------------------------------------------------------------
    | 5. Women Owned MSEs (gender = 'Female')
    |--------------------------------------------------------------------------
    */
    public function getWomenOwnedMse(?string $year, ?string $fromDate, ?string $toDate, ?int $type = null): array
    {
        $defaultActivities = [
            'Manufacturing' => 0,
            'Services' => 0,
            'Trading' => 0,
        ];

        $query = DB::table('team_msme_schemes as ms')
            ->whereRaw("LOWER(TRIM(ms.gender)) = 'female'")
            ->whereNotNull('ms.major_activity')
            ->where('ms.major_activity', '!=', '');

        $this->applyDateScope($query, $fromDate, $toDate, $year, 'ms.created_at', $type);

        $total = (clone $query)->count();

        $majorActivityCounts = (clone $query)
            ->select('ms.major_activity', DB::raw('COUNT(*) as total_count'))
            ->groupBy('ms.major_activity')
            ->pluck('total_count', 'ms.major_activity')
            ->toArray();

        $normalized = [];
        foreach ($majorActivityCounts as $key => $value) {
            $normalized[ucfirst(strtolower(trim($key)))] = (int) $value;
        }

        return [
            'total_women' => (int) $total,
            'major_activities' => array_merge($defaultActivities, $normalized),
        ];
    }

    /*
    |--------------------------------------------------------------------------
    | 6. MSE Registration via Bulk Upload (SNP / IA)  [DUMMY — TBD by BA]
    |--------------------------------------------------------------------------
    */
    public function getMseBulkUpload(?string $year, ?string $fromDate, ?string $toDate, ?int $type = null): array
    {
        $query = DB::table('team_msme_scheme_drafts')
            ->where('status', 'Migrated');

        // Apply date filters
        $this->applyDateScope($query, $fromDate, $toDate, $year, 'created_at', $type);

        // Get total count of migrated records
        $total = (clone $query)->count();

        // Get count of SNP (role_type = 1)
        $snpQuery = clone $query;
        $snp = (clone $snpQuery)->where('role_type', 1)->count();

        // Get count of IA (role_type = 2)
        $iaQuery = clone $query;
        $ia = (clone $iaQuery)->where('role_type', 2)->count();

        return [
            'total' => (int) $total,
            'snp' => (int) $snp,
            'ia' => (int) $ia,
        ];
    }
    /*
    |--------------------------------------------------------------------------
    | 7. MSE Registered via Two Step (SNP Assistance / Helpdesk / Self) [DUMMY]
    |--------------------------------------------------------------------------
    */
    public function getMseTwoStep(?string $year, ?string $fromDate, ?string $toDate, ?int $type = null): array
    {
        // Base query for all MSE registrations with is_msme_registration = 1 (new registration)
        $baseQuery = DB::table('team_msme_schemes as ms')
            ->where('ms.status', 0)
            ->where('ms.is_msme_registration', 1);

        // Apply date filters
        $this->applyDateFilters($baseQuery, $year, $fromDate, $toDate, $type);

        // Count by select_snp values:
        // select_snp = 2 → Self Registration
        // select_snp = 3 → Helpdesk Support  
        // select_snp = 1 → SNP Assistance (To Be Validated)

        $selfCount = (clone $baseQuery)->where('ms.select_snp', 2)->count();
        $helpdeskCount = (clone $baseQuery)->where('ms.select_snp', 3)->count();
        $snpAssistCount = (clone $baseQuery)->where('ms.select_snp', 1)->count();

        // Total = sum of all three types
        $total = $selfCount + $helpdeskCount + $snpAssistCount;

        return [
            'total' => $total,
            'snp_assist' => $snpAssistCount,
            'helpdesk' => $helpdeskCount,
            'self' => $selfCount,
        ];
    }

    /*
    |--------------------------------------------------------------------------
    | 8. SNP Registration (pending / verified / rejected / reverted)
    |    team_snp_scheme   status: 1=pending, 2=verified, 3=rejected, 4=reverted
    |--------------------------------------------------------------------------
    */
    public function getSnpRegistrationCount(?string $year, ?string $fromDate, ?string $toDate, ?int $type = null): array
    {
        [$dateFilter, $dateBindings] = $this->buildDateFilter($fromDate, $toDate, $year, 'snp.created_at', $type);

        $result = DB::select("
            SELECT
                COUNT(DISTINCT snp.id) AS total,
                SUM(CASE WHEN snp.status = 1 THEN 1 ELSE 0 END) AS pending,
                SUM(CASE WHEN snp.status = 2 THEN 1 ELSE 0 END) AS verified,
                SUM(CASE WHEN snp.status = 3 THEN 1 ELSE 0 END) AS rejected,
                SUM(CASE WHEN snp.status = 4 THEN 1 ELSE 0 END) AS reverted
            FROM team_snp_scheme snp
            WHERE 1=1
            {$dateFilter}
        ", $dateBindings);
        // dd($result);

        return isset($result[0]) ? (array) $result[0] : [
            'total' => 0,
            'pending' => 0,
            'verified' => 0,
            'rejected' => 0,
            'reverted' => 0,
        ];
    }

    /*
    |--------------------------------------------------------------------------
    | 9. BNP Registration (pending / verified / rejected / reverted)
    |    network_providers  status: 1=pending, 2=verified, 3=rejected, 4=reverted
    |--------------------------------------------------------------------------
    */
    public function getBnpRegistrationCount(?string $year, ?string $fromDate, ?string $toDate, ?int $type = null): array
    {
        $roleId = DB::table('roles')->where('slug', 'bnp')->value('id');

        [$dateFilter, $dateBindings] = $this->buildDateFilter($fromDate, $toDate, $year, 'np.created_at', $type);

        $result = DB::select("
            SELECT
                COUNT(DISTINCT np.id) AS total,
                SUM(CASE WHEN np.status = 1 THEN 1 ELSE 0 END) AS pending,
                SUM(CASE WHEN np.status = 2 THEN 1 ELSE 0 END) AS verified,
                SUM(CASE WHEN np.status = 3 THEN 1 ELSE 0 END) AS rejected,
                SUM(CASE WHEN np.status = 4 THEN 1 ELSE 0 END) AS reverted
            FROM network_providers np
            WHERE JSON_CONTAINS(np.roles, JSON_QUOTE(?))
            {$dateFilter}
        ", array_merge([$roleId], $dateBindings));

        return isset($result[0]) ? (array) $result[0] : [
            'total' => 0,
            'pending' => 0,
            'verified' => 0,
            'rejected' => 0,
            'reverted' => 0,
        ];
    }

    /*
    |--------------------------------------------------------------------------
    | 10. LSP Registration (pending / verified / rejected / reverted)
    |--------------------------------------------------------------------------
    */
    public function getLspRegistrationCount(?string $year, ?string $fromDate, ?string $toDate, ?int $type = null): array
    {
        $roleId = DB::table('roles')->where('slug', 'lsp')->value('id');

        [$dateFilter, $dateBindings] = $this->buildDateFilter($fromDate, $toDate, $year, 'np.created_at', $type);

        $result = DB::select("
            SELECT
                COUNT(DISTINCT np.id) AS total,
                SUM(CASE WHEN np.status = 1 THEN 1 ELSE 0 END) AS pending,
                SUM(CASE WHEN np.status = 2 THEN 1 ELSE 0 END) AS verified,
                SUM(CASE WHEN np.status = 3 THEN 1 ELSE 0 END) AS rejected,
                SUM(CASE WHEN np.status = 4 THEN 1 ELSE 0 END) AS reverted
            FROM network_providers np
            WHERE JSON_CONTAINS(np.roles, JSON_QUOTE(?))
            {$dateFilter}
        ", array_merge([$roleId], $dateBindings));

        return isset($result[0]) ? (array) $result[0] : [
            'total' => 0,
            'pending' => 0,
            'verified' => 0,
            'rejected' => 0,
            'reverted' => 0,
        ];
    }
    private function applyDateFilters($query, ?string $year, ?string $fromDate, ?string $toDate, ?int $type, string $dateColumn = 'created_at'): void
    {
        if ($fromDate && $toDate) {
            // Date range filter
            $startDate = Carbon::parse($fromDate)->startOfDay();
            $endDate = Carbon::parse($toDate)->endOfDay();
            $query->whereBetween($dateColumn, [$startDate, $endDate]);
        } elseif ($year) {
            // Year filter
            $query->whereYear($dateColumn, (int) $year);
        } elseif ($type == 1) {
            // No date filter - show all data (do nothing)
            return;
        } else {
            // Default to current year
            $query->whereYear($dateColumn, now()->year);
        }
    }
    /*
    |--------------------------------------------------------------------------
    | 11. Associations Registration (pending / verified / rejected)
    |     [DUMMY — TBD by BA: which role slug / table to use]
    |--------------------------------------------------------------------------
    */
    public function getAssociationsCount(?string $year, ?string $fromDate, ?string $toDate, ?int $type = null): array
    {

        $result = [
            'total' => $this->getTotalRegisteredIa($year, $fromDate, $toDate, $type),
            'pending' => $this->getTotalPendingIa($year, $fromDate, $toDate, $type),
            'verified' => $this->getTotalVerifiedIa($year, $fromDate, $toDate, $type),
            'rejected' => $this->getTotalRejectedIa($year, $fromDate, $toDate, $type),
        ];

        \Log::info('Associations Count Result:', $result);

        return $result;
    }

    /*
    |--------------------------------------------------------------------------
    | 12. Claims Summary (all claims — no user filter for Admin)
    |--------------------------------------------------------------------------
    */
    public function getClaimsSummary(?string $year, ?string $fromDate, ?string $toDate, ?int $type = null): array
    {
        $claimTypes = DB::table('claim_types')->select('id', 'name', 'slug')->get();
        $action = new \App\Web\Dashboard\GetClaimSummaryAction();

        $claimTypeRows = [];
        $y = $year ? (int) $year : null;

        foreach ($claimTypes as $ct) {
            $summary = $action->execute($ct->slug, $y, $fromDate, $toDate, null, $type, false);

            $claimTypeRows[] = [
                'claimType' => $summary['claimType'],
                'claimTypeName' => $summary['claimTypeName'],
                'totalClaim' => $summary['totalClaim'],
                'totalDraft' => $summary['totalDraft'],
                'pendingCount' => $summary['pendingCount'],
                'approvedCount' => $summary['approvedCount'],
                'rejectedCount' => $summary['rejectedCount'],
                'paymentCompletedCount' => $summary['paymentCompletedCount'],
                'totalAmount' => $summary['totalAmount'],
            ];
        }

        $overallSummary = $action->execute(null, $y, $fromDate, $toDate, null, $type, false);

        return [
            'per_type' => $claimTypeRows,
            'all' => [
                'totalClaim' => $overallSummary['totalClaim'],
                'totalDraft' => $overallSummary['totalDraft'],
                'pendingCount' => $overallSummary['pendingCount'],
                'approvedCount' => $overallSummary['approvedCount'],
                'rejectedCount' => $overallSummary['rejectedCount'],
                'paymentCompletedCount' => $overallSummary['paymentCompletedCount'],
                'totalAmount' => $overallSummary['totalAmount'],
            ],
        ];
    }

    /*
    |--------------------------------------------------------------------------
    | 13. Entity-Wise Claims (SNP / BNP / LSP)
    |     Joins claims → user_roles → roles on slug
    |--------------------------------------------------------------------------
    */
    public function getEntityWiseClaimsSummary(?string $year, ?string $fromDate, ?string $toDate, ?int $type = null): array
    {
        $entities = ['snp', 'bnp', 'lsp'];

        $roleClaimTypes = [
            'snp' => [
                '0378d757-7b90-4105-beb4-5a8af2d06eb4', // Claim for Catalogue Creation
                '7c571904-62bc-4e56-ac6d-a62d13818585', // Claim for Accounts Management
                '482d8eb2-b316-11f0-922a-00155d022d06', // Claim for Packaging
            ],
            'bnp' => [
                'd8bb8e45-9a96-11f0-922a-00155d022d06', // Claim for Demand Generation
            ],
            'lsp' => [
                'f47a6dae-67f3-4331-899c-e83c19aff14a', // Claim for Logistics and Transportation
            ]
        ];

        $action = new \App\Web\Dashboard\GetClaimSummaryAction();
        $result = [];
        $y = $year ? (int) $year : null;

        foreach ($entities as $slug) {
            $claimTypeIds = $roleClaimTypes[$slug] ?? [];

            $entityTotal = 0;
            $entityDraft = 0;
            $entityPending = 0;
            $entityApproved = 0;
            $entityRejected = 0;
            $entityPayment = 0;
            $entityAmount = 0;

            foreach ($claimTypeIds as $cid) {
                // We need the claim type slug for GetClaimSummaryAction
                $ct = DB::table('claim_types')->where('id', $cid)->first();
                if ($ct) {
                    $summary = $action->execute($ct->slug, $y, $fromDate, $toDate, null, $type, false);
                    $entityTotal += $summary['totalClaim'];
                    $entityDraft += $summary['totalDraft'];
                    $entityPending += $summary['pendingCount'];
                    $entityApproved += $summary['approvedCount'];
                    $entityRejected += $summary['rejectedCount'];
                    $entityPayment += $summary['paymentCompletedCount'];
                    $entityAmount += $summary['totalAmount'];
                }
            }

            $result[$slug] = [
                'totalClaim' => $entityTotal,
                'totalDraft' => $entityDraft,
                'pendingCount' => $entityPending,
                'approvedCount' => $entityApproved,
                'rejectedCount' => $entityRejected,
                'paymentCompletedCount' => $entityPayment,
                'totalAmount' => $entityAmount,
            ];
        }

        return $result;
    }

    public function getTotalRegisteredIa($year, $fromDate, $toDate, $type)
    {
        $query = DB::table('industrial_associations');

        // Apply date range filter if both dates are provided
        if (!empty($fromDate) && !empty($toDate)) {
            $query->whereBetween('created_at', [
                Carbon::parse($fromDate)->startOfDay(),
                Carbon::parse($toDate)->endOfDay(),
            ]);
        }
        // Apply year filter if year is provided and type is NOT 1 (meaning we're not in "all" mode)
        elseif (!empty($year) && $type != 1) {
            $query->whereYear('created_at', $year);
        }
        // If no filters, return all records (no WHERE clause added)

        return $query->count();
    }

    public function getTotalPendingIa($year, $fromDate, $toDate, $type)
    {
        $query = DB::table('industrial_associations')->where('status', 1);

        if (!empty($fromDate) && !empty($toDate)) {
            $query->whereBetween('created_at', [
                Carbon::parse($fromDate)->startOfDay(),
                Carbon::parse($toDate)->endOfDay(),
            ]);
        } elseif (!empty($year) && $type != 1) {
            $query->whereYear('created_at', $year);
        }

        return $query->count();
    }

    public function getTotalVerifiedIa($year, $fromDate, $toDate, $type)
    {
        $query = DB::table('industrial_associations')->where('status', 2);

        if (!empty($fromDate) && !empty($toDate)) {
            $query->whereBetween('created_at', [
                Carbon::parse($fromDate)->startOfDay(),
                Carbon::parse($toDate)->endOfDay(),
            ]);
        } elseif (!empty($year) && $type != 1) {
            $query->whereYear('created_at', $year);
        }

        return $query->count();
    }

    public function getTotalRejectedIa($year, $fromDate, $toDate, $type)
    {
        $query = DB::table('industrial_associations')->where('status', 3);

        if (!empty($fromDate) && !empty($toDate)) {
            $query->whereBetween('created_at', [
                Carbon::parse($fromDate)->startOfDay(),
                Carbon::parse($toDate)->endOfDay(),
            ]);
        } elseif (!empty($year) && $type != 1) {
            $query->whereYear('created_at', $year);
        }

        return $query->count();
    }
}
