<?php

namespace App\Web\RootManager\Common;

use Illuminate\Support\Facades\DB;

class StateWiseCountService
{
    /**
     * Get State Wise MSME Counts
     */
    public function getStateWiseMsmeData()
    {
        $query = "
            SELECT 
                s.name AS state_name,

                -- Open MSME
                COUNT(
                    DISTINCT CASE 
                        WHEN ms.select_snp = 0
                             AND ms.bpp_id IS NULL
                             AND ms.major_activity IS NOT NULL
                             AND ms.major_activity != ''
                        THEN ms.id
                    END
                ) AS open_msme,

                -- Selected MSME
                COUNT(
                    DISTINCT CASE 
                        WHEN ms.select_snp = 1
                             AND tsm.status = 0
                             AND ms.major_activity IS NOT NULL
                             AND ms.major_activity != ''
                        THEN ms.id
                    END
                ) AS selected_msme,

                -- Onboarded MSME
                COUNT(
                    DISTINCT CASE 
                        WHEN tsm.status = 1
                             AND ms.major_activity IS NOT NULL
                             AND ms.major_activity != ''
                        THEN ms.id
                    END
                ) AS onboarded_msme

            FROM team_msme_schemes ms

            -- State Table Join
            LEFT JOIN states s 
                ON s.id = ms.state_id

            -- Mapping Table Join
            LEFT JOIN team_snpmsme_mapping tsm 
                ON tsm.msme_id = ms.id

            WHERE ms.state_id IS NOT NULL

            GROUP BY s.name

            ORDER BY s.name ASC
        ";

        return DB::select($query);
    }
}