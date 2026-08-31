<?php

namespace App\Web\RootManager\Common;

use Illuminate\Support\Facades\DB;

class SnpMsmeWiseService
{
    /**
     * Get SNP wise MSME data - Without JSON_OVERLAPS
     */
    public function getSnpMsmeWiseData()
    {
        $query = "
            SELECT 
                tss.snp_id,
                tss.organization_id,
                tss.organization_name,
                (
                    SELECT COUNT(DISTINCT ms.id)
                    FROM team_msme_schemes ms
                    WHERE ms.select_snp = 0
                      AND ms.bpp_id IS NULL
                      AND ms.major_activity IS NOT NULL
                      AND ms.major_activity != ''
                      AND (
                            JSON_CONTAINS(tss.state_id, '\"5bc85de0-0292-11f1-922a-00155d022d06\"')
                            OR JSON_CONTAINS(tss.state_id, JSON_QUOTE(ms.state_id))
                      )
                      AND JSON_CONTAINS(tss.transaction_type, JSON_QUOTE(ms.ondc_transaction_type_id))
                ) AS open_msme,
                (
                    SELECT COUNT(DISTINCT ms2.id)
                    FROM team_msme_schemes ms2
                    INNER JOIN team_snpmsme_mapping tsm2 ON tsm2.msme_id = ms2.id
                    WHERE tsm2.snp_id = tss.id
                      AND ms2.select_snp = 1
                      AND tsm2.status = 0
                      AND ms2.major_activity IS NOT NULL
                      AND ms2.major_activity != ''
                ) AS direct_selection_by_mse,
                (
                    SELECT COUNT(*)
                    FROM team_msme_schemes ms3
                    INNER JOIN team_snpmsme_mapping tsm3 ON tsm3.msme_id = ms3.id
                    INNER JOIN team_snp_scheme tss3 ON tsm3.snp_id = tss3.id
                    WHERE tss3.snp_id = tss.snp_id
                      AND tsm3.status = 1
                      AND ms3.major_activity IS NOT NULL
                      AND ms3.major_activity != ''
                ) AS onboarded_msme
            FROM team_snp_scheme tss
            WHERE tss.snp_id IS NOT NULL
            ORDER BY tss.organization_name ASC
        ";
        
        return DB::select($query);
    }
}