<?php

declare(strict_types=1);

namespace App\Domain\MIS;

use Illuminate\Support\Facades\DB;

final class StateWiseMISReportAction
{
    public function execute(): array
    {
        $query = <<<SQL
        SELECT 
            s.name AS state_name,
            COALESCE((
                SELECT COUNT(DISTINCT ms.id)
                FROM team_msme_schemes ms
                WHERE ms.state_id = s.id
                  AND ms.select_snp = 0
                  AND ms.bpp_id IS NULL
                  AND ms.major_activity IS NOT NULL
                  AND ms.major_activity != ''
            ), 0) AS open_msme,
            
            COALESCE((
                SELECT COUNT(DISTINCT ms.id)
                FROM team_msme_schemes ms
                INNER JOIN team_snpmsme_mapping tsm 
                    ON tsm.msme_id = ms.id
                WHERE ms.state_id = s.id
                  AND ms.select_snp = 1
                  AND tsm.status = 0
                  AND ms.major_activity IS NOT NULL
                  AND ms.major_activity != ''
            ), 0) AS direct_selection_msme,
            
            COALESCE((
                SELECT COUNT(DISTINCT ms.id)
                FROM team_msme_schemes ms
                INNER JOIN team_snpmsme_mapping tsm 
                    ON tsm.msme_id = ms.id
                WHERE ms.state_id = s.id
                  AND tsm.status = 1
                  AND ms.major_activity IS NOT NULL
                  AND ms.major_activity != ''
            ), 0) AS onboarded_msme

        FROM states s
        ORDER BY s.name ASC;
        SQL;

        return DB::select($query);
    }
}