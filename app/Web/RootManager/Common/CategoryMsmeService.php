<?php

namespace App\Web\RootManager\Common;

use Illuminate\Support\Facades\DB;

class CategoryMsmeService
{
    /**
     * Get Category Wise MSME Counts
     */
    public function getCategoryWiseMsmeData()
    {
        $query = "
            SELECT 
                sd.name AS product_category_name,

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

            FROM sub_domains sd

            LEFT JOIN team_msme_schemes ms
                ON FIND_IN_SET(
                    sd.id,
                    REPLACE(
                        REPLACE(
                            REPLACE(ms.product_category_id, '[', ''),
                        ']', ''),
                    '\"', '')
                )

            LEFT JOIN team_snpmsme_mapping tsm 
                ON tsm.msme_id = ms.id

            WHERE ms.product_category_id IS NOT NULL
              AND ms.product_category_id != ''

            GROUP BY sd.name

            ORDER BY sd.name ASC
        ";

        return DB::select($query);
    }
}