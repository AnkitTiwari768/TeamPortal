<?php 
declare(strict_types=1);
namespace App\Web\Dashboard;
use App\Core\BaseService;
use App\Web\SNP\SNPMSMEService;
use DB;

class DashboardService extends BaseService
{
    public function getSnpMyMsmeCount()
    {
        $snp = app(SnpMSMEService::class)->getSnpDetail(AuthId());
        $stateIds       = json_decode($snp->state_id, true) ?: [$snp->state_id];
        $txnTypeIds     = json_decode($snp->transaction_type, true) ?: [$snp->transaction_type];
        $subDomainIds   = json_decode($snp->sub_domain, true) ?: [$snp->sub_domain];

        // base query for reuse
        $baseQuery = DB::table('team_msme_schemes as ms')
            ->where('ms.select_snp', 0)
            ->whereIn('ms.state_id', $stateIds)
            ->whereIn('ms.ondc_transaction_type_id', $txnTypeIds)
            ->where(function ($q) use ($subDomainIds) {
                foreach ($subDomainIds as $sd) {
                    $q->orWhereRaw('JSON_CONTAINS(ms.product_category_id, ?)', ['"'.$sd.'"']);
                }
            });

        // 1️⃣ Total count
        $totalCount = (clone $baseQuery)->count();

        // 2️⃣ Major activity counts
        $majorActivityCounts = (clone $baseQuery)
            ->select('ms.major_activity', DB::raw('COUNT(*) as total_count'))
            ->groupBy('ms.major_activity')
            ->pluck('total_count', 'ms.major_activity'); // returns key => value

        return [
            'total_msme'        => $totalCount,
            'major_activities'  => $majorActivityCounts
        ];
    }


    public function getSnpOnboardedAndMsmeChoosenMeCount()
    {
        $result = \DB::select('
            SELECT 
                -- total counts
                (SELECT COUNT(*)
                FROM team_msme_schemes ms
                INNER JOIN team_snpmsme_mapping tsm ON tsm.msme_id = ms.id
                INNER JOIN team_snp_scheme tss ON tsm.snp_id = tss.id
                WHERE tss.user_id = ? AND tsm.status = 1) AS onboarded,

                (SELECT COUNT(*)
                FROM team_msme_schemes ms
                INNER JOIN team_snpmsme_mapping tsm ON tsm.msme_id = ms.id
                INNER JOIN team_snp_scheme tss ON tsm.snp_id = tss.id
                WHERE tss.user_id = ? AND tsm.status = 0) AS chossen
        ', [(string) AuthId(), (string) AuthId()]);

        $flattened = isset($result[0]) ? (array) $result[0] : [];

        // Fetch major_activity-wise counts
        $majorActivity = \DB::select('
            SELECT 
                ms.major_activity,
                SUM(CASE WHEN tsm.status = 1 THEN 1 ELSE 0 END) AS onboarded_count,
                SUM(CASE WHEN tsm.status = 0 THEN 1 ELSE 0 END) AS chossen_count
            FROM team_msme_schemes ms
            INNER JOIN team_snpmsme_mapping tsm ON tsm.msme_id = ms.id
            INNER JOIN team_snp_scheme tss ON tsm.snp_id = tss.id
            WHERE tss.user_id = ?
            GROUP BY ms.major_activity
        ', [(string) AuthId()]);

        // Convert to associative array
        $majorActivityCounts = collect($majorActivity)->mapWithKeys(function ($item) {
            return [
                $item->major_activity => [
                    'onboarded' => $item->onboarded_count,
                    'chossen'   => $item->chossen_count
                ]
            ];
        });

        return [
            'total'            => $flattened,
            'major_activities' => $majorActivityCounts
        ];
    }


    public function getGenderWiseOnboardedMsmeCount()
    {
        $userId = (string) AuthId();

        $result = \DB::select('
            SELECT 
                COALESCE(ms.gender, "Not Specified") AS gender,
                COUNT(*) AS total
            FROM team_msme_schemes ms
            INNER JOIN team_snpmsme_mapping tsm ON tsm.msme_id = ms.id
            INNER JOIN team_snp_scheme tss ON tsm.snp_id = tss.id
            WHERE tss.user_id = ?
            AND tsm.status = 1   -- only onboarded
            GROUP BY ms.gender
        ', [$userId]);

        // Convert to associative array
        $genderWiseCounts = collect($result)->mapWithKeys(function ($item) {
            return [
                $item->gender => $item->total
            ];
        });

        return [
            'gender_wise' => $genderWiseCounts
        ];
    }

	
	
	public function getOthersMyMsmeCount()
    {
        // base query for reuse
        $baseQuery = DB::table('team_msme_schemes as ms');
            
        // 1️⃣ Total count
        $totalCount = (clone $baseQuery)->count();

        // 2️⃣ Major activity counts
        $majorActivityCounts = (clone $baseQuery)
            ->select('ms.major_activity', DB::raw('COUNT(*) as total_count'))
            ->groupBy('ms.major_activity')
            ->pluck('total_count', 'ms.major_activity'); // returns key => value

        return [
            'total_msme'        => $totalCount,
            'major_activities'  => $majorActivityCounts
        ];
    }


    public function getOthersOnboardedAndMsmeChoosenMeCount()
    {
        $result = \DB::select('
            SELECT 
                (SELECT COUNT(*) FROM team_msme_schemes ms WHERE ms.select_snp = 0) AS option1,
                (SELECT COUNT(*) FROM team_msme_schemes ms WHERE ms.select_snp = 1) AS option2,
                (SELECT COUNT(*) 
                FROM team_msme_schemes ms
                INNER JOIN team_snpmsme_mapping tsm ON tsm.msme_id = ms.id
                WHERE tsm.status = 1) AS onboarded
        ');

        $flattened = isset($result[0]) ? (array) $result[0] : [];

        // ✅ FIX: onboarded count uses a separate inner join to avoid left join row inflation
        $majorActivity = \DB::select('
            SELECT 
                ms.major_activity,

                COUNT(CASE WHEN ms.select_snp = 0 THEN 1 END) AS option1_count,
                COUNT(CASE WHEN ms.select_snp = 1 THEN 1 END) AS option2_count,

                (
                    SELECT COUNT(*) 
                    FROM team_snpmsme_mapping tsm
                    INNER JOIN team_msme_schemes ms2 ON ms2.id = tsm.msme_id
                    WHERE tsm.status = 1 AND ms2.major_activity = ms.major_activity
                ) AS onboarded_count

            FROM team_msme_schemes ms
            GROUP BY ms.major_activity
        ');

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
        ];
    }


    public function getClaimCountById()
    {
        $authId = (string) AuthId();

        $result = \DB::select('
            SELECT 
                (
                    SELECT COUNT(DISTINCT cs.id)
                    FROM claims cs
                    WHERE cs.created_by = ?
                ) AS totalClaims,
                (
                    SELECT COUNT(DISTINCT cs.id)
                    FROM claims cs
                    INNER JOIN claim_types ct ON cs.claim_type_id = ct.id
                    WHERE cs.created_by = ? AND ct.slug = ?
                ) AS catalogueClaim,
                (
                    SELECT COUNT(DISTINCT cs.id)
                    FROM claims cs
                    INNER JOIN claim_types ct ON cs.claim_type_id = ct.id
                    WHERE cs.created_by = ? AND ct.slug = ?
                ) AS accountClaim,
                (
                    SELECT COUNT(DISTINCT cs.id)
                    FROM claims cs
                    INNER JOIN claim_types ct ON cs.claim_type_id = ct.id
                    WHERE cs.created_by = ? AND ct.slug = ?
                ) AS logisticClaim,
                (
                    SELECT COUNT(DISTINCT cs.id)
                    FROM claims cs
                    WHERE cs.created_by = ? AND cs.status = 3
                ) AS approvedCount,
                (
                    SELECT COUNT(DISTINCT cs.id)
                    FROM claims cs
                    WHERE cs.created_by = ? AND cs.status = 4
                ) AS rejectedCount,
                (
                    SELECT COUNT(DISTINCT cs.id)
                    FROM claims cs
                    WHERE cs.created_by = ? AND cs.status = 5
                ) AS revertedCount,
                (
                    SELECT COUNT(DISTINCT cs.id)
                    FROM claims cs
                    WHERE cs.created_by = ? AND cs.status = 0
                ) AS draftCount,
                (
                    SELECT COUNT(DISTINCT cs.id)
                    FROM claims cs
                    WHERE cs.created_by = ? AND cs.status = 7
                ) AS paymentCompletedCount,
                (
                    SELECT COUNT(DISTINCT cs.id)
                    FROM claims cs
                    WHERE cs.created_by = ? AND cs.status IN (1, 2, 6)
                ) AS pendingCount
        ',
        [
            $authId, // totalClaims

            $authId, 'claim-for-catalogue-creation',
            $authId, 'claim-for-accounts-management',
            $authId, 'claim-for-logistics-and-transportation',

            $authId, // approvedCount
            $authId, // rejectedCount
            $authId, // revertedCount
            $authId,  // pendingCount
            $authId,  // draftCount
            $authId  // paymentCompletedCount
        ]);

        return isset($result[0]) ? (array) $result[0] : [];
    }

    public function getSnpCount()
    {
        $authId = (string) AuthId();

        $result = \DB::select('
            SELECT 
                (
                    SELECT COUNT(DISTINCT snp.id)
                    FROM team_snp_scheme snp
                ) AS totalSnp,
                (
                    SELECT COUNT(DISTINCT snp.id)
                    FROM team_snp_scheme snp
                    WHERE snp.status = 0 
                ) AS pendingSnp,
                (
                    SELECT COUNT(DISTINCT snp.id)
                    FROM team_snp_scheme snp
                    WHERE snp.status = 1 
                ) AS approvedSnp,
                (
                    SELECT COUNT(DISTINCT snp.id)
                    FROM team_snp_scheme snp
                    WHERE snp.status = 4 
                ) AS rejectedSnp,
                (
                    SELECT COUNT(DISTINCT snp.id)
                    FROM team_snp_scheme snp
                    WHERE snp.review_status = 5 AND snp.status = 0 
                ) AS revertedSnp
                
        '
        );

        return isset($result[0]) ? (array) $result[0] : [];
    }

    public function getBnpCount()
    {
        $authId = (string) AuthId();

        $result = \DB::select('
            SELECT 
                (
                    SELECT COUNT(DISTINCT bnp.id)
                    FROM team_bnp_scheme bnp
                ) AS totalBnp,
                (
                    SELECT COUNT(DISTINCT bnp.id)
                    FROM team_bnp_scheme bnp
                    WHERE bnp.status = 0 
                ) AS pendingBnp,
                (
                    SELECT COUNT(DISTINCT bnp.id)
                    FROM team_bnp_scheme bnp
                    WHERE bnp.status = 1 
                ) AS approvedBnp,
                (
                    SELECT COUNT(DISTINCT bnp.id)
                    FROM team_bnp_scheme bnp
                    WHERE bnp.status = 4 
                ) AS rejectedBnp,
                (
                    SELECT COUNT(DISTINCT bnp.id)
                    FROM team_bnp_scheme bnp
                    WHERE bnp.review_status = 5 AND bnp.status = 0 
                ) AS revertedBnp
                
        '
        );

        return isset($result[0]) ? (array) $result[0] : [];
    }

    public function getOtherClaimCountById($status, $sentStatus)
    {
        // ✅ Whitelist allowed columns to prevent SQL injection
        $allowedColumns = ['ondc_review_status', 'nsic_review_status', 'nsicfinance_review_status','status','ca_review_status'];
        if (!in_array($status, $allowedColumns)) {
            throw new \InvalidArgumentException("Invalid column name: " . $status);
        }

        $isSentallowedColumns = ['is_sent_ondc', 'is_sent_nsic', 'is_sent_nsicfinance','is_sent_ca'];
        if (!in_array($sentStatus, $isSentallowedColumns)) {
            throw new \InvalidArgumentException("Invalid column name: " . $sentStatus);
        }

        // ✅ Build SQL dynamically with column name
        $column = "cs." . $status;
        $sentColumn = "cs." . $sentStatus;

        $result = \DB::select("
            SELECT 
                (
                    SELECT COUNT(DISTINCT cs.id)
                    FROM claims cs
                    WHERE {$sentColumn} = 1
                ) AS totalClaims,
                (
                    SELECT COUNT(DISTINCT cs.id)
                    FROM claims cs
                    WHERE {$column} = 3
                ) AS approvedCount,
                (
                    SELECT COUNT(DISTINCT cs.id)
                    FROM claims cs
                    WHERE {$column} = 4
                ) AS rejectedCount,
                (
                    SELECT COUNT(DISTINCT cs.id)
                    FROM claims cs
                    WHERE {$column} = 5
                ) AS revertedCount,
                (
                    SELECT COUNT(DISTINCT cs.id)
                    FROM claims cs
                    WHERE {$column} = 7
                ) AS paymentCompletedCount,
                (
                    SELECT COUNT(DISTINCT cs.id)
                    FROM claims cs
                    WHERE {$column} IN (1, 2, 6)
                ) AS pendingCount
        ");

        return isset($result[0]) ? (array) $result[0] : [];
    }

	function getCADashboardBatchSummary(){
        $authId = (string) AuthId();
        
         $query = "
                SELECT
                    COUNT(DISTINCT b.id) AS totalBatch,
                    COUNT(DISTINCT CASE WHEN b.is_ca_certified IS NULL OR b.is_sent_ca = 1 THEN b.id END) AS pendingBatch,
                    COUNT(DISTINCT CASE WHEN b.is_ca_certified = 1 THEN b.id END) AS approvedBatch
                FROM batches b
                INNER JOIN team_snpca_mapping tsm ON tsm.snp_user_id = b.created_by
                WHERE tsm.ca_user_id = :authId
            ";

        $result = \DB::select($query, ['authId' => $authId]);
            // dd($result);
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


}