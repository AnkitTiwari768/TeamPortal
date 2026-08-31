<?php

namespace App\Web\Common;

use Illuminate\Support\Facades\DB;

class StateCountService
{
    /**
     * Get State Wise MSME Counts
     */
    public function getStateMsmeData()
{
    $query = "
        SELECT 
            state_name,
            COUNT(*) AS count
        FROM (
            SELECT 
                ms.id,
                COALESCE(st.name, 'Unknown') AS state_name
            FROM team_msme_schemes ms
            LEFT JOIN states st ON st.id = ms.state_id
            WHERE ms.major_activity IS NOT NULL
              AND LENGTH(TRIM(ms.major_activity)) > 0
        ) AS data
        GROUP BY state_name
        ORDER BY state_name ASC
    ";

    return DB::select($query);
}
}