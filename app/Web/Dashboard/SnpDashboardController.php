<?php

declare(strict_types=1);

namespace App\Web\Dashboard;

use App\Http\Controllers\ClientController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Traits\SubUserTrait;

final class SnpDashboardController extends ClientController
{
    use SubUserTrait;
    public function __construct(
        private DashboardService $service
        
    ) {
    }

    public function index(Request $request)
    {
        [$selectedYear, $fromDate, $toDate, $type] = $this->resolveFilters($request);

        $data = $this->buildMseDashboardData($selectedYear, $fromDate, $toDate, $type);

        if ($request->ajax()) {
            return response()->json($data);
        }

        return view('dashboard.snp-new', array_merge($data, ['selectedYear' => $selectedYear]))
            ->with('title', __('message.dashboard_list'));
    }

    private function resolveFilters(Request $request): array
    {
        // Default type = 1 (all-time, no date filter) when not supplied.
        $type = $request->type !== null ? (int) $request->type : 1;
        $fromDate = null;
        $toDate = null;
        $selectedYear = null;

        if (!empty($request->from_date_new) && !empty($request->to_date_new)) {
            $fromDate = $request->from_date_new;
            $toDate = $request->to_date_new;
            $type = 2;
        } elseif (!empty($request->year)) {
            $selectedYear = $request->year;
            $type = 2;
        }

        return [$selectedYear, $fromDate, $toDate, $type];
    }

    private function buildMseDashboardData(
        ?string $year,
        ?string $fromDate,
        ?string $toDate,
        ?int $type
    ): array {
        return [
            //'msmeForMeCounts'    => $this->service->getSnpMyMsmeCount($year, $fromDate, $toDate, $type),
            'msmeForMeCounts'    => $this->service->getOpenMsmeCountForSNP($year, $fromDate, $toDate, $type),
            'msmeChoosenCounts'  => $this->service->getSnpMsmeChoosenMeCount($year, $fromDate, $toDate, $type),
            'msmeCounts'         => $this->service->getSnpOnboardedCount($year, $fromDate, $toDate, $type),
            'msmeCountsByGender' => $this->service->getGenderWiseOnboardedMsmeCount($year, $fromDate, $toDate, $type),
            'claimCounts'        => $this->service->getClaimCountById($year, $fromDate, $toDate),
        ];
    }

    // public function getMsmeCategoryCountOnboarded(Request $request)
    // {
    //     $authId = (string) authId();
    //     $year = $request->filled('year') ? (int) $request->year : null;
    //     $type = $request->filled('type') ? (int) $request->type : null;

    //     $baseQuery = \Illuminate\Support\Facades\DB::table('team_msme_schemes as tms')
    //         ->join('team_snpmsme_mapping as tsm', 'tsm.msme_id', '=', 'tms.id')
    //         ->join('team_snp_scheme as tss', 'tss.id', '=', 'tsm.snp_id')
    //         ->where('tsm.status', 1)
    //         ->whereNotNull('tms.major_activity')
    //         ->where('tms.major_activity', '!=', '');

    //     if ($request->filled('from_date_new') && $request->filled('to_date_new')) {
    //         $baseQuery->whereBetween('tsm.created_at', [
    //             \Carbon\Carbon::parse($request->from_date_new)->startOfDay(),
    //             \Carbon\Carbon::parse($request->to_date_new)->endOfDay(),
    //         ]);
    //     } elseif ($year) {
    //         $baseQuery->whereYear('tsm.created_at', $year);
    //     } elseif ($type == 1) {
    //         // All time
    //     } else {
    //         $baseQuery->whereYear('tsm.created_at', now()->year);
    //     }

    //     $totalCount = (clone $baseQuery)->count();

    //     if ($totalCount === 0) {
    //         return response()->json(['data' => []]);
    //     }

    //     // Use Admin logic for categories since it's optimized
    //     $categoriesRaw = (clone $baseQuery)
    //         ->whereNotNull('tms.product_category_id')
    //         ->pluck('tms.product_category_id');

    //     $categoryCounts = [];
    //     foreach ($categoriesRaw as $jsonStr) {
    //         if (!$jsonStr) continue;

    //         $ids = json_decode((string) $jsonStr, true);
    //         if (is_array($ids)) {
    //             foreach ($ids as $id) {
    //                 $categoryCounts[$id] = ($categoryCounts[$id] ?? 0) + 1;
    //             }
    //         } else {
    //             $categoryCounts[$jsonStr] = ($categoryCounts[$jsonStr] ?? 0) + 1;
    //         }
    //     }

    //     if (empty($categoryCounts)) {
    //         return response()->json(['data' => []]);
    //     }

    //     $subDomains = \Illuminate\Support\Facades\DB::table('sub_domains')
    //         ->whereIn('id', array_keys($categoryCounts))
    //         ->pluck('name', 'id');

    //     $validCategoryCounts = [];
    //     foreach ($categoryCounts as $id => $count) {
    //         if (isset($subDomains[$id])) {
    //             $validCategoryCounts[$id] = $count;
    //         }
    //     }

    //     arsort($validCategoryCounts);
    //     $top10 = array_slice($validCategoryCounts, 0, 10, true);

    //     $subDomainStats = [];
    //     foreach ($top10 as $id => $count) {
    //         $subDomainStats[] = [
    //             'name' => $subDomains[$id],
    //             'y' => round(($count / $totalCount) * 100, 2),
    //             'count' => $count,
    //         ];
    //     }

    //     return response()->json(['data' => $subDomainStats]);
    // }
     public function getMsmeCategoryCountOnboarded(Request $request)
    {
        $year = $request->filled('year') ? (int) $request->year : null;
        $type = $request->filled('type') ? (int) $request->type : null;

        $userIds = $this->getUserIdsWithSubUsers(AuthId());
        $snpDetail = DB::table('network_providers')
            ->where('user_id', $userIds)
            ->first();

        $roleSelections = $snpDetail
            ? json_decode($snpDetail->role_selection_details, true)
            : [];

        if (empty($roleSelections)) {
            return response()->json([
                'data' => [],
                'total_count' => 0
            ]);
        }

        /*
        |--------------------------------------------------------------------------
        | National State ID
        |--------------------------------------------------------------------------
        */
        $nationalId = DB::table('states')
            ->where('slug', 'national')
            ->value('id');
        $baseQuery = DB::table('team_msme_schemes as ms')
            ->whereNull('ms.bpp_id')
            ->where('ms.select_snp', 0)
            ->whereIn('ms.major_activity', [
                'Manufacturing',
                'Trading',
                'Services'
            ])
            ->whereNotNull('ms.product_category_id')
            ->where('ms.product_category_id', '!=', '');

        /*
        |--------------------------------------------------------------------------
        | Date Filters
        |--------------------------------------------------------------------------
        */
        if ($request->filled('from_date_new') && $request->filled('to_date_new')) {

            $baseQuery->whereBetween('ms.created_at', [
                Carbon::parse($request->from_date_new)->startOfDay(),
                Carbon::parse($request->to_date_new)->endOfDay(),
            ]);

        } elseif ($type != 1 && !empty($year)) {

            $baseQuery->whereYear('ms.created_at', $year);
        }
        $baseQuery->where(function ($query) use (
            $roleSelections,
            $nationalId
        ) {

            foreach ($roleSelections as $role) {

            if (($role['role_name'] ?? '') !== 'Seller Network Participant (SNP)') {
                            continue;
                }
                if (empty($role['domain'])) {
                    continue;
                }

                $query->orWhere(function ($subQuery) use (
                    $role,
                    $nationalId
                ) {

                    /*
                    |--------------------------------------------------------------------------
                    | Category / Domain
                    |--------------------------------------------------------------------------
                    */
                    $subQuery->whereRaw(
                        'JSON_CONTAINS(ms.product_category_id, ?)',
                        ['"' . trim($role['domain']) . '"']
                    );

                    /*
                    |--------------------------------------------------------------------------
                    | ONDC Transaction Type
                    |--------------------------------------------------------------------------
                    */
                
                    if (($role['transaction_type_name'] ?? '') !== 'Both') {
                            $subQuery->where(function($subQuery) use ($role) {
                                $subQuery->where('ms.ondc_transaction_type_id', $role['transaction_type'])
                                    ->orWhere('ms.ondc_transaction_type_id', 'b44fb78b-d49e-11f0-922a-00155d022d06'); // Both
                            });
                        }
                        else {
                            $subQuery->whereIn(
                                'ms.ondc_transaction_type_id',
                                ['9e7e1e8b-5578-11f0-81dc-00155d022d06', // B2B
                                '36523ead-533d-11f0-81dc-00155d022d06', // B2C
                                'b44fb78b-d49e-11f0-922a-00155d022d06' ] //Both
                            );
                        }
                    /*
                    |--------------------------------------------------------------------------
                    | Serviceability
                    |--------------------------------------------------------------------------
                    */
                    if (
                        !empty($role['serviceability']) &&
                        $role['serviceability'] != $nationalId
                    ) {

                        $serviceability = $role['serviceability'];

                        /*
                        | In case serviceability is JSON/string
                        */
                        if (!is_array($serviceability)) {
                            $serviceability = [$serviceability];
                        }

                        $subQuery->whereIn(
                            'ms.state_id',
                            $serviceability
                        );
                    }
                });
            }
        });
        $msmeCategories = (clone $baseQuery)
            ->select(
                'ms.id',
                'ms.product_category_id'
            )
            ->distinct()
            ->get();

        /*
        |--------------------------------------------------------------------------
        | Total Unique MSME Count
        |--------------------------------------------------------------------------
        */
        $totalCount = $msmeCategories
            ->pluck('id')
            ->unique()
            ->count();

        if ($totalCount === 0) {
            return response()->json([
                'data' => [],
                'total_count' => 0
            ]);
        }
        $categoryCounts = [];

        foreach ($msmeCategories as $msme) {

            if (empty($msme->product_category_id)) {
                continue;
            }

            $categories = json_decode(
                (string) $msme->product_category_id,
                true
            );

            /*
            |--------------------------------------------------------------------------
            | JSON Array
            |--------------------------------------------------------------------------
            */
            if (is_array($categories)) {

                /*
                | Duplicate category IDs inside JSON ko remove karo
                */
                $categories = array_unique($categories);

                foreach ($categories as $categoryId) {

                    if (empty($categoryId)) {
                        continue;
                    }

                    $categoryCounts[$categoryId] =
                        ($categoryCounts[$categoryId] ?? 0) + 1;
                }

            } else {

                /*
                |--------------------------------------------------------------------------
                | Normal single category value
                |--------------------------------------------------------------------------
                */
                $categoryId = trim((string) $msme->product_category_id);

                if ($categoryId !== '') {

                    $categoryCounts[$categoryId] =
                        ($categoryCounts[$categoryId] ?? 0) + 1;
                }
            }
        }

        if (empty($categoryCounts)) {
            return response()->json([
                'data' => [],
                'total_count' => $totalCount
            ]);
        }

        /*
        |--------------------------------------------------------------------------
        | Get Category Names
        |--------------------------------------------------------------------------
        */
        $subDomains = DB::table('sub_domains')
            ->whereIn('id', array_keys($categoryCounts))
            ->pluck('name', 'id');

        /*
        |--------------------------------------------------------------------------
        | Only Valid Categories
        |--------------------------------------------------------------------------
        */
        $validCategoryCounts = [];

        foreach ($categoryCounts as $categoryId => $count) {

            if (isset($subDomains[$categoryId])) {

                $validCategoryCounts[$categoryId] = $count;
            }
        }

        /*
        |--------------------------------------------------------------------------
        | Sort Highest Count First
        |--------------------------------------------------------------------------
        */
        arsort($validCategoryCounts);

        /*
        |--------------------------------------------------------------------------
        | Top 10 Categories
        |--------------------------------------------------------------------------
        */
        $top10 = array_slice(
            $validCategoryCounts,
            0,
            10,
            true
        );

        /*
        |--------------------------------------------------------------------------
        | Final Response
        |--------------------------------------------------------------------------
        */
        $subDomainStats = [];

        foreach ($top10 as $categoryId => $count) {

            $subDomainStats[] = [
                'name' => $subDomains[$categoryId],
                'y' => round(
                    ($count / $totalCount) * 100,
                    2
                ),
                'count' => $count,
            ];
        }

        return response()->json([
            'data' => $subDomainStats,
            'total_count' => $totalCount
        ]);
    }
}
