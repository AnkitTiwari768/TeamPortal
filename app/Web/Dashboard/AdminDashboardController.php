<?php

declare(strict_types=1);

namespace App\Web\Dashboard;

use App\Http\Controllers\ClientController;
use Illuminate\Http\Request;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

final class AdminDashboardController extends ClientController
{
    use DashboardTrait;

    public function __construct(private AdminDashboardService $service) {}

    /*
    |--------------------------------------------------------------------------
    | Main dashboard page for Administrator role
    |--------------------------------------------------------------------------
    */
    public function index(Request $request)
    {
        [$selectedYear, $fromDate, $toDate, $type] = $this->resolveFilters($request);

        $data = $this->buildMseData($selectedYear, $fromDate, $toDate, $type);

        if ($request->ajax()) {
            return response()->json($data);
        }

        return view('dashboard.administrator', array_merge($data, [
            'selectedYear' => $selectedYear,
        ]))->with('title', __('message.dashboard_list'));
    }

    /*
    |--------------------------------------------------------------------------
    | AJAX — refresh MSE cards on filter change
    |--------------------------------------------------------------------------
    */
    public function getMseSummary(Request $request)
    {
        [$selectedYear, $fromDate, $toDate, $type] = $this->resolveFilters($request);
        return response()->json($this->buildMseData($selectedYear, $fromDate, $toDate, $type));
    }

    /*
    |--------------------------------------------------------------------------
    | AJAX — refresh Claim tab on filter change
    |--------------------------------------------------------------------------
    */
    public function getClaimSummary(Request $request)
    {
        [$selectedYear, $fromDate, $toDate, $type] = $this->resolveFilters($request);

        $claims = $this->service->getClaimsSummary($selectedYear, $fromDate, $toDate, $type);
        $entityWise = $this->service->getEntityWiseClaimsSummary($selectedYear, $fromDate, $toDate, $type);

        return response()->json([
            'success' => true,
            'data' => $claims['per_type'],
            'all' => $claims['all'],
            'entity_wise' => $entityWise,
        ]);
    }

    /*
    |--------------------------------------------------------------------------
    | AJAX — target specifically for Admin dashboard pie chart
    |--------------------------------------------------------------------------
    */
     public function getMsmeCategoryCountOnboarded(Request $request)
    {
        $year = $request->filled('year') ? (int) $request->year : null;
        $type = $request->filled('type') ? (int) $request->type : null;

        $baseQuery = \Illuminate\Support\Facades\DB::table('team_msme_schemes as tms')
            ->where('tms.select_snp', 0)
            ->whereNull('tms.bpp_id');

        // Add major_activity filters to match other dashboard counts
        $baseQuery->whereNotNull('tms.major_activity')
            ->where('tms.major_activity', '!=', '');

        if ($request->filled('from_date_new') && $request->filled('to_date_new')) {
            $baseQuery->whereBetween('tms.created_at', [
                \Carbon\Carbon::parse($request->from_date_new)->startOfDay(),
                \Carbon\Carbon::parse($request->to_date_new)->endOfDay(),
            ]);
        } elseif ($year) {
            $baseQuery->whereYear('tms.created_at', $year);
        } elseif ($type == 1) {
            // No date filter - show all data
        } else {
            $baseQuery->whereYear('tms.created_at', now()->year);
        }

        $totalCount = (clone $baseQuery)->count();

        if ($totalCount === 0) {
            return response()->json(['data' => [], 'total_count' => 0]);
        }

        // OPTIMIZATION: Fetch category logic in PHP to avoid slow MySQL JSON_CONTAINS join
        // Keyed by tms.id so an MSME with multiple mapping rows only contributes
        // its categories once, instead of once per duplicate row.
        $categoriesRaw = (clone $baseQuery)
            ->whereNotNull('tms.product_category_id')
            ->pluck('tms.product_category_id', 'tms.id');

        $categoryCounts = [];
        foreach ($categoriesRaw as $jsonStr) {
            if (!$jsonStr)
                continue;

            $ids = json_decode((string) $jsonStr, true);
            if (is_array($ids)) {
                foreach ($ids as $id) {
                    $categoryCounts[$id] = ($categoryCounts[$id] ?? 0) + 1;
                }
            } else {
                // In case it's stored as plain string but intended as single ID
                $categoryCounts[$jsonStr] = ($categoryCounts[$jsonStr] ?? 0) + 1;
            }
        }

        if (empty($categoryCounts)) {
            return response()->json(['data' => [], 'total_count' => $totalCount]);
        }

        // Ensure we only count IDs that exist in the sub_domains table (mimics INNER JOIN)
        $subDomains = \Illuminate\Support\Facades\DB::table('sub_domains')
            ->whereIn('id', array_keys($categoryCounts))
            ->pluck('name', 'id');

        $validCategoryCounts = [];
        foreach ($categoryCounts as $id => $count) {
            if (isset($subDomains[$id])) {
                $validCategoryCounts[$id] = $count;
            }
        }

        arsort($validCategoryCounts);
        $top10 = array_slice($validCategoryCounts, 0, 10, true);

        $subDomainStats = [];
        foreach ($top10 as $id => $count) {
            $subDomainStats[] = [
                'name' => $subDomains[$id],
                'y' => round(($count / $totalCount) * 100, 2),
                'count' => $count,
            ];
        }

        return response()->json(['data' => $subDomainStats, 'total_count' => $totalCount]);
    }
//     public function getMsmeCategoryCountOnboarded_old(Request $request)
//     {
//         $year = $request->filled('year') ? (int) $request->year : null;
//         $type = $request->filled('type') ? (int) $request->type : null;

//         $baseQuery = \Illuminate\Support\Facades\DB::table('team_msme_schemes as tms')
//             ->join('team_snpmsme_mapping as tsm', 'tsm.msme_id', '=', 'tms.id')
//             ->join('team_snp_scheme as tss', 'tss.id', '=', 'tsm.snp_id')
//             ->where('tsm.status', 1);

//         // Add major_activity filters to match other dashboard counts
//         $baseQuery->whereNotNull('tms.major_activity')
//             ->where('tms.major_activity', '!=', '');

//         if ($request->filled('from_date_new') && $request->filled('to_date_new')) {
//             $baseQuery->whereBetween('tsm.created_at', [
//                 \Carbon\Carbon::parse($request->from_date_new)->startOfDay(),
//                 \Carbon\Carbon::parse($request->to_date_new)->endOfDay(),
//             ]);
//         } elseif ($year) {
//             $baseQuery->whereYear('tsm.created_at', $year);
//         } elseif ($type == 1) {
//             // No date filter - show all data
//         } else {
//             $baseQuery->whereYear('tsm.created_at', now()->year);
//         }

//         $totalCount = (clone $baseQuery)->count();

//         if ($totalCount === 0) {
//             return response()->json(['data' => []]);
//         }

//         // OPTIMIZATION: Fetch category logic in PHP to avoid slow MySQL JSON_CONTAINS join
//         $categoriesRaw = (clone $baseQuery)
//             ->whereNotNull('tms.product_category_id')
//             ->pluck('tms.product_category_id');

//         $categoryCounts = [];
//         foreach ($categoriesRaw as $jsonStr) {
//             if (!$jsonStr)
//                 continue;

//             $ids = json_decode((string) $jsonStr, true);
//             if (is_array($ids)) {
//                 foreach ($ids as $id) {
//                     $categoryCounts[$id] = ($categoryCounts[$id] ?? 0) + 1;
//                 }
//             } else {
//                 // In case it's stored as plain string but intended as single ID
//                 $categoryCounts[$jsonStr] = ($categoryCounts[$jsonStr] ?? 0) + 1;
//             }
//         }

//         if (empty($categoryCounts)) {
//             return response()->json(['data' => []]);
//         }

//         // Ensure we only count IDs that exist in the sub_domains table (mimics INNER JOIN)
//         $subDomains = \Illuminate\Support\Facades\DB::table('sub_domains')
//             ->whereIn('id', array_keys($categoryCounts))
//             ->pluck('name', 'id');

//         $validCategoryCounts = [];
//         foreach ($categoryCounts as $id => $count) {
//             if (isset($subDomains[$id])) {
//                 $validCategoryCounts[$id] = $count;
//             }
//         }

//         arsort($validCategoryCounts);
//         $top10 = array_slice($validCategoryCounts, 0, 10, true);

//         $subDomainStats = [];
//         foreach ($top10 as $id => $count) {
//             $subDomainStats[] = [
//                 'name' => $subDomains[$id],
//                 'y' => round(($count / $totalCount) * 100, 2),
//                 'count' => $count,
//             ];
//         }
// dd($subDomainStats);
//         return response()->json(['data' => $subDomainStats]);
//     }
    

    public function getTopPerformerNps(Request $request)
    {
        $year = $request->filled('year') ? (int) $request->year : null;
        $type = $request->filled('type') ? (int) $request->type : null;

        $baseQuery = \Illuminate\Support\Facades\DB::table('claims as c')
            ->select(
                'c.created_by',
                \Illuminate\Support\Facades\DB::raw('COALESCE(ms.enterprise_name, np.organization_name) as entity_name')
            )
            ->selectRaw('(SUM(CASE WHEN c.claim_status NOT IN (?, ?, ?, ?, ?, ?) THEN 1 ELSE 0 END) + SUM(CASE WHEN c.claim_status IN (?,?) AND c.temporary_state = 1 THEN 1 ELSE 0 END )) as total_claims', [
                \App\Domain\Claim\ClaimStatus::DRAFT->value,
                \App\Domain\Batch\BatchStatus::APPROVED->value,
                \App\Domain\Batch\BatchStatus::PAYMENT_COMPLETED->value,
                \App\Domain\Batch\BatchStatus::REJECTED_BY_ONDC->value,
                \App\Domain\Batch\BatchStatus::REJECTED_BY_NSIC->value,
                \App\Domain\Batch\BatchStatus::REJECTED_NSIC_FINANCE->value,
                \App\Domain\Batch\BatchStatus::REJECTED_BY_ONDC->value,
                \App\Domain\Batch\BatchStatus::REJECTED_BY_NSIC->value,
            ])
            ->leftJoin('team_msme_schemes as ms', 'ms.user_id', '=', 'c.created_by')
            ->leftJoin('network_providers as np', 'np.user_id', '=', 'c.created_by')
            ->groupBy('c.created_by', 'ms.enterprise_name', 'np.organization_name')
            ->havingRaw('COALESCE(ms.enterprise_name, np.organization_name) IS NOT NULL')
            ->havingRaw('COALESCE(ms.enterprise_name, np.organization_name) != ""')
            ->havingRaw('total_claims > 0');

        if ($request->filled('from_date_new') && $request->filled('to_date_new')) {
            $baseQuery->whereBetween('c.created_at', [
                \Carbon\Carbon::parse($request->from_date_new)->startOfDay(),
                \Carbon\Carbon::parse($request->to_date_new)->endOfDay(),
            ]);
        } elseif ($year) {
            $baseQuery->whereYear('c.created_at', $year);
        } elseif ($type != 1) {
            $baseQuery->whereYear('c.created_at', now()->year);
        }

        $result = $baseQuery
            ->orderByDesc('total_claims')
            ->limit(5)
            ->get();

        $chartData = $result->map(function ($item) {
            return [
                'name' => $item->entity_name,
                'y' => (int) $item->total_claims,
            ];
        });

        return response()->json(['data' => $chartData]);
    }

    public function getMsmeGenderPercantege(Request $request)
    {
        $year = $request->filled('year') ? (int) $request->year : null;
        $type = $request->filled('type') ? (int) $request->type : null;

        $baseQuery = \Illuminate\Support\Facades\DB::table('team_msme_schemes as ms')
            ->join('team_snpmsme_mapping as tsm', 'tsm.msme_id', '=', 'ms.id')
            ->join('team_snp_scheme as tss', 'tsm.snp_id', '=', 'tss.id')
            ->where('tsm.status', 1);

        if ($request->filled('from_date_new') && $request->filled('to_date_new')) {
            $baseQuery->whereBetween('tsm.created_at', [
                \Carbon\Carbon::parse($request->from_date_new)->startOfDay(),
                \Carbon\Carbon::parse($request->to_date_new)->endOfDay(),
            ]);
        } elseif ($year) {
            $baseQuery->whereYear('tsm.created_at', $year);
        } elseif ($type == 1) {
            // No date filter - show all data
        } else {
            $baseQuery->whereYear('tsm.created_at', now()->year);
        }

        $results = (clone $baseQuery)
            ->selectRaw('COALESCE(NULLIF(ms.gender, ""), "Not Specified") as gender, COUNT(*) as count')
            ->groupByRaw('COALESCE(NULLIF(ms.gender, ""), "Not Specified")')
            ->get();

        $totalCount = $results->sum('count');

        $data = [];
        foreach ($results as $result) {
            $data[] = [
                'name' => ucfirst($result->gender),
                'count' => $result->count,
                'percentage' => $totalCount > 0 ? round(($result->count / $totalCount) * 100, 2) : 0,
                'y' => $totalCount > 0 ? round(($result->count / $totalCount) * 100, 2) : 0,
            ];
        }

        return response()->json([
            'data' => $data,
            'total' => $totalCount
        ]);
    }

    /**
     * Get state-wise MSME count for bar graph (Admin only)
     * 
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function getMsmeStateWiseCount(Request $request)
    {
        $year = $request->filled('year') ? (int) $request->year : null;
        $type = $request->filled('type') ? (int) $request->type : null;
        $fromDate = $request->filled('from_date_new') ? $request->from_date_new : null;
        $toDate = $request->filled('to_date_new') ? $request->to_date_new : null;

        // Base query without SNP filter (admin can see all data)
        $baseQuery = \Illuminate\Support\Facades\DB::table('team_msme_schemes as tms')
            ->join('team_snpmsme_mapping as tsm', 'tsm.msme_id', '=', 'tms.id')
            ->leftJoin('states as st', 'st.id', '=', 'tms.state_id')
            ->where('tsm.status', 1);

        // Apply date filters - FIXED LOGIC
        if ($fromDate && $toDate) {
            // Date range filter takes priority
            $baseQuery->whereBetween('tsm.created_at', [
                \Carbon\Carbon::parse($fromDate)->startOfDay(),
                \Carbon\Carbon::parse($toDate)->endOfDay(),
            ]);
        } elseif ($year) {
            // Year filter
            $baseQuery->whereYear('tsm.created_at', $year);
        } elseif ($type == 1) {
            // No date filter - show all data (don't add any where clause)
            // This is intentional - leave it empty
        } else {
            // Default to current year
            $baseQuery->whereYear('tsm.created_at', now()->year);
        }

        // Get state-wise counts
        $results = $baseQuery
            ->select(\Illuminate\Support\Facades\DB::raw("COALESCE(st.name, 'Not Specified') as name"), \Illuminate\Support\Facades\DB::raw('COUNT(tms.id) as count'))
            ->groupBy(\Illuminate\Support\Facades\DB::raw("COALESCE(st.name, 'Not Specified')"))
            ->orderBy('count', 'desc')
            ->get();

        // If no results, return empty array
        if ($results->isEmpty()) {
            return response()->json([]);
        }

        $totalCount = $results->sum('count');

        // Format for Highcharts bar chart
        $data = [];
        foreach ($results as $result) {
            $data[] = [
                'name' => ucwords(strtolower($result->name)), // Proper case state names
                'y' => (int) $result->count,
            ];
        }

        return response()->json($data);
    }

    /*
|--------------------------------------------------------------------------
| 14. Registered MSE vs Onboarded MSE Per Month (Bar/Column Chart)
|--------------------------------------------------------------------------
*/
    /*
    |--------------------------------------------------------------------------
    | 14. Registered MSE vs Onboarded MSE Per Month (Bar/Column Chart)
    |--------------------------------------------------------------------------
    */
    public function getRegisteredVsOnboardedMonthly(Request $request)
    {
        $year = $request->filled('year') ? (int) $request->year : null;
        $type = $request->filled('type') ? (int) $request->type : null;
        $fromDate = $request->filled('from_date_new') ? $request->from_date_new : null;
        $toDate = $request->filled('to_date_new') ? $request->to_date_new : null;

        // Get all months (Jan-Dec)
        $months = [
            1 => 'Jan',
            2 => 'Feb',
            3 => 'Mar',
            4 => 'Apr',
            5 => 'May',
            6 => 'Jun',
            7 => 'Jul',
            8 => 'Aug',
            9 => 'Sep',
            10 => 'Oct',
            11 => 'Nov',
            12 => 'Dec'
        ];

        $result = [];

        foreach ($months as $monthNum => $monthName) {
            // Registered MSE Query
            $registeredQuery = DB::table('team_msme_schemes as ms')
                ->whereNotNull('ms.major_activity')
                ->where('ms.major_activity', '!=', '');

            // Onboarded MSE Query
            $onboardedQuery = DB::table('team_msme_schemes as ms')
                ->join('team_snpmsme_mapping as tsm', 'tsm.msme_id', '=', 'ms.id')
                ->where('tsm.status', 1)
                ->whereNotNull('ms.major_activity')
                ->where('ms.major_activity', '!=', '');

            // Apply date filters for each query
            $this->applyMonthlyDateFilter($registeredQuery, $fromDate, $toDate, $year, $type, $monthNum, 'ms');
            $this->applyMonthlyDateFilter($onboardedQuery, $fromDate, $toDate, $year, $type, $monthNum, 'tsm');

            $registeredCount = (clone $registeredQuery)->count();
            $onboardedCount = (clone $onboardedQuery)->count();

            $result[] = [
                'month' => $monthName,
                'month_num' => $monthNum,
                'registered' => (int) $registeredCount,
                'onboarded' => (int) $onboardedCount,
            ];
        }

        // FIXED: Always show all months when no specific year or date range is selected
        // Only filter out months when we have a specific year or date range AND want to show only months with data
        $hasSpecificFilter = (!empty($fromDate) && !empty($toDate)) || (!empty($year));

        // If no specific filter is applied (All Time), show ALL months (including future months with zero data)
        if (!$hasSpecificFilter) {
            // Keep all months, even those with zero data
            // No filtering needed
        } else {
            // When filters are applied, optionally filter out months with zero data
            // This is optional - you can remove this if you want to show all months even with filters
            $result = array_filter($result, function ($item) {
                return $item['registered'] > 0 || $item['onboarded'] > 0;
            });
        }

        return response()->json([
            'data' => array_values($result),
            'total_registered' => array_sum(array_column($result, 'registered')),
            'total_onboarded' => array_sum(array_column($result, 'onboarded')),
            'has_data' => count($result) > 0
        ]);
    }

    private function applyMonthlyDateFilter($query, $fromDate, $toDate, $year, $type, $monthNum, $tableAlias = 'ms')
    {

        if (!empty($fromDate) && !empty($toDate)) {
            // Date range filter
            $startDate = Carbon::parse($fromDate)->startOfDay();
            $endDate = Carbon::parse($toDate)->endOfDay();

            $query->whereBetween($tableAlias . '.created_at', [$startDate, $endDate])
                ->whereMonth($tableAlias . '.created_at', $monthNum);
        } elseif (!empty($year)) {
            // Year filter - specific year provided
            $query->whereYear($tableAlias . '.created_at', (int) $year)
                ->whereMonth($tableAlias . '.created_at', $monthNum);
        } else {
            // No specific filters - "All Time" mode
            // Don't add any year condition, just filter by month
            // This will get data from all years for this month
            $query->whereMonth($tableAlias . '.created_at', $monthNum);
        }
    }
    /*
    |--------------------------------------------------------------------------
    | Helpers
    |--------------------------------------------------------------------------
    */
    private function resolveFilters(Request $request): array
    {
        // Default to type=1 (all-time, no date filter) when no explicit type sent.
        // This ensures the Blade SSR initial render matches the initial AJAX call
        // (which also sends type=1 via getFilterParams when no year is selected).
        $type = $request->type !== null ? (int) $request->type : 1;
        $fromDate = null;
        $toDate = null;
        $selectedYear = null;

        if (!empty($request->from_date_new) && !empty($request->to_date_new)) {
            $fromDate = $request->from_date_new;
            $toDate = $request->to_date_new;
        } elseif (!empty($request->year)) {
            $selectedYear = $request->year;
        } else {
            if ($type == 1) {
                $selectedYear = null;
            }
        }

        return [$selectedYear, $fromDate, $toDate, $type];
    }

    private function buildMseData(?string $year, ?string $fromDate, ?string $toDate, ?int $type): array
    {
        $fundMetrics = null;
        if (acl(config('permissions.allocation-view')) || acl(config('permissions.fund-distribution-view'))) {
            $fundMetrics = app(DashboardService::class)->getFundManagementMetricsData($year, $fromDate, $toDate, $type);
        }

        return [
            'fundMetrics' => $fundMetrics,
            'registeredMse' => $this->service->getTotalRegisteredMse($year, $fromDate, $toDate, $type),
            'openMse' => $this->service->getOpenMse($year, $fromDate, $toDate, $type),
            'directSelection' => $this->service->getDirectSelectionMse($year, $fromDate, $toDate, $type),
            'onboardedMse' => $this->service->getOnboardedMse($year, $fromDate, $toDate, $type),
            'womenMse' => $this->service->getWomenOwnedMse($year, $fromDate, $toDate, $type),
            'bulkUpload' => $this->service->getMseBulkUpload($year, $fromDate, $toDate, $type),
            'twoStep' => $this->service->getMseTwoStep($year, $fromDate, $toDate, $type),
            'snpCount' => $this->service->getSnpRegistrationCount($year, $fromDate, $toDate, $type),
            'bnpCount' => $this->service->getBnpRegistrationCount($year, $fromDate, $toDate, $type),
            'lspCount' => $this->service->getLspRegistrationCount($year, $fromDate, $toDate, $type),
            'associationsCount' => $this->service->getAssociationsCount($year, $fromDate, $toDate, $type),

            'total_batches_submitted_by_np' => $this->getTotalSubmittedByNPs($year, $fromDate, $toDate, $type, null),
            'total_claims_submitted_by_np' => $this->getTotalClaimsSubmittedByNPs($year, $fromDate, $toDate, $type, null),
            'total_batches_pending_with_ondc' => $this->getTotalBatchesPendingWithONDC($year, $fromDate, $toDate, $type, null),
            'total_claims_pending_with_ondc' => $this->getTotalClaimsPendingWithONDC($year, $fromDate, $toDate, $type, null),
            'total_batches_pending_with_nsic' => $this->getTotalBatchesPendingWithNSIC($year, $fromDate, $toDate, $type, null),
            'total_claims_pending_with_nsic' => $this->getTotalClaimsPendingWithNSIC($year, $fromDate, $toDate, $type, null),
            'total_batches_pending_with_nps' => $this->getTotalBatchesPendingWithNPs($year, $fromDate, $toDate, $type, null),
            'total_amount_pending_with_nps' => $this->getTotalAmountPendingWithNPs($year, $fromDate, $toDate, $type, null),
            'total_claims_pending_with_nps' => $this->getTotalClaimsPendingWithNPs($year, $fromDate, $toDate, $type, null),
            'total_batches_pending_with_finance' => $this->getTotalBatchesPendingWithFinance($year, $fromDate, $toDate, $type, null),
            'total_claims_pending_with_finance' => $this->getTotalClaimsPendingWithFinance($year, $fromDate, $toDate, $type, null),
            'total_approved_batches' => $this->getTotalApprovedBatches($year, $fromDate, $toDate, $type, null),

            'total_approved_claims' => $this->getTotalApprovedClaims($year, $fromDate, $toDate, $type, null),

            'total_rejected_batches' => $this->getTotalRejectedBatches($year, $fromDate, $toDate, $type, null),
            'total_rejected_claims' => $this->getTotalRejectedClaims($year, $fromDate, $toDate, $type, null),
            'total_amount_submitted_by_nps' => $this->getTotalAmountSubmittedByNPs($year, $fromDate, $toDate, $type, null),
            'total_amount_pending_with_ondc' => $this->getTotalAmountPendingWithONDC($year, $fromDate, $toDate, $type, null),
            'total_amount_pending_with_nsic' => $this->getTotalAmountPendingWithNSIC($year, $fromDate, $toDate, $type, null),
            'total_amount_pending_with_finance' => $this->getTotalAmountPendingWithFinance($year, $fromDate, $toDate, $type, null),
            'total_amount_approved' => $this->getTotalApprovedAmount($year, $fromDate, $toDate, $type, null),

            'total_amount_rejected' => $this->getTotalRejectedAmount($year, $fromDate, $toDate, $type, null),
            'total_amount_pending' => $this->getTotalAmountPending($year, $fromDate, $toDate, $type, null),
            'total_batches_pending' => $this->getTotalBatchesPending($year, $fromDate, $toDate, $type, null),
            'total_claims_pending' => $this->getTotalClaimsPending($year, $fromDate, $toDate, $type, null),

            'total_payment_completed_claims' => $this->getTotalPaymentCompletedClaims($year, $fromDate, $toDate, $type, null),
            'total_payment_completed_batches' => $this->getTotalPaymentCompletedBatches($year, $fromDate, $toDate, $type, null),
            'total_amount_payment_completed' => $this->getTotalPaymentCompletedAmount($year, $fromDate, $toDate, $type, null),
        ];
    }
}
