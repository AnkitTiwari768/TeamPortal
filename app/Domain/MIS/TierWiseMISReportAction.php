<?php

declare(strict_types=1);

namespace App\Domain\MIS;

use Illuminate\Support\Facades\DB;

class TierWiseMISReportAction
{
    public function execute(?string $tier = null)
    {
        $query = DB::table('locations as l')
            ->leftJoin('team_msme_schemes as ms', 'ms.district_id', '=', 'l.id')
            ->selectRaw("
                COALESCE(l.tier,'Tier-2/3') AS tier,

                COUNT(DISTINCT ms.id) AS total_msme,

                SUM(
                    CASE
                        WHEN ms.select_snp = 0
                             AND ms.bpp_id IS NULL
                        THEN 1 ELSE 0
                    END
                ) AS open_msme,

                SUM(
                    CASE
                        WHEN ms.select_snp = 1
                             AND ms.bpp_id IS NULL
                        THEN 1 ELSE 0
                    END
                ) AS direct_selection,

                SUM(
                    CASE
                        WHEN ms.bpp_id IS NOT NULL
                        THEN 1 ELSE 0
                    END
                ) AS onboarded_msme,

                SUM(
                    CASE
                        WHEN LOWER(TRIM(ms.gender)) = 'female'
                        THEN 1 ELSE 0
                    END
                ) AS total_women,

                SUM(
                    CASE
                        WHEN LOWER(TRIM(ms.social_category)) = 'sc'
                        THEN 1 ELSE 0
                    END
                ) AS sc_count,

                SUM(
                    CASE
                        WHEN LOWER(TRIM(ms.social_category)) = 'st'
                        THEN 1 ELSE 0
                    END
                ) AS st_count,

                SUM(
                    CASE
                        WHEN LOWER(TRIM(ms.social_category)) = 'obc'
                        THEN 1 ELSE 0
                    END
                ) AS obc_count,

                SUM(
                    CASE
                        WHEN LOWER(TRIM(ms.social_category)) IN ('general','gen')
                        THEN 1 ELSE 0
                    END
                ) AS general_count
            ")
            ->whereNotNull('ms.major_activity')
            ->where('ms.major_activity', '<>', '');

        // Tier Filter
        if (!empty($tier)) {

            if ($tier === 'tier1') {
                $query->where('l.tier', 'Tier-1');
            }

            if ($tier === 'tier2_3') {
                $query->where('l.tier', 'Tier-2/3');
            }
        }

        return $query
            ->groupBy(DB::raw("COALESCE(l.tier,'Tier-2/3')"))
            ->orderBy('tier')
            ->get();
    }
}