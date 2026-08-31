<?php

declare(strict_types=1);

namespace App\Web\Dashboard;

use App\Core\BaseService;
use App\Web\SNP\SNPMSMEService;
use DB;
use Carbon\Carbon;
use App\Traits\SubUserTrait;

class DashboardService extends BaseService
{
    use SubUserTrait;

    public function getOpenMsmeCountForSNP($year = null, $fromDate = null, $toDate = null, $type = null)
    {
        $userIds = $this->getUserIdsWithSubUsers(AuthId());
        $snpDetail = DB::table('network_providers')->where('user_id', $userIds)->first();

        $roleSelections = $snpDetail ? json_decode($snpDetail->role_selection_details, true) : [];

        if (empty($roleSelections)) {
            return (object) [
                'total' => 0,
                'manufacturing' => 0,
                'trading' => 0,
                'services' => 0,
            ];
        }

        $nationalId = DB::table('states')
            ->where('slug', 'national')
            ->value('id');

        $query = DB::table('team_msme_schemes as ms')
            ->selectRaw("
                COUNT(*) AS total,
                SUM(CASE WHEN ms.major_activity = 'Manufacturing' THEN 1 ELSE 0 END) AS manufacturing,
                SUM(CASE WHEN ms.major_activity = 'Trading' THEN 1 ELSE 0 END) AS trading,
                SUM(CASE WHEN ms.major_activity = 'Services' THEN 1 ELSE 0 END) AS services
            ")
            ->whereNull('ms.bpp_id')
            ->where('ms.select_snp', 0)
            ->whereIn('ms.major_activity', [
                'Manufacturing',
                'Trading',
                'Services',
            ]);

        // Date Filters
        if ($fromDate && $toDate) {
            $query->whereBetween('ms.created_at', [
                Carbon::parse($fromDate)->startOfDay(),
                Carbon::parse($toDate)->endOfDay(),
            ]);
        } else {
            if ($type != 1 && !empty($year)) {
                $query->whereYear('ms.created_at', $year);
            }
        }

        // SNP Role Selection Filters — same logic used by the SNP Wise MIS Report,
        // so this card stays consistent with that report.
        \App\Domain\MIS\SnpWiseMISReportAction::applyRoleSelectionOpenFilters($query, $roleSelections, $nationalId);

        $result = $query->first();
    
        return [
            'total_msme' => (int) ($result->total ?? 0),
            'major_activities' => [
                'manufacturing' => (int) ($result->manufacturing ?? 0),
                'trading' => (int) ($result->trading ?? 0),
                'services' => (int) ($result->services ?? 0),
            ]
        ];
    }
    // public function getOpenMsmeCountForSNP($year = null, $fromDate = null, $toDate = null, $type = null)
    // {
    //     $snpDetail = DB::table('network_providers')->where('user_id', getSubUserAndParentIds(AuthId()))->first();

    //     $roleSelections = $snpDetail ? json_decode($snpDetail->role_selection_details, true) : [];

    //     if (empty($roleSelections)) {
    //         return (object) [
    //             'total' => 0,
    //             'manufacturing' => 0,
    //             'trading' => 0,
    //             'services' => 0,
    //         ];
    //     }

    //     $nationalId = DB::table('states')
    //         ->where('slug', 'national')
    //         ->value('id');

    //     $query = DB::table('team_msme_schemes as ms')
    //         ->selectRaw("
    //             COUNT(*) AS total,
    //             SUM(CASE WHEN ms.major_activity = 'Manufacturing' THEN 1 ELSE 0 END) AS manufacturing,
    //             SUM(CASE WHEN ms.major_activity = 'Trading' THEN 1 ELSE 0 END) AS trading,
    //             SUM(CASE WHEN ms.major_activity = 'Services' THEN 1 ELSE 0 END) AS services
    //         ")
    //         ->whereNull('ms.bpp_id')
    //         ->where('ms.select_snp', 0)
    //         ->whereIn('ms.major_activity', [
    //             'Manufacturing',
    //             'Trading',
    //             'Services',
    //         ]);

    //     // Date Filters
    //     if ($fromDate && $toDate) {
    //         $query->whereBetween('ms.created_at', [
    //             Carbon::parse($fromDate)->startOfDay(),
    //             Carbon::parse($toDate)->endOfDay(),
    //         ]);
    //     } else {
    //         if ($type != 1 && !empty($year)) {
    //             $query->whereYear('ms.created_at', $year);
    //         }
    //     }

    //     // SNP Role Selection Filters
    //     $query->where(function ($query) use ($roleSelections, $nationalId) {

    //         foreach ($roleSelections as $role) {

    //             if (($role['role_name'] ?? '') !== 'Seller Network Participant (SNP)') {
    //                 continue;
    //             }
    //             // echo "<pre>"; print_r($role); echo "</pre>";
    //             $query->orWhere(function ($subQuery) use ($role, $nationalId) {

    //                 $subQuery->whereRaw(
    //                     'JSON_CONTAINS(ms.product_category_id, ?)',
    //                     ['"' . trim($role['domain']) . '"']
    //                 );

    //                 if (($role['transaction_type_name'] ?? '') !== 'Both') {
    //                     $subQuery->where(
    //                         'ms.ondc_transaction_type_id',
    //                         $role['transaction_type']
    //                     );
    //                 } else {
    //                     $subQuery->whereIn(
    //                         'ms.ondc_transaction_type_id',
    //                         [
    //                             '9e7e1e8b-5578-11f0-81dc-00155d022d06', // B2B
    //                             '36523ead-533d-11f0-81dc-00155d022d06', // B2C
    //                             'b44fb78b-d49e-11f0-922a-00155d022d06'
    //                         ] //Both
    //                     );
    //                 }


    //                 if (
    //                     !empty($role['serviceability']) &&
    //                     $role['serviceability'] != $nationalId
    //                 ) {
    //                     $subQuery->whereIn(
    //                         'ms.state_id',
    //                         (array) $role['serviceability']
    //                     );
    //                 }
    //             });
    //         }
    //     });

    //     $result = $query->first();

    //     return [
    //         'total_msme' => (int) ($result->total ?? 0),
    //         'major_activities' => [
    //             'manufacturing' => (int) ($result->manufacturing ?? 0),
    //             'trading' => (int) ($result->trading ?? 0),
    //             'services' => (int) ($result->services ?? 0),
    //         ]
    //     ];
    // }

    public function getSnpMyMsmeCount($year = null, $fromDate = null, $toDate = null, $type = null)
    {

        $snp = app(SnpMSMEService::class)->getSnpDetail(AuthId());

        $stateIds = [];
        $transactionTypeIds = [];
        $subDomainIds = [];

        if (!empty($snp->state_id)) {
            $stateIds = json_decode($snp->state_id, true);
        }

        if (!empty($snp->transaction_type)) {
            $transactionTypeIds = json_decode($snp->transaction_type, true);
        }

        if (!empty($snp->sub_domain)) {
            $subDomainIds = json_decode($snp->sub_domain, true);
        }

        $nationalId = '5bc85de0-0292-11f1-922a-00155d022d06';


        $baseQuery = DB::table('team_msme_schemes as ms')->where('ms.select_snp', 0)->whereNull('ms.bpp_id');

        if (!in_array($nationalId, $stateIds)) {
            $baseQuery->where(function ($q) use ($stateIds) {
                $q->whereIn('ms.state_id', $stateIds)
                    ->orWhereIn('ms.state_id', ['5bc85de0-0292-11f1-922a-00155d022d06']);
            });
        }

        $baseQuery->whereIn('ms.ondc_transaction_type_id', $transactionTypeIds);

        $baseQuery->where(function ($q) use ($subDomainIds) {
            foreach ($subDomainIds as $sd) {
                $q->orWhereRaw('JSON_CONTAINS(ms.product_category_id, ?)', ['"' . $sd . '"']);
            }
        });


        if ($fromDate && $toDate) {
            $baseQuery->whereBetween('ms.created_at', [
                Carbon::parse($fromDate)->startOfDay(),
                Carbon::parse($toDate)->endOfDay(),
            ]);
        } else {
            if ($type != 1) {
                $baseQuery->whereYear('ms.created_at', $year);
            }
        }


        $totalCount = (clone $baseQuery)->count();

        $majorActivityCounts = (clone $baseQuery)
            ->select('ms.major_activity', DB::raw('COUNT(*) as total_count'))
            ->whereNotNull('ms.major_activity')
            ->where('ms.major_activity', '!=', '')
            ->groupBy('ms.major_activity')
            ->pluck('total_count', 'ms.major_activity')
            ->toArray();


        $defaultActivities = [
            'Manufacturing' => 0,
            'Services' => 0,
            'Trading' => 0,
        ];

        // Normalize DB values (avoid case mismatch issues)
        $normalized = [];
        foreach ($majorActivityCounts as $key => $value) {
            $formattedKey = ucfirst(strtolower(trim($key)));
            $normalized[$formattedKey] = (int) $value;
        }

        // Merge DB result into defaults
        $finalMajorActivities = array_merge($defaultActivities, $normalized);

        return [
            'total_msme' => $totalCount,
            'major_activities' => $finalMajorActivities
        ];
    }

    // public function getSnpOnboardedAndMsmeChoosenMeCount()
    // {
    //     $result = \DB::select('
    //         SELECT 
    //             -- total counts
    //             (SELECT COUNT(*)
    //             FROM team_msme_schemes ms
    //             INNER JOIN team_snpmsme_mapping tsm ON tsm.msme_id = ms.id
    //             INNER JOIN team_snp_scheme tss ON tsm.snp_id = tss.id
    //             WHERE tss.user_id = ? AND tsm.status = 1) AS onboarded,

    //             (SELECT COUNT(*)
    //             FROM team_msme_schemes ms
    //             INNER JOIN team_snpmsme_mapping tsm ON tsm.msme_id = ms.id
    //             INNER JOIN team_snp_scheme tss ON tsm.snp_id = tss.id
    //             WHERE tss.user_id = ? AND tsm.status = 0) AS chossen
    //     ', [(string) AuthId(), (string) AuthId()]);

    //     $flattened = isset($result[0]) ? (array) $result[0] : [];

    //     // Fetch major_activity-wise counts
    //     $majorActivity = \DB::select('
    //         SELECT 
    //             ms.major_activity,
    //             SUM(CASE WHEN tsm.status = 1 THEN 1 ELSE 0 END) AS onboarded_count,
    //             SUM(CASE WHEN tsm.status = 0 THEN 1 ELSE 0 END) AS chossen_count
    //         FROM team_msme_schemes ms
    //         INNER JOIN team_snpmsme_mapping tsm ON tsm.msme_id = ms.id
    //         INNER JOIN team_snp_scheme tss ON tsm.snp_id = tss.id
    //         WHERE tss.user_id = ?
    //         GROUP BY ms.major_activity
    //     ', [(string) AuthId()]);

    //     // Convert to associative array
    //     $majorActivityCounts = collect($majorActivity)->mapWithKeys(function ($item) {
    //         return [
    //             $item->major_activity => [
    //                 'onboarded' => $item->onboarded_count,
    //                 'chossen'   => $item->chossen_count
    //             ]
    //         ];
    //     });

    //     return [
    //         'total'            => $flattened,
    //         'major_activities' => $majorActivityCounts
    //     ];
    // }



    // public function getSnpMsmeChoosenMeCount($year = null, $fromDate = null, $toDate = null, $type = null)
    // {

    //     $defaultActivities = [
    //         'Manufacturing' => ['chossen' => 0],
    //         'Services' => ['chossen' => 0],
    //         'Trading' => ['chossen' => 0],
    //     ];



    //     $userId = (string) AuthId();

    //     $dateFilter = '';
    //     $dateBindings = [];

    //     if (!empty($fromDate) && !empty($toDate)) {

    //         $dateFilter = " AND tsm.created_at BETWEEN ? AND ?";
    //         $dateBindings[] = date('Y-m-d 00:00:00', strtotime($fromDate));
    //         $dateBindings[] = date('Y-m-d 23:59:59', strtotime($toDate));
    //     } else {
    //         if ($type != 1) {
    //             $dateFilter = " AND YEAR(tsm.created_at) = ?";
    //             $dateBindings[] = $year ?? date('Y');
    //         }
    //     }


    //     $bindings = array_merge(
    //         [$userId],      // user_id
    //         $dateBindings   // date filter bindings
    //     );

    //     $result = DB::select("
    // 			SELECT 
    // 				(
    // 					SELECT COUNT(*)
    // 					FROM team_msme_schemes ms
    // 					INNER JOIN team_snpmsme_mapping tsm 
    // 						ON tsm.msme_id = ms.id
    // 					INNER JOIN team_snp_scheme tss 
    // 						ON tsm.snp_id = tss.id
    // 					WHERE tss.user_id = ?
    // 					  AND ms.select_snp = 1
    // 					  AND tsm.status = 0
    // 					  {$dateFilter}
    // 					  AND ms.major_activity IS NOT NULL
    // 					  AND ms.major_activity != ''
    // 				) AS chossen
    // 		", $bindings);

    //     $total = !empty($result)
    //         ? (array) $result[0]
    //         : ['chossen' => 0];


    //     /*
    //         |--------------------------------------------------------------------------
    //         | MAJOR ACTIVITY WISE CHOSSEN COUNT
    //         |--------------------------------------------------------------------------
    //         */

    //     $majorActivityBindings = array_merge([$userId], $dateBindings);

    //     $majorActivity = DB::select("
    // 			SELECT 
    // 				ms.major_activity,
    // 				COUNT(*) AS chossen_count
    // 			FROM team_msme_schemes ms
    // 			INNER JOIN team_snpmsme_mapping tsm 
    // 				ON tsm.msme_id = ms.id
    // 			INNER JOIN team_snp_scheme tss 
    // 				ON tsm.snp_id = tss.id
    // 			WHERE tss.user_id = ?
    // 			  AND ms.select_snp = 1
    // 			  AND tsm.status = 0
    // 			  {$dateFilter}
    // 			  AND ms.major_activity IS NOT NULL
    // 			  AND ms.major_activity != ''
    // 			GROUP BY ms.major_activity
    // 		", $majorActivityBindings);

    //     $majorActivityCounts = collect($majorActivity)->mapWithKeys(function ($item) {
    //         return [
    //             $item->major_activity => [
    //                 'chossen' => (int) $item->chossen_count
    //             ],
    //         ];
    //     })->toArray();


    //     $finalMajorActivities = array_merge($defaultActivities, $majorActivityCounts);

    //     return [
    //         'total' => $total,
    //         'major_activities' => $finalMajorActivities,
    //     ];
    // }
    public function getSnpMsmeChoosenMeCount($year = null, $fromDate = null, $toDate = null, $type = null)
    {
        $defaultActivities = [
            'Manufacturing' => ['chossen' => 0],
            'Services' => ['chossen' => 0],
            'Trading' => ['chossen' => 0],
        ];

        $user = auth()->user();

        // -------------------------------------------------
        // USER IDS (USER + PARENT + SUB-USERS)
        // -------------------------------------------------
        $userIds = getSubUserAndParentIds(authId());

        $placeholders = implode(',', array_fill(0, count($userIds), '?'));

        // -------------------------------------------------
        // DATE FILTER
        // -------------------------------------------------
        $dateFilter = '';
        $dateBindings = [];

        if (!empty($fromDate) && !empty($toDate)) {
            $dateFilter = " AND tsm.created_at BETWEEN ? AND ?";
            $dateBindings[] = date('Y-m-d 00:00:00', strtotime($fromDate));
            $dateBindings[] = date('Y-m-d 23:59:59', strtotime($toDate));
        } else {
            if ($type != 1) {
                $dateFilter = " AND YEAR(tsm.created_at) = ?";
                $dateBindings[] = $year ?? date('Y');
            }
        }

        // -------------------------------------------------
        // TOTAL CHOSEN COUNT
        // -------------------------------------------------
        $bindings = array_merge($userIds, $dateBindings);

        $result = DB::select("
        SELECT (
            SELECT COUNT(*)
            FROM team_msme_schemes ms
            INNER JOIN team_snpmsme_mapping tsm 
                ON tsm.msme_id = ms.id
            INNER JOIN team_snp_scheme tss 
                ON tsm.snp_id = tss.id
            WHERE tss.user_id IN ($placeholders)
              AND ms.select_snp = 1
              AND tsm.status = 0
              {$dateFilter}
              AND ms.major_activity IS NOT NULL
              AND ms.major_activity != ''
        ) AS chossen
    ", $bindings);

        $total = !empty($result)
            ? (array) $result[0]
            : ['chossen' => 0];

        // -------------------------------------------------
        // MAJOR ACTIVITY WISE COUNT
        // -------------------------------------------------
        $majorActivityBindings = array_merge($userIds, $dateBindings);

        $majorActivity = DB::select("
        SELECT 
            ms.major_activity,
            COUNT(*) AS chossen_count
        FROM team_msme_schemes ms
        INNER JOIN team_snpmsme_mapping tsm 
            ON tsm.msme_id = ms.id
        INNER JOIN team_snp_scheme tss 
            ON tsm.snp_id = tss.id
        WHERE tss.user_id IN ($placeholders)
          AND ms.select_snp = 1
          AND tsm.status = 0
          {$dateFilter}
          AND ms.major_activity IS NOT NULL
          AND ms.major_activity != ''
        GROUP BY ms.major_activity
    ", $majorActivityBindings);

        $majorActivityCounts = collect($majorActivity)->mapWithKeys(function ($item) {
            return [
                $item->major_activity => [
                    'chossen' => (int) $item->chossen_count
                ],
            ];
        })->toArray();

        $finalMajorActivities = array_merge($defaultActivities, $majorActivityCounts);

        return [
            'total' => $total,
            'major_activities' => $finalMajorActivities,
        ];
    }


    // public function getSnpOnboardedCount($year = null, $fromDate = null, $toDate = null, $type = null)
    // {

    //     $defaultActivities = [
    //         'Manufacturing' => ['onboarded' => 0],
    //         'Services' => ['onboarded' => 0],
    //         'Trading' => ['onboarded' => 0],
    //     ];


    //     $userId = (string) AuthId();

    //     $dateFilter = '';
    //     $dateBindings = [];

    //     if (!empty($fromDate) && !empty($toDate)) {

    //         $dateFilter = " AND tsm.created_at BETWEEN ? AND ?";
    //         $dateBindings[] = date('Y-m-d 00:00:00', strtotime($fromDate));
    //         $dateBindings[] = date('Y-m-d 23:59:59', strtotime($toDate));
    //     } else {
    //         if ($type != 1) {
    //             $dateFilter = " AND YEAR(tsm.created_at) = ?";
    //             $dateBindings[] = $year ?? date('Y');
    //         }
    //     }


    //     $bindings = array_merge(
    //         [$userId],
    //         $dateBindings
    //     );

    //     $result = DB::select("
    // 			SELECT 
    // 				(
    // 					SELECT COUNT(*)
    // 					FROM team_msme_schemes ms
    // 					INNER JOIN team_snpmsme_mapping tsm 
    // 						ON tsm.msme_id = ms.id
    // 					INNER JOIN team_snp_scheme tss 
    // 						ON tsm.snp_id = tss.id
    // 					WHERE tss.user_id = ?
    // 					  AND tsm.status = 1
    // 					  {$dateFilter}
    // 					  AND ms.major_activity IS NOT NULL
    // 					  AND ms.major_activity != ''
    // 				) AS onboarded
    // 		", $bindings);   // ← comma removed here

    //     $total = !empty($result)
    //         ? (array) $result[0]
    //         : ['onboarded' => 0];


    //     /*
    //         |--------------------------------------------------------------------------
    //         | MAJOR ACTIVITY WISE ONBOARDED COUNT
    //         |--------------------------------------------------------------------------
    //         */

    //     $majorActivityBindings = array_merge([$userId], $dateBindings);

    //     $majorActivity = DB::select("
    // 			SELECT 
    // 				ms.major_activity,
    // 				COUNT(*) AS onboarded_count
    // 			FROM team_msme_schemes ms
    // 			INNER JOIN team_snpmsme_mapping tsm 
    // 				ON tsm.msme_id = ms.id
    // 			INNER JOIN team_snp_scheme tss 
    // 				ON tsm.snp_id = tss.id
    // 			WHERE tss.user_id = ?
    // 			  AND tsm.status = 1
    // 			  {$dateFilter}
    // 			  AND ms.major_activity IS NOT NULL
    // 			  AND ms.major_activity != ''
    // 			GROUP BY ms.major_activity
    // 		", $majorActivityBindings);

    //     $majorActivityCounts = collect($majorActivity)->mapWithKeys(function ($item) {
    //         return [
    //             $item->major_activity => [
    //                 'onboarded' => (int) $item->onboarded_count,
    //             ],
    //         ];
    //     })->toArray();

    //     $finalMajorActivities = array_merge($defaultActivities, $majorActivityCounts);


    //     return [
    //         'total' => $total,
    //         'major_activities' => $finalMajorActivities,
    //     ];
    // }

    public function getSnpOnboardedCount($year = null, $fromDate = null, $toDate = null, $type = null)
    {
        $defaultActivities = [
            'Manufacturing' => ['onboarded' => 0],
            'Services' => ['onboarded' => 0],
            'Trading' => ['onboarded' => 0],
        ];

        $user = auth()->user();

        // -------------------------------------------------
        // USER + PARENT + SUB-USERS IDS
        // -------------------------------------------------
        $userIds = getSubUserAndParentIds(authId());

        $placeholders = implode(',', array_fill(0, count($userIds), '?'));

        // -------------------------------------------------
        // DATE FILTER
        // -------------------------------------------------
        $dateFilter = '';
        $dateBindings = [];

        if (!empty($fromDate) && !empty($toDate)) {
            $dateFilter = " AND tsm.created_at BETWEEN ? AND ?";
            $dateBindings[] = date('Y-m-d 00:00:00', strtotime($fromDate));
            $dateBindings[] = date('Y-m-d 23:59:59', strtotime($toDate));
        } else {
            if ($type != 1) {
                $dateFilter = " AND YEAR(tsm.created_at) = ?";
                $dateBindings[] = $year ?? date('Y');
            }
        }

        // -------------------------------------------------
        // TOTAL ONBOARDED COUNT
        // -------------------------------------------------
        $bindings = array_merge($userIds, $dateBindings);

        $result = DB::select("
            SELECT (
                SELECT COUNT(*)
                FROM team_msme_schemes ms
                INNER JOIN team_snpmsme_mapping tsm 
                    ON tsm.msme_id = ms.id
                INNER JOIN team_snp_scheme tss 
                    ON tsm.snp_id = tss.id
                WHERE tss.user_id IN ($placeholders)
                AND tsm.status = 1
                {$dateFilter}
                AND ms.major_activity IS NOT NULL
                AND ms.major_activity != ''
            ) AS onboarded
        ", $bindings);

        $total = !empty($result)
            ? (array) $result[0]
            : ['onboarded' => 0];

        // -------------------------------------------------
        // MAJOR ACTIVITY WISE ONBOARDED
        // -------------------------------------------------
        $majorActivityBindings = array_merge($userIds, $dateBindings);

        $majorActivity = DB::select("
            SELECT 
                ms.major_activity,
                COUNT(*) AS onboarded_count
            FROM team_msme_schemes ms
            INNER JOIN team_snpmsme_mapping tsm 
                ON tsm.msme_id = ms.id
            INNER JOIN team_snp_scheme tss 
                ON tsm.snp_id = tss.id
            WHERE tss.user_id IN ($placeholders)
            AND tsm.status = 1
            {$dateFilter}
            AND ms.major_activity IS NOT NULL
            AND ms.major_activity != ''
            GROUP BY ms.major_activity
        ", $majorActivityBindings);

        $majorActivityCounts = collect($majorActivity)->mapWithKeys(function ($item) {
            return [
                $item->major_activity => [
                    'onboarded' => (int) $item->onboarded_count,
                ],
            ];
        })->toArray();

        $finalMajorActivities = array_merge($defaultActivities, $majorActivityCounts);

        return [
            'total' => $total,
            'major_activities' => $finalMajorActivities,
        ];
    }


    public function getOthersRegisteredMsmeCount($year = null, $fromDate = null, $toDate = null, $type = null)
    {

        $baseQuery = DB::table('team_msme_schemes as ms');


        if ($fromDate && $toDate) {
            $baseQuery->whereBetween('ms.created_at', [
                Carbon::parse($fromDate)->startOfDay(),
                Carbon::parse($toDate)->endOfDay(),
            ]);
        } else {

            if ($type != 1) {
                $baseQuery->whereYear('ms.created_at', $year);
            }
        }

        $baseQuery->whereNotNull('ms.major_activity')->where('ms.major_activity', '!=', '');

        $totalCount = (clone $baseQuery)->count();

        $majorActivityCounts = (clone $baseQuery)
            ->select('ms.major_activity', DB::raw('COUNT(*) as total_count'))
            ->groupBy('ms.major_activity')
            ->pluck('total_count', 'ms.major_activity')
            ->toArray();

        /*
            |--------------------------------------------------------------------------
            | Default Values (Always Visible)
            |--------------------------------------------------------------------------
            */

        $defaultActivities = [
            'Manufacturing' => 0,
            'Services' => 0,
            'Trading' => 0,
        ];

        // Normalize database values (handle case mismatch safely)
        $normalized = [];
        foreach ($majorActivityCounts as $key => $value) {
            $formattedKey = ucfirst(strtolower(trim($key)));
            $normalized[$formattedKey] = (int) $value;
        }

        // Merge DB values into defaults
        $finalMajorActivities = array_merge($defaultActivities, $normalized);

        return [
            'total_msme' => (int) $totalCount,
            'major_activities' => $finalMajorActivities,
        ];



        /* $totalCount = (clone $baseQuery)->count();
        $majorActivityCounts = (clone $baseQuery)
            ->select('ms.major_activity', DB::raw('COUNT(*) as total_count'))
            ->groupBy('ms.major_activity')
            ->pluck('total_count', 'ms.major_activity');

        return [
            'total_msme'       => (int) $totalCount,
            'major_activities' => $majorActivityCounts,
        ]; */
    }


    public function getSnpOtherRolesOpenMsmeCount($year = null, $fromDate = null, $toDate = null, $type = null)
    {

        //dd($year);
        $baseQuery = DB::table('team_msme_schemes as ms')->where('ms.select_snp', 0)->whereNull('ms.bpp_id');

        if ($fromDate && $toDate) {
            $baseQuery->whereBetween('ms.created_at', [
                Carbon::parse($fromDate)->startOfDay(),
                Carbon::parse($toDate)->endOfDay(),
            ]);
        } else {
            if ($type != 1) {
                $baseQuery->whereYear('ms.created_at', $year);
            }
        }


        $baseQuery->whereNotNull('ms.major_activity')->where('ms.major_activity', '!=', '');

        $totalCount = (clone $baseQuery)->count();

        $majorActivityCounts = (clone $baseQuery)
            ->select('ms.major_activity', DB::raw('COUNT(*) as total_count'))
            ->groupBy('ms.major_activity')
            ->pluck('total_count', 'ms.major_activity')
            ->toArray();

        /*
            |--------------------------------------------------------------------------
            | Always Show These 3 Activities
            |--------------------------------------------------------------------------
            */

        $defaultActivities = [
            'Manufacturing' => 0,
            'Services' => 0,
            'Trading' => 0,
        ];

        // Normalize DB values (avoid case mismatch problems)
        $normalized = [];
        foreach ($majorActivityCounts as $key => $value) {
            $formattedKey = ucfirst(strtolower(trim($key)));
            $normalized[$formattedKey] = (int) $value;
        }

        // Merge DB results into defaults
        $finalMajorActivities = array_merge($defaultActivities, $normalized);

        return [
            'total_msme_open' => (int) $totalCount,
            'major_activities' => $finalMajorActivities
        ];



        /* $totalCount = (clone $baseQuery)->count();

        $majorActivityCounts = (clone $baseQuery)
            ->select('ms.major_activity', DB::raw('COUNT(*) as total_count'))
            ->groupBy('ms.major_activity')
            ->pluck('total_count', 'ms.major_activity');

        return [
            'total_msme_open'        => $totalCount,
            'major_activities'  => $majorActivityCounts
        ]; */
    }


    public function getSnpOtherRolesChoosenMsmeCount($year = null, $fromDate = null, $toDate = null, $type = null)
    {

        $defaultActivities = [
            'Manufacturing' => ['chossen' => 0],
            'Services' => ['chossen' => 0],
            'Trading' => ['chossen' => 0],
        ];

        $dateFilter = '';
        $dateBindings = [];

        if (!empty($fromDate) && !empty($toDate)) {

            $dateFilter = " AND tsm.created_at BETWEEN ? AND ?";
            $dateBindings[] = date('Y-m-d 00:00:00', strtotime($fromDate));
            $dateBindings[] = date('Y-m-d 23:59:59', strtotime($toDate));
        } else {
            if ($type != 1) {

                $dateFilter = " AND YEAR(tsm.created_at) = ?";
                $dateBindings[] = $year ?? date('Y');
            }
        }


        $result = DB::select("
				SELECT 
					(
						SELECT COUNT(*)
						FROM team_msme_schemes ms
						INNER JOIN team_snpmsme_mapping tsm 
							ON tsm.msme_id = ms.id
						INNER JOIN team_snp_scheme tss 
							ON tsm.snp_id = tss.id
						WHERE ms.select_snp = 1
						  AND tsm.status = 0
						  {$dateFilter}
						  AND ms.major_activity IS NOT NULL
						  AND ms.major_activity != ''
					) AS chossen
			", $dateBindings);

        $total = !empty($result)
            ? (array) $result[0]
            : ['chossen' => 0];



        $majorActivity = DB::select("
				SELECT 
					ms.major_activity,
					COUNT(*) AS chossen_count
				FROM team_msme_schemes ms
				INNER JOIN team_snpmsme_mapping tsm 
					ON tsm.msme_id = ms.id
				INNER JOIN team_snp_scheme tss 
					ON tsm.snp_id = tss.id
				WHERE ms.select_snp = 1
				  AND tsm.status = 0
				  {$dateFilter}
				  AND ms.major_activity IS NOT NULL
				  AND ms.major_activity != ''
				GROUP BY ms.major_activity
			", $dateBindings);

        $majorActivityCounts = collect($majorActivity)->mapWithKeys(function ($item) {
            return [
                $item->major_activity => [
                    'chossen' => (int) $item->chossen_count
                ],
            ];
        })->toArray();

        $finalMajorActivities = array_merge($defaultActivities, $majorActivityCounts);

        return [
            'total' => $total,
            'major_activities' => $finalMajorActivities,
        ];
    }


    public function getSnpOtherRolesOnboardedCount($year = null, $fromDate = null, $toDate = null, $type = null)
    {

        $defaultActivities = [
            'Manufacturing' => ['onboarded' => 0],
            'Services' => ['onboarded' => 0],
            'Trading' => ['onboarded' => 0],
        ];

        $dateFilter = '';
        $dateBindings = [];

        if (!empty($fromDate) && !empty($toDate)) {

            $dateFilter = " AND tsm.created_at BETWEEN ? AND ?";
            $dateBindings[] = date('Y-m-d 00:00:00', strtotime($fromDate));
            $dateBindings[] = date('Y-m-d 23:59:59', strtotime($toDate));
        } else {
            if ($type != 1) {
                $dateFilter = " AND YEAR(tsm.created_at) = ?";
                $dateBindings[] = $year ?? date('Y');
            }
        }


        $result = DB::select("
				SELECT COUNT(*) AS onboarded
				FROM team_msme_schemes ms
				INNER JOIN team_snpmsme_mapping tsm 
					ON tsm.msme_id = ms.id
				INNER JOIN team_snp_scheme tss 
					ON tsm.snp_id = tss.id
				WHERE tsm.status = 1
				  {$dateFilter}
				  AND ms.major_activity IS NOT NULL
				  AND ms.major_activity != ''
			", $dateBindings);

        $total = !empty($result)
            ? (array) $result[0]
            : ['onboarded' => 0];


        $majorActivity = DB::select("
				SELECT 
					ms.major_activity,
					COUNT(*) AS onboarded_count
				FROM team_msme_schemes ms
				INNER JOIN team_snpmsme_mapping tsm 
					ON tsm.msme_id = ms.id
				INNER JOIN team_snp_scheme tss 
					ON tsm.snp_id = tss.id
				WHERE tsm.status = 1
				  {$dateFilter}
				  AND ms.major_activity IS NOT NULL
				  AND ms.major_activity != ''
				GROUP BY ms.major_activity
			", $dateBindings);

        $majorActivityCounts = collect($majorActivity)->mapWithKeys(function ($item) {
            return [
                $item->major_activity => [
                    'onboarded' => (int) $item->onboarded_count,
                ],
            ];
        })->toArray();

        $finalMajorActivities = array_merge($defaultActivities, $majorActivityCounts);

        return [
            'total' => $total,
            'major_activities' => $finalMajorActivities,
        ];
    }




    public function getGenderWiseOnboardedMsmeCount($year = null, $fromDate = null, $toDate = null, $type = null)
    {
        /*$userId = (string) AuthId();

        $dateFilter = '';
        $bindings = [$userId];

        if ($fromDate && $toDate) {
            $dateFilter .= " AND DATE(ms.created_at) BETWEEN ? AND ?";
            $bindings[] = $fromDate;
            $bindings[] = $toDate;
        }

        if ($year) {
            $dateFilter .= " AND YEAR(ms.created_at) = ?";
            $bindings[] = $year;
        }

        $result = \DB::select("
            SELECT 
                COALESCE(ms.gender, 'Not Specified') AS gender,
                COUNT(*) AS total
            FROM team_msme_schemes ms
            INNER JOIN team_snpmsme_mapping tsm ON tsm.msme_id = ms.id
            INNER JOIN team_snp_scheme tss ON tsm.snp_id = tss.id
            WHERE tss.user_id = ?
            AND tsm.status = 1   -- only onboarded
            {$dateFilter}
            GROUP BY ms.gender
        ", $bindings);

        $genderWiseCounts = collect($result)->mapWithKeys(function ($item) {
            return [
                $item->gender => $item->total
            ];
        });

        return [
            'gender_wise' => $genderWiseCounts
        ];*/


        $userIds = getSubUserAndParentIds(authId());
        $userIdPlaceholders = implode(',', array_fill(0, count($userIds), '?'));

        $dateFilter = '';
        $bindings = $userIds;

        /*
            |--------------------------------------------------------------------------
            | Date filter (either date range OR year)
            |--------------------------------------------------------------------------
            */
        if (!empty($fromDate) && !empty($toDate)) {

            $dateFilter = " AND ms.created_at BETWEEN ? AND ?";
            $bindings[] = Carbon::parse($fromDate)->startOfDay();
            $bindings[] = Carbon::parse($toDate)->endOfDay();
        } else {
            if ($type != 1) {
                $dateFilter = " AND YEAR(ms.created_at) = ?";
                $bindings[] = $year ?? date('Y');
            }
        }

        /*
            |--------------------------------------------------------------------------
            | Gender-wise onboarded count
            |--------------------------------------------------------------------------
            */
        $result = DB::select("
                SELECT 
                    COALESCE(ms.gender, 'Not Specified') AS gender,
                    COUNT(*) AS total
                FROM team_msme_schemes ms
                INNER JOIN team_snpmsme_mapping tsm ON tsm.msme_id = ms.id
                INNER JOIN team_snp_scheme tss ON tsm.snp_id = tss.id
                WHERE tss.user_id IN ($userIdPlaceholders)
                AND tsm.status = 1
                {$dateFilter}
                GROUP BY ms.gender
            ", $bindings);

        /*
            |--------------------------------------------------------------------------
            | Format response
            |--------------------------------------------------------------------------
            */
        $genderWiseCounts = collect($result)->mapWithKeys(function ($item) {
            return [
                $item->gender => (int) $item->total,
            ];
        });

        return [
            'gender_wise' => $genderWiseCounts,
        ];
    }




    // public function getOthersMyMsmeCount()
    // {
    //     // base query for reuse
    //     $baseQuery = DB::table('team_msme_schemes as ms');

    //     // 1️⃣ Total count
    //     $totalCount = (clone $baseQuery)->count();

    //     // 2️⃣ Major activity counts
    //     $majorActivityCounts = (clone $baseQuery)
    //         ->select('ms.major_activity', DB::raw('COUNT(*) as total_count'))
    //         ->groupBy('ms.major_activity')
    //         ->pluck('total_count', 'ms.major_activity'); // returns key => value

    //     return [
    //         'total_msme'        => $totalCount,
    //         'major_activities'  => $majorActivityCounts
    //     ];
    // }






    // public function getOthersOnboardedAndMsmeChoosenMeCount()
    // {
    //     $result = \DB::select('
    //         SELECT 
    //             (SELECT COUNT(*) FROM team_msme_schemes ms WHERE ms.select_snp = 0) AS option1,
    //             (SELECT COUNT(*) FROM team_msme_schemes ms WHERE ms.select_snp = 1) AS option2,
    //             (SELECT COUNT(*) 
    //             FROM team_msme_schemes ms
    //             INNER JOIN team_snpmsme_mapping tsm ON tsm.msme_id = ms.id
    //             WHERE tsm.status = 1) AS onboarded
    //     ');

    //     $flattened = isset($result[0]) ? (array) $result[0] : [];

    //     // ✅ FIX: onboarded count uses a separate inner join to avoid left join row inflation
    //     $majorActivity = \DB::select('
    //         SELECT 
    //             ms.major_activity,

    //             COUNT(CASE WHEN ms.select_snp = 0 THEN 1 END) AS option1_count,
    //             COUNT(CASE WHEN ms.select_snp = 1 THEN 1 END) AS option2_count,

    //             (
    //                 SELECT COUNT(*) 
    //                 FROM team_snpmsme_mapping tsm
    //                 INNER JOIN team_msme_schemes ms2 ON ms2.id = tsm.msme_id
    //                 WHERE tsm.status = 1 AND ms2.major_activity = ms.major_activity
    //             ) AS onboarded_count

    //         FROM team_msme_schemes ms
    //         GROUP BY ms.major_activity
    //     ');

    //     $majorActivityCounts = collect($majorActivity)->mapWithKeys(function ($item) {
    //         return [
    //             $item->major_activity => [
    //                 'option1'   => (int) $item->option1_count,
    //                 'option2'   => (int) $item->option2_count,
    //                 'onboarded' => (int) $item->onboarded_count,
    //             ]
    //         ];
    //     });

    //     return [
    //         'total'            => $flattened,
    //         'major_activities' => $majorActivityCounts
    //     ];
    // }

    public function getOthersOnboardedAndMsmeChoosenMeCount($year = null, $fromDate = null, $toDate = null)
    {
        /* $dateFilter = '';
        $bindings = [];

        if ($year) {
            $dateFilter .= " AND YEAR(ms.created_at) = ? ";
            $bindings[] = $year;
        }

        if ($fromDate && $toDate) {
            $dateFilter .= " AND DATE(ms.created_at) BETWEEN ? AND ? ";
            $bindings[] = $fromDate;
            $bindings[] = $toDate;
        }

        $result = \DB::select("
            SELECT 
                (SELECT COUNT(*) FROM team_msme_schemes ms WHERE ms.select_snp = 0 {$dateFilter}) AS option1,
                (SELECT COUNT(*) FROM team_msme_schemes ms WHERE ms.select_snp = 1 {$dateFilter}) AS option2,
                (SELECT COUNT(*) 
                FROM team_msme_schemes ms
                INNER JOIN team_snpmsme_mapping tsm ON tsm.msme_id = ms.id
                WHERE tsm.status = 1 {$dateFilter}
                ) AS onboarded
        ", array_merge($bindings, $bindings, $bindings));

        $flattened = isset($result[0]) ? (array) $result[0] : [];

        $majorActivity = \DB::select("
            SELECT 
                ms.major_activity,

                COUNT(CASE WHEN ms.select_snp = 0 THEN 1 END) AS option1_count,
                COUNT(CASE WHEN ms.select_snp = 1 THEN 1 END) AS option2_count,

                (
                    SELECT COUNT(*) 
                    FROM team_snpmsme_mapping tsm
                    INNER JOIN team_msme_schemes ms2 ON ms2.id = tsm.msme_id
                    WHERE tsm.status = 1 
                    AND ms2.major_activity = ms.major_activity
                    {$dateFilter}
                ) AS onboarded_count

            FROM team_msme_schemes ms
            WHERE 1=1 {$dateFilter}
            GROUP BY ms.major_activity
        ", array_merge($bindings, $bindings));

        $majorActivityCounts = collect($majorActivity)->mapWithKeys(function ($item) {
            return [
                $item->major_activity => [
                    'option1'   => (int) $item->option1_count,
                    'option2'   => (int) $item->option2_count,
                    'onboarded' => (int) $item->onboarded_count,
                ]
            ];
        });

        return [
            'total'            => $flattened,
            'major_activities' => $majorActivityCounts
        ];*/

        $dateFilterMain = '';
        $dateFilterSub = '';
        $dateBindingsMain = [];
        $dateBindingsSub = [];

        if (!empty($fromDate) && !empty($toDate)) {

            $start = Carbon::parse($fromDate)->startOfDay();
            $end = Carbon::parse($toDate)->endOfDay();

            $dateFilterMain = " AND ms.created_at BETWEEN ? AND ?";
            $dateFilterSub = " AND ms2.created_at BETWEEN ? AND ?";

            $dateBindingsMain = [$start, $end];
            $dateBindingsSub = [$start, $end];
        } elseif (!empty($year)) {

            $dateFilterMain = " AND YEAR(ms.created_at) = ?";
            $dateFilterSub = " AND YEAR(ms2.created_at) = ?";

            $dateBindingsMain = [(int) $year];
            $dateBindingsSub = [(int) $year];
        }

        /*
|--------------------------------------------------------------------------
| TOTAL COUNTS
|--------------------------------------------------------------------------
*/
        $result = DB::select("
    SELECT 
        (
            SELECT COUNT(*)
            FROM team_msme_schemes ms
            WHERE ms.select_snp = 0 {$dateFilterMain}
        ) AS option1,

        (
            SELECT COUNT(*)
            FROM team_msme_schemes ms
            WHERE ms.select_snp = 1 {$dateFilterMain}
        ) AS option2,

        (
            SELECT COUNT(*)
            FROM team_snpmsme_mapping tsm
            INNER JOIN team_msme_schemes ms ON ms.id = tsm.msme_id
            WHERE tsm.status = 1 {$dateFilterMain}
        ) AS onboarded
", array_merge(
            $dateBindingsMain,
            $dateBindingsMain,
            $dateBindingsMain
        ));

        $total = isset($result[0])
            ? (array) $result[0]
            : ['option1' => 0, 'option2' => 0, 'onboarded' => 0];

        /*
|--------------------------------------------------------------------------
| MAJOR ACTIVITY WISE COUNTS (NO "OTHERS")
|--------------------------------------------------------------------------
*/
        $majorActivity = DB::select("
    SELECT 
        ms.major_activity,

        SUM(CASE WHEN ms.select_snp = 0 THEN 1 ELSE 0 END) AS option1_count,
        SUM(CASE WHEN ms.select_snp = 1 THEN 1 ELSE 0 END) AS option2_count,

        (
            SELECT COUNT(*)
            FROM team_snpmsme_mapping tsm
            INNER JOIN team_msme_schemes ms2 ON ms2.id = tsm.msme_id
            WHERE tsm.status = 1
              AND ms2.major_activity = ms.major_activity
              {$dateFilterSub}
        ) AS onboarded_count

    FROM team_msme_schemes ms
    WHERE 1=1 {$dateFilterMain}
      AND ms.major_activity IS NOT NULL
      AND ms.major_activity != ''
    GROUP BY ms.major_activity
", array_merge(
            $dateBindingsMain,
            $dateBindingsSub
        ));

        /*
|--------------------------------------------------------------------------
| FORMAT RESULT
|--------------------------------------------------------------------------
*/
        $majorActivityCounts = collect($majorActivity)->mapWithKeys(function ($item) {
            return [
                $item->major_activity => [
                    'option1' => (int) $item->option1_count,
                    'option2' => (int) $item->option2_count,
                    'onboarded' => (int) $item->onboarded_count,
                ],
            ];
        });

        /*
|--------------------------------------------------------------------------
| FINAL RESPONSE
|--------------------------------------------------------------------------
*/
        return [
            'total' => $total,
            'major_activities' => $majorActivityCounts,
        ];
    }

    public function getClaimCountById($year = null, $fromDate = null, $toDate = null)
    {
        $authId = (string) AuthId();

        $query = DB::table('claims as cs')
            ->leftJoin('claim_types as ct', 'ct.id', '=', 'cs.claim_type_id')
            ->where('cs.created_by', $authId);

        /*
     * Date filter
     */
        if (!empty($fromDate) && !empty($toDate)) {
            $query->whereBetween('cs.created_at', [
                Carbon::parse($fromDate)->startOfDay(),
                Carbon::parse($toDate)->endOfDay(),
            ]);
        } else {
            $query->whereYear('cs.created_at', $year ?? now()->year);
        }

        return (array) $query
            ->selectRaw('
            COUNT(DISTINCT CASE
                WHEN cs.claim_status IS NOT NULL 
                THEN cs.id
            END) AS totalClaims,

            COUNT(DISTINCT CASE
                WHEN ct.slug = ?
                THEN cs.id
            END) AS catalogueClaim,

            COUNT(DISTINCT CASE
                WHEN ct.slug = ?
                THEN cs.id
            END) AS accountClaim,

            COUNT(DISTINCT CASE
                WHEN ct.slug = ?
                THEN cs.id
            END) AS logisticClaim,

            COUNT(DISTINCT CASE
                WHEN cs.claim_status = 17
                THEN cs.id
            END) AS approvedCount,

            COUNT(DISTINCT CASE
                WHEN cs.claim_status IN (6, 9, 18) 
                THEN cs.id
            END) AS rejectedCount,

            COUNT(DISTINCT CASE
                WHEN cs.claim_status = 5
                THEN cs.id
            END) AS revertedCount,

            COUNT(DISTINCT CASE
                WHEN cs.claim_status = 0
                THEN cs.id
            END) AS draftCount,

            COUNT(DISTINCT CASE
                WHEN cs.claim_status = 19
                THEN cs.id
            END) AS paymentCompletedCount,

            COUNT(DISTINCT CASE
                WHEN cs.claim_status IN (2,3,4,7,8,10,11,12,13,14,15,16)
                THEN cs.id
            END) AS pendingCount
        ', [
                'claim-for-catalogue-creation',
                'claim-for-accounts-management',
                'claim-for-transportation-and-logistic',
            ])
            ->first() ?? [];
    }


    // public function getSnpCount()
    // {
    //     $authId = (string) AuthId();

    //     $result = \DB::select('
    //         SELECT 
    //             (
    //                 SELECT COUNT(DISTINCT snp.id)
    //                 FROM team_snp_scheme snp
    //             ) AS totalSnp,
    //             (
    //                 SELECT COUNT(DISTINCT snp.id)
    //                 FROM team_snp_scheme snp
    //                 WHERE snp.status = 0 
    //             ) AS pendingSnp,
    //             (
    //                 SELECT COUNT(DISTINCT snp.id)
    //                 FROM team_snp_scheme snp
    //                 WHERE snp.status = 1 
    //             ) AS approvedSnp,
    //             (
    //                 SELECT COUNT(DISTINCT snp.id)
    //                 FROM team_snp_scheme snp
    //                 WHERE snp.status = 4 
    //             ) AS rejectedSnp,
    //             (
    //                 SELECT COUNT(DISTINCT snp.id)
    //                 FROM team_snp_scheme snp
    //                 WHERE snp.review_status = 5 AND snp.status = 0 
    //             ) AS revertedSnp

    //     '
    //     );

    //     return isset($result[0]) ? (array) $result[0] : [];
    // }

    public function getSnpCount($year = null, $fromDate = null, $toDate = null)
    {
        /*$authId = (string) AuthId();

        $dateFilter = '';
        $bindings = [];

        if ($year) {
            $dateFilter .= " AND YEAR(snp.created_at) = ? ";
            $bindings[] = $year;
        }

        if ($fromDate && $toDate) {
            $dateFilter .= " AND DATE(snp.created_at) BETWEEN ? AND ? ";
            $bindings[] = $fromDate;
            $bindings[] = $toDate;
        }

        $result = \DB::select("
            SELECT 
                (
                    SELECT COUNT(DISTINCT snp.id)
                    FROM team_snp_scheme snp
                    WHERE 1=1 {$dateFilter}
                ) AS totalSnp,
                (
                    SELECT COUNT(DISTINCT snp.id)
                    FROM team_snp_scheme snp
                    WHERE snp.status = 0 {$dateFilter}
                ) AS pendingSnp,
                (
                    SELECT COUNT(DISTINCT snp.id)
                    FROM team_snp_scheme snp
                    WHERE snp.status = 1 {$dateFilter}
                ) AS approvedSnp,
                (
                    SELECT COUNT(DISTINCT snp.id)
                    FROM team_snp_scheme snp
                    WHERE snp.status = 4 {$dateFilter}
                ) AS rejectedSnp,
                (
                    SELECT COUNT(DISTINCT snp.id)
                    FROM team_snp_scheme snp
                    WHERE snp.review_status = 5 AND snp.status = 0 {$dateFilter}
                ) AS revertedSnp
        ", array_merge($bindings, $bindings, $bindings, $bindings, $bindings));

        return isset($result[0]) ? (array) $result[0] : [];*/

        $dateFilter = '';
        $dateBindings = [];

        if (!empty($fromDate) && !empty($toDate)) {

            $dateFilter = " AND snp.created_at BETWEEN ? AND ?";
            $dateBindings[] = Carbon::parse($fromDate)->startOfDay();
            $dateBindings[] = Carbon::parse($toDate)->endOfDay();
        } elseif (!empty($year)) {

            $dateFilter = " AND YEAR(snp.created_at) = ?";
            $dateBindings[] = (int) $year;
        }

        /*
|--------------------------------------------------------------------------
| Single optimized query (NO subqueries)
|--------------------------------------------------------------------------
*/
        $result = DB::select("
    SELECT
        COUNT(DISTINCT snp.id) AS totalSnp,
        SUM(CASE WHEN snp.status = 0 THEN 1 ELSE 0 END) AS pendingSnp,
        SUM(CASE WHEN snp.status = 1 THEN 1 ELSE 0 END) AS approvedSnp,
        SUM(CASE WHEN snp.status = 4 THEN 1 ELSE 0 END) AS rejectedSnp,
        SUM(CASE WHEN snp.review_status = 5 AND snp.status = 0 THEN 1 ELSE 0 END) AS revertedSnp
    FROM team_snp_scheme snp
    WHERE 1 = 1
    {$dateFilter}
", $dateBindings);

        /*
|--------------------------------------------------------------------------
| Response
|--------------------------------------------------------------------------
*/
        return isset($result[0]) ? (array) $result[0] : [];
    }


    // public function getBnpCount()
    // {
    //     $authId = (string) AuthId();

    //     $result = \DB::select('
    //         SELECT 
    //             (
    //                 SELECT COUNT(DISTINCT bnp.id)
    //                 FROM team_bnp_scheme bnp
    //             ) AS totalBnp,
    //             (
    //                 SELECT COUNT(DISTINCT bnp.id)
    //                 FROM team_bnp_scheme bnp
    //                 WHERE bnp.status = 0 
    //             ) AS pendingBnp,
    //             (
    //                 SELECT COUNT(DISTINCT bnp.id)
    //                 FROM team_bnp_scheme bnp
    //                 WHERE bnp.status = 1 
    //             ) AS approvedBnp,
    //             (
    //                 SELECT COUNT(DISTINCT bnp.id)
    //                 FROM team_bnp_scheme bnp
    //                 WHERE bnp.status = 4 
    //             ) AS rejectedBnp,
    //             (
    //                 SELECT COUNT(DISTINCT bnp.id)
    //                 FROM team_bnp_scheme bnp
    //                 WHERE bnp.review_status = 5 AND bnp.status = 0 
    //             ) AS revertedBnp

    //     '
    //     );

    //     return isset($result[0]) ? (array) $result[0] : [];
    // }

    public function getBnpCount($year = null, $fromDate = null, $toDate = null)
    {
        /* $authId = (string) AuthId();

        $dateFilter = '';
        $bindings = [];

        if ($year) {
            $dateFilter .= " AND YEAR(bnp.created_at) = ? ";
            $bindings[] = $year;
        }

        if ($fromDate && $toDate) {
            $dateFilter .= " AND DATE(bnp.created_at) BETWEEN ? AND ? ";
            $bindings[] = $fromDate;
            $bindings[] = $toDate;
        }

        $result = \DB::select("
            SELECT 
                (
                    SELECT COUNT(DISTINCT bnp.id)
                    FROM team_bnp_scheme bnp
                    WHERE 1=1 {$dateFilter}
                ) AS totalBnp,
                (
                    SELECT COUNT(DISTINCT bnp.id)
                    FROM team_bnp_scheme bnp
                    WHERE bnp.status = 0 {$dateFilter}
                ) AS pendingBnp,
                (
                    SELECT COUNT(DISTINCT bnp.id)
                    FROM team_bnp_scheme bnp
                    WHERE bnp.status = 1 {$dateFilter}
                ) AS approvedBnp,
                (
                    SELECT COUNT(DISTINCT bnp.id)
                    FROM team_bnp_scheme bnp
                    WHERE bnp.status = 4 {$dateFilter}
                ) AS rejectedBnp,
                (
                    SELECT COUNT(DISTINCT bnp.id)
                    FROM team_bnp_scheme bnp
                    WHERE bnp.review_status = 5 AND bnp.status = 0 {$dateFilter}
                ) AS revertedBnp
        ", array_merge($bindings, $bindings, $bindings, $bindings, $bindings));

        return isset($result[0]) ? (array) $result[0] : [];*/

        $authId = (string) AuthId();

        $dateFilter = '';
        $bindings = [];

        if ($year) {
            $dateFilter .= " AND YEAR(bnp.created_at) = ? ";
            $bindings[] = $year;
        }

        if ($fromDate && $toDate) {
            $dateFilter .= " AND DATE(bnp.created_at) BETWEEN ? AND ? ";
            $bindings[] = $fromDate;
            $bindings[] = $toDate;
        }

        $result = \DB::select("
            SELECT 
                (
                    SELECT COUNT(DISTINCT bnp.id)
                    FROM team_bnp_scheme bnp
                    WHERE 1=1 {$dateFilter}
                ) AS totalBnp,
                (
                    SELECT COUNT(DISTINCT bnp.id)
                    FROM team_bnp_scheme bnp
                    WHERE bnp.status = 0 {$dateFilter}
                ) AS pendingBnp,
                (
                    SELECT COUNT(DISTINCT bnp.id)
                    FROM team_bnp_scheme bnp
                    WHERE bnp.status = 1 {$dateFilter}
                ) AS approvedBnp,
                (
                    SELECT COUNT(DISTINCT bnp.id)
                    FROM team_bnp_scheme bnp
                    WHERE bnp.status = 4 {$dateFilter}
                ) AS rejectedBnp,
                (
                    SELECT COUNT(DISTINCT bnp.id)
                    FROM team_bnp_scheme bnp
                    WHERE bnp.review_status = 5 AND bnp.status = 0 {$dateFilter}
                ) AS revertedBnp
        ", array_merge($bindings, $bindings, $bindings, $bindings, $bindings));

        return isset($result[0]) ? (array) $result[0] : [];
    }


    // public function getOtherClaimCountById($status, $sentStatus)
    // {
    //     // ✅ Whitelist allowed columns to prevent SQL injection
    //     $allowedColumns = ['ondc_review_status', 'nsic_review_status', 'nsicfinance_review_status','status','ca_review_status'];
    //     if (!in_array($status, $allowedColumns)) {
    //         throw new \InvalidArgumentException("Invalid column name: " . $status);
    //     }

    //     $isSentallowedColumns = ['is_sent_ondc', 'is_sent_nsic', 'is_sent_nsicfinance','is_sent_ca'];
    //     if (!in_array($sentStatus, $isSentallowedColumns)) {
    //         throw new \InvalidArgumentException("Invalid column name: " . $sentStatus);
    //     }

    //     // ✅ Build SQL dynamically with column name
    //     $column = "cs." . $status;
    //     $sentColumn = "cs." . $sentStatus;

    //     $result = \DB::select("
    //         SELECT 
    //             (
    //                 SELECT COUNT(DISTINCT cs.id)
    //                 FROM claims cs
    //                 WHERE {$sentColumn} = 1
    //             ) AS totalClaims,
    //             (
    //                 SELECT COUNT(DISTINCT cs.id)
    //                 FROM claims cs
    //                 WHERE {$column} = 3
    //             ) AS approvedCount,
    //             (
    //                 SELECT COUNT(DISTINCT cs.id)
    //                 FROM claims cs
    //                 WHERE {$column} = 4
    //             ) AS rejectedCount,
    //             (
    //                 SELECT COUNT(DISTINCT cs.id)
    //                 FROM claims cs
    //                 WHERE {$column} = 5
    //             ) AS revertedCount,
    //             (
    //                 SELECT COUNT(DISTINCT cs.id)
    //                 FROM claims cs
    //                 WHERE {$column} = 7
    //             ) AS paymentCompletedCount,
    //             (
    //                 SELECT COUNT(DISTINCT cs.id)
    //                 FROM claims cs
    //                 WHERE {$column} IN (1, 2, 6)
    //             ) AS pendingCount
    //     ");

    //     return isset($result[0]) ? (array) $result[0] : [];
    // }


    public function getOtherClaimCountById($status, $sentStatus, $year = null, $fromDate = null, $toDate = null)
    {
        /* $allowedColumns = ['ondc_review_status', 'nsic_review_status', 'nsicfinance_review_status', 'status', 'ca_review_status'];
        if (!in_array($status, $allowedColumns)) {
            throw new \InvalidArgumentException("Invalid column name: " . $status);
        }

        $isSentAllowedColumns = ['is_sent_ondc', 'is_sent_nsic', 'is_sent_nsicfinance', 'is_sent_ca'];
        if (!in_array($sentStatus, $isSentAllowedColumns)) {
            throw new \InvalidArgumentException("Invalid column name: " . $sentStatus);
        }

        $column = "cs." . $status;
        $sentColumn = "cs." . $sentStatus;

        $dateFilter = '';
        $bindings = [];

        if ($fromDate && $toDate) {
            $fromDateTime = Carbon::parse($fromDate)->startOfDay()->toDateTimeString();
            $toDateTime   = Carbon::parse($toDate)->endOfDay()->toDateTimeString();

            $dateFilter .= " AND cs.created_at BETWEEN ? AND ? ";
            $bindings[] = $fromDateTime;
            $bindings[] = $toDateTime;

            if ($year) {
                $dateFilter .= " AND YEAR(cs.created_at) = ? ";
                $bindings[] = $year;
            }
        } else {
            $yearToUse = $year ?? date('Y');
            $dateFilter .= " AND YEAR(cs.created_at) = ? ";
            $bindings[] = $yearToUse;
        }

        $result = \DB::select("
            SELECT 
                (
                    SELECT COUNT(DISTINCT cs.id)
                    FROM claims cs
                    WHERE {$sentColumn} = 1
                    {$dateFilter}
                ) AS totalClaims,
                (
                    SELECT COUNT(DISTINCT cs.id)
                    FROM claims cs
                    WHERE {$column} = 3
                    {$dateFilter}
                ) AS approvedCount,
                (
                    SELECT COUNT(DISTINCT cs.id)
                    FROM claims cs
                    WHERE {$column} = 4
                    {$dateFilter}
                ) AS rejectedCount,
                (
                    SELECT COUNT(DISTINCT cs.id)
                    FROM claims cs
                    WHERE {$column} = 5
                    {$dateFilter}
                ) AS revertedCount,
                (
                    SELECT COUNT(DISTINCT cs.id)
                    FROM claims cs
                    WHERE {$column} = 7
                    {$dateFilter}
                ) AS paymentCompletedCount,
                (
                    SELECT COUNT(DISTINCT cs.id)
                    FROM claims cs
                    WHERE {$column} IN (1, 2, 6)
                    {$dateFilter}
                ) AS pendingCount
        ", array_merge($bindings, $bindings, $bindings, $bindings, $bindings, $bindings));
        return isset($result[0]) ? (array) $result[0] : [];*/


        $allowedColumns = [
            'ondc_review_status',
            'nsic_review_status',
            'nsicfinance_review_status',
            'status',
            'ca_review_status'
        ];

        if (!in_array($status, $allowedColumns, true)) {
            throw new \InvalidArgumentException("Invalid column name: {$status}");
        }

        $isSentAllowedColumns = [
            'is_sent_ondc',
            'is_sent_nsic',
            'is_sent_nsicfinance',
            'is_sent_ca'
        ];

        if (!in_array($sentStatus, $isSentAllowedColumns, true)) {
            throw new \InvalidArgumentException("Invalid column name: {$sentStatus}");
        }

        $column = "cs.$status";
        $sentColumn = "cs.$sentStatus";

        /*
|--------------------------------------------------------------------------
| Date filter (either range OR year)
|--------------------------------------------------------------------------
*/
        $dateFilter = '';
        $dateBindings = [];

        if (!empty($fromDate) && !empty($toDate)) {

            $dateFilter = " AND cs.created_at BETWEEN ? AND ?";
            $dateBindings[] = Carbon::parse($fromDate)->startOfDay();
            $dateBindings[] = Carbon::parse($toDate)->endOfDay();
        } else {

            $dateFilter = " AND YEAR(cs.created_at) = ?";
            $dateBindings[] = $year ?? now()->year;
        }

        /*
|--------------------------------------------------------------------------
| Final Query
|--------------------------------------------------------------------------
*/
        $result = DB::select("
    SELECT 
        SUM(CASE WHEN {$sentColumn} = 1 THEN 1 ELSE 0 END) AS totalClaims,
        SUM(CASE WHEN {$column} = 3 THEN 1 ELSE 0 END) AS approvedCount,
        SUM(CASE WHEN {$column} = 4 THEN 1 ELSE 0 END) AS rejectedCount,
        SUM(CASE WHEN {$column} = 5 THEN 1 ELSE 0 END) AS revertedCount,
        SUM(CASE WHEN {$column} = 7 THEN 1 ELSE 0 END) AS paymentCompletedCount,
        SUM(CASE WHEN {$column} IN (1,2,6) THEN 1 ELSE 0 END) AS pendingCount
    FROM claims cs
    WHERE 1 = 1
    {$dateFilter}
", $dateBindings);

        /*
|--------------------------------------------------------------------------
| Response
|--------------------------------------------------------------------------
*/
        return isset($result[0]) ? (array) $result[0] : [];
    }






    /*public function getClaimCount()
    {
        $authId = (string) AuthId();
        $result = \DB::select('
            select 
                (
                    select count(*)
                    from claims cs
                    where cs.created_by = ?
                ) as totalClaims,
                (
                    select count(*)
                    from claims cs
                    inner join claim_types ct on cs.claim_type_id = ct.id
                    where cs.created_by = ? and ct.slug = ?
                ) as catalogueClaim,
                (
                    select count(*)
                    from claims cs
                    inner join claim_types ct on cs.claim_type_id = ct.id
                    where cs.created_by = ? and ct.slug = ?
                ) as accountClaim,
                (
                    select count(*)
                    from claims cs
                    inner join claim_types ct on cs.claim_type_id = ct.id
                    where cs.created_by = ? and ct.slug = ?
                ) as logisticClaim
        ', [$authId, $authId, 'claim-for-catalogue-creation', $authId, 'claim-for-accounts-management', $authId, 'claim-for-logistics-and-transportation']);

        // Flatten the first result object to associative array
        $flattened = isset($result[0]) ? (array) $result[0] : [];

        // Return flattened result
        return $flattened;

    }*/



    public function getCADashboardBatchSummary($year = null, $fromDate = null, $toDate = null)
    {
        /*$authId = (string) AuthId();

        $dateFilter = '';
        $bindings = [$authId];

        if ($year) {
            $dateFilter .= " AND YEAR(b.created_at) = ? ";
            $bindings[] = $year;
        }

        if ($fromDate && $toDate) {
            $dateFilter .= " AND DATE(b.created_at) BETWEEN ? AND ? ";
            $bindings[] = $fromDate;
            $bindings[] = $toDate;
        }

        $result = \DB::select("
            SELECT 
                SUM(CASE WHEN bw.status_id = 3 THEN 1 ELSE 0 END) AS approved,
                SUM(CASE WHEN bw.status_id = 6 THEN 1 ELSE 0 END) AS pending,
                SUM(CASE WHEN bw.status_id = 5 THEN 1 ELSE 0 END) AS reverted
            FROM batch_review_statuses bw
            LEFT JOIN batches b ON bw.batch_id = b.id
            WHERE bw.user_id = ? {$dateFilter}
        ", $bindings);

        $summary = [];
        if (!empty($result)) {
            $row = (array) $result[0];

            $approved = (int) $row['approved'];
            $pending  = (int) $row['pending'];
            $reverted = (int) $row['reverted'];

            $summary = [
                'approved' => $approved,
                'pending'  => $pending,
                'reverted' => $reverted,
                'total'    => $approved + $pending + $reverted
            ];
        } else {
            $summary = [
                'approved' => 0,
                'pending'  => 0,
                'reverted' => 0,
                'total'    => 0
            ];
        }

        return $summary;*/

        $userIds = getSubUserAndParentIds(authId());
        $userIdPlaceholders = implode(',', array_fill(0, count($userIds), '?'));

        /*
|--------------------------------------------------------------------------
| Date filter (either date range OR year)
|--------------------------------------------------------------------------
*/
        $dateFilter = '';
        $dateBindings = [];

        if (!empty($fromDate) && !empty($toDate)) {

            $dateFilter = " AND b.created_at BETWEEN ? AND ?";
            $dateBindings[] = Carbon::parse($fromDate)->startOfDay();
            $dateBindings[] = Carbon::parse($toDate)->endOfDay();
        } elseif (!empty($year)) {

            $dateFilter = " AND YEAR(b.created_at) = ?";
            $dateBindings[] = (int) $year;
        }

        /*
|--------------------------------------------------------------------------
| Query
|--------------------------------------------------------------------------
*/
        $result = DB::select("
    SELECT
        COALESCE(SUM(CASE WHEN bw.status_id = 3 THEN 1 ELSE 0 END), 0) AS approved,
        COALESCE(SUM(CASE WHEN bw.status_id = 6 THEN 1 ELSE 0 END), 0) AS pending,
        COALESCE(SUM(CASE WHEN bw.status_id = 5 THEN 1 ELSE 0 END), 0) AS reverted
    FROM batch_review_statuses bw
    LEFT JOIN batches b ON bw.batch_id = b.id
    WHERE bw.user_id IN ($userIdPlaceholders)
    {$dateFilter}
", array_merge($userIds, $dateBindings));

        /*
|--------------------------------------------------------------------------
| Response
|--------------------------------------------------------------------------
*/
        $row = isset($result[0]) ? (array) $result[0] : [
            'approved' => 0,
            'pending' => 0,
            'reverted' => 0,
        ];

        return [
            'approved' => (int) $row['approved'],
            'pending' => (int) $row['pending'],
            'reverted' => (int) $row['reverted'],
            'total' => (int) $row['approved'] + (int) $row['pending'] + (int) $row['reverted'],
        ];
    }

    public function getFundManagementMetricsData($year = null, $fromDate = null, $toDate = null, $type = null)
    {
        // 1. Resolve current and previous date ranges
        $currentFrom = null;
        $currentTo = null;
        $prevFrom = null;
        $prevTo = null;
        $prevYear = null;
        $financialYearStr = null;

        if (!empty($fromDate) && !empty($toDate)) {
            $currentFrom = Carbon::parse($fromDate)->format('Y-m-d');
            $currentTo = Carbon::parse($toDate)->format('Y-m-d');

            $days = Carbon::parse($currentFrom)->diffInDays(Carbon::parse($currentTo)) + 1;

            $prevFrom = Carbon::parse($currentFrom)->copy()->subDays($days)->format('Y-m-d');
            $prevTo = Carbon::parse($currentFrom)->copy()->subDay()->format('Y-m-d');
        } elseif (!empty($year)) {
            // Map 4-digit year to financial year string (e.g. 2026 -> 2026-2027)
            if (strlen((string)$year) === 4 && is_numeric($year)) {
                $financialYearStr = $year . '-' . ((int)$year + 1);
                $prevYear = ((int)$year - 1) . '-' . $year;
            } else {
                $financialYearStr = $year;
                if (preg_match('/^(\d{4})-(\d{4})$/', $year, $matches)) {
                    $start = (int)$matches[1] - 1;
                    $end = (int)$matches[2] - 1;
                    $prevYear = "{$start}-{$end}";
                }
            }
        } else {
            // No explicit filter selected -- default to the current financial year
            // instead of aggregating across every financial year ever recorded.
            $financialYearStr = \App\Utils\Calendar::getCurrentFinancialYear();
            if (preg_match('/^(\d{4})-(\d{4})$/', $financialYearStr, $matches)) {
                $prevYear = ((int)$matches[1] - 1) . '-' . ((int)$matches[2] - 1);
            }
        }

        // 2. Fetch Allocation Totals across FundAllocation & FundCarryForward
        // Current period Direct Allocations (FundAllocation)
        //
        // NOTE ON CARRY-FORWARD DOUBLE-COUNTING: fund_allocation_component_mappings.amount
        // is a per-period snapshot that is never reduced on the SOURCE period when its
        // remainder is carried away (only the pool is debited -- see
        // StoreFundAllocationAction.php, the "wash the pool" step) and, for a component-
        // keyed carry-forward, the SAME rupees are also merged straight into the
        // DESTINATION period's own mapping row (StoreFundAllocationAction.php:185-188).
        // So summing this column across every period in a financial year counts those
        // rupees twice (once on the source row, once on the destination row) for every
        // component-keyed carry-forward, and misses header-only "unallocated" carry-
        // forward entirely (it never becomes a mapping row at all). Both are corrected
        // below using fund_carry_forward_details, the ledger of every sweep: subtract the
        // component-keyed portion (already double-counted by the raw sum) and add back the
        // UNALLOCATED_COMPONENT-sentinel portion (never captured by the raw sum).
        $allocQuery = DB::table('fund_allocation_component_mappings as al')
            ->join('fund_allocations as ah', 'al.fund_allocation_id', '=', 'ah.id');
        if (!empty($fromDate) && !empty($toDate)) {
            $allocQuery->whereBetween('ah.sanction_order_date', [$currentFrom, $currentTo]);
        } elseif (!empty($financialYearStr)) {
            $allocQuery->where('ah.financial_year', $financialYearStr);
        }
        $currentRawMappedAllocated = (float) $allocQuery->sum('al.amount');

        $currentComponentKeyedCarryQuery = DB::table('fund_carry_forward_details as cfd')
            ->join('fund_carry_forwards as cf', 'cfd.carry_forward_id', '=', 'cf.id')
            ->where('cfd.major_component_id', '!=', \App\Domain\FundCarryForward\FundCarryForwardDetail::UNALLOCATED_COMPONENT);
        if (!empty($fromDate) && !empty($toDate)) {
            $currentComponentKeyedCarryQuery->whereBetween('cf.carry_forward_date', [$currentFrom, $currentTo]);
        } elseif (!empty($financialYearStr)) {
            $currentComponentKeyedCarryQuery->where('cf.financial_year', $financialYearStr);
        }
        $currentComponentKeyedCarry = (float) $currentComponentKeyedCarryQuery->sum('cfd.carried_forward_amount');

        $currentDirectAllocated = $currentRawMappedAllocated - $currentComponentKeyedCarry;

        // Current period Carry Forward Allocations (FundCarryForward)
        // Only the UNALLOCATED_COMPONENT sentinel rows belong here -- they are the only
        // carry-forward money NOT already reflected in $currentDirectAllocated above.
        $carryQuery = DB::table('fund_carry_forward_details as cfd')
            ->join('fund_carry_forwards as cf', 'cfd.carry_forward_id', '=', 'cf.id')
            ->where('cfd.major_component_id', \App\Domain\FundCarryForward\FundCarryForwardDetail::UNALLOCATED_COMPONENT);
        if (!empty($fromDate) && !empty($toDate)) {
            $carryQuery->whereBetween('cf.carry_forward_date', [$currentFrom, $currentTo]);
        } elseif (!empty($financialYearStr)) {
            $carryQuery->where('cf.financial_year', $financialYearStr);
        }
        $currentCarryAllocated = (float) $carryQuery->sum('cfd.carried_forward_amount');

        $currentAllocated = $currentDirectAllocated + $currentCarryAllocated;

        // Previous period Direct Allocations (FundAllocation) -- same source/destination
        // double-count correction as $currentDirectAllocated above.
        $prevAllocQuery = DB::table('fund_allocation_component_mappings as al')
            ->join('fund_allocations as ah', 'al.fund_allocation_id', '=', 'ah.id');
        if (!empty($fromDate) && !empty($toDate)) {
            $prevAllocQuery->whereBetween('ah.sanction_order_date', [$prevFrom, $prevTo]);
        } elseif (!empty($prevYear)) {
            $prevAllocQuery->where('ah.financial_year', $prevYear);
        } else {
            $prevAllocQuery->whereRaw('1 = 0'); // No comparative year
        }
        $prevRawMappedAllocated = (float) $prevAllocQuery->sum('al.amount');

        $prevComponentKeyedCarryQuery = DB::table('fund_carry_forward_details as cfd')
            ->join('fund_carry_forwards as cf', 'cfd.carry_forward_id', '=', 'cf.id')
            ->where('cfd.major_component_id', '!=', \App\Domain\FundCarryForward\FundCarryForwardDetail::UNALLOCATED_COMPONENT);
        if (!empty($fromDate) && !empty($toDate)) {
            $prevComponentKeyedCarryQuery->whereBetween('cf.carry_forward_date', [$prevFrom, $prevTo]);
        } elseif (!empty($prevYear)) {
            $prevComponentKeyedCarryQuery->where('cf.financial_year', $prevYear);
        } else {
            $prevComponentKeyedCarryQuery->whereRaw('1 = 0');
        }
        $prevComponentKeyedCarry = (float) $prevComponentKeyedCarryQuery->sum('cfd.carried_forward_amount');

        $prevDirectAllocated = $prevRawMappedAllocated - $prevComponentKeyedCarry;

        // Previous period Carry Forward Allocations (FundCarryForward) -- same
        // UNALLOCATED_COMPONENT-only restriction as $currentCarryAllocated above.
        $prevCarryQuery = DB::table('fund_carry_forward_details as cfd')
            ->join('fund_carry_forwards as cf', 'cfd.carry_forward_id', '=', 'cf.id')
            ->where('cfd.major_component_id', \App\Domain\FundCarryForward\FundCarryForwardDetail::UNALLOCATED_COMPONENT);
        if (!empty($fromDate) && !empty($toDate)) {
            $prevCarryQuery->whereBetween('cf.carry_forward_date', [$prevFrom, $prevTo]);
        } elseif (!empty($prevYear)) {
            $prevCarryQuery->where('cf.financial_year', $prevYear);
        } else {
            $prevCarryQuery->whereRaw('1 = 0');
        }
        $prevCarryAllocated = (float) $prevCarryQuery->sum('cfd.carried_forward_amount');

        $prevAllocated = $prevDirectAllocated + $prevCarryAllocated;

        // 3. Fetch Distribution Totals (FundDistribution)
        // Current period Distribution
        $distQuery = DB::table('fund_distributions')
            ->whereNull('deleted_at');
        if (!empty($fromDate) && !empty($toDate)) {
            $distQuery->where(function ($q) use ($currentFrom, $currentTo) {
                $q->whereBetween('sanction_order_date', [$currentFrom, $currentTo])
                    ->orWhere(function ($sub) use ($currentFrom, $currentTo) {
                        $sub->whereNull('sanction_order_date')
                            ->whereBetween('created_at', [$currentFrom . ' 00:00:00', $currentTo . ' 23:59:59']);
                    });
            });
        } elseif (!empty($financialYearStr)) {
            $distQuery->where('financial_year', $financialYearStr);
        }
        $currentDistributed = (float) $distQuery->sum('distribution_amount');

        // Previous period Distribution
        $prevDistQuery = DB::table('fund_distributions')
            ->whereNull('deleted_at');
        if (!empty($fromDate) && !empty($toDate)) {
            $prevDistQuery->where(function ($q) use ($prevFrom, $prevTo) {
                $q->whereBetween('sanction_order_date', [$prevFrom, $prevTo])
                    ->orWhere(function ($sub) use ($prevFrom, $prevTo) {
                        $sub->whereNull('sanction_order_date')
                            ->whereBetween('created_at', [$prevFrom . ' 00:00:00', $prevTo . ' 23:59:59']);
                    });
            });
        } elseif (!empty($prevYear)) {
            $prevDistQuery->where('financial_year', $prevYear);
        } else {
            $prevDistQuery->whereRaw('1 = 0');
        }
        $prevDistributed = (float) $prevDistQuery->sum('distribution_amount');

        // 4. Calculate Derived Metrics
        $currentRemaining = $currentAllocated - $currentDistributed;
        $prevRemaining = $prevAllocated - $prevDistributed;

        // Utilization Percentage
        $utilizationPercent = 0.0;
        if ($currentAllocated > 0) {
            $utilizationPercent = ($currentDistributed / $currentAllocated) * 100;
        }

        // 5. Calculate Trend Changes
        $allocatedTrend = $this->calculateTrendPercentage($currentAllocated, $prevAllocated);
        $distributedTrend = $this->calculateTrendPercentage($currentDistributed, $prevDistributed);
        $remainingTrend = $this->calculateTrendPercentage($currentRemaining, $prevRemaining);

        // 6. Format Prev Period String
        $prevPeriodString = 'vs previous period';
        if (!empty($fromDate) && !empty($toDate)) {
            $formattedPrevFrom = Carbon::parse($prevFrom)->format('d-m-Y');
            $formattedPrevTo = Carbon::parse($prevTo)->format('d-m-Y');
            $prevPeriodString = "vs prev period ({$formattedPrevFrom} to {$formattedPrevTo})";
        } elseif (!empty($financialYearStr) && !empty($prevYear)) {
            $prevPeriodString = "vs prev FY ({$prevYear})";
        }

        return [
            'allocated' => [
                'current' => $currentAllocated,
                'previous' => $prevAllocated,
                'trend' => $allocatedTrend['value'],
                'direction' => $allocatedTrend['direction']
            ],
            'distributed' => [
                'current' => $currentDistributed,
                'previous' => $prevDistributed,
                'trend' => $distributedTrend['value'],
                'direction' => $distributedTrend['direction']
            ],
            'remaining' => [
                'current' => $currentRemaining,
                'previous' => $prevRemaining,
                'trend' => $remainingTrend['value'],
                'direction' => $remainingTrend['direction']
            ],
            'utilization_percent' => round($utilizationPercent, 2),
            'prev_period_string' => $prevPeriodString,
            'financial_year_label' => $financialYearStr,
        ];
    }

    private function calculateTrendPercentage(float $current, float $prev): array
    {
        if ($prev == 0) {
            if ($current > 0) {
                return ['value' => 100.0, 'direction' => 'up'];
            }
            return ['value' => 0.0, 'direction' => 'neutral'];
        }

        $change = (($current - $prev) / $prev) * 100;

        if ($change > 0) {
            return ['value' => round($change, 2), 'direction' => 'up'];
        } elseif ($change < 0) {
            return ['value' => round(abs($change), 2), 'direction' => 'down'];
        }
        return ['value' => 0.0, 'direction' => 'neutral'];
    }
}
