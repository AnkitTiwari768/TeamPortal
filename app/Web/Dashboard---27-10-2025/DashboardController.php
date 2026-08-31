<?php 

declare(strict_types=1);

namespace App\Web\Dashboard;

use App\Http\Controllers\ClientController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

final class DashboardController extends ClientController 
{
    public function __construct(private DashboardService $service) {}


    public function index($year=null)
    { 
		//dd($this->getMsmeStateWiseCount());
		$userRoles = getAuthUserRoles();
		
		if(in_array('administrator', array_column($userRoles,'slug')))
        {
            return view('dashboard.in-progress')->with('title',__('message.dashboard_list'));
        }
		
		elseif (in_array('snp', array_column($userRoles,'slug'))) 
        {
			$msmeForMeCounts = $this->service->getSnpMyMsmeCount(); 
			$msmeCounts = $this->service->getSnpOnboardedAndMsmeChoosenMeCount(); 
			$msmeCountsByGender = $this->service->getGenderWiseOnboardedMsmeCount();
			$claimCounts = $this->service->getClaimCountById(); 
			return view('dashboard.snp',compact('msmeForMeCounts','msmeCounts','claimCounts','msmeCountsByGender'))->with('title', __('message.dashboard_list'));
			
		}elseif (array_intersect(['ondc-admin', 'nsic','nsic-finance','mo-mse','ca'], array_column($userRoles, 'slug')))
        {
			if (in_array('ondc-admin', array_column($userRoles,'slug'))) 
			{
				$status = 'ondc_review_status'; $sentStatus = 'is_sent_ondc';
			}
			if (in_array('nsic', array_column($userRoles,'slug'))) 
			{
				$status = 'nsic_review_status'; $sentStatus = 'is_sent_nsic';
			}
			if (in_array('nsic-finance', array_column($userRoles,'slug'))) 
			{
				$status = 'nsicfinance_review_status'; $sentStatus = 'is_sent_nsicfinance';
			}
			if (in_array('mo-mse', array_column($userRoles,'slug'))) 
			{
				$status = 'status'; $sentStatus = 'is_sent_ondc';
			}
			if (in_array('ca', array_column($userRoles,'slug'))) 
			{
				$status = 'status'; $sentStatus = 'is_sent_ca';
			}
			$msmeForMeCounts = $this->service->getOthersMyMsmeCount(); 
			$msmeCounts = $this->service->getOthersOnboardedAndMsmeChoosenMeCount(); 
			$claimCounts = $this->service->getOtherClaimCountById($status, $sentStatus); 
            $snpCount = $this->service->getSnpCount(); 	
            $bnpCount = $this->service->getBnpCount();
			$caCountSummaryBatchWise = $this->service->getCADashboardBatchSummary(); 
            
           return view('dashboard.others',compact('msmeForMeCounts','msmeCounts','claimCounts','snpCount','bnpCount','caCountSummaryBatchWise'))->with('title', __('message.dashboard_list'));
		}
        else 
        {
            return view('dashboard.in-progress')->with('title',__('message.dashboard_list'));

        }
        
    }



    public function getMappingMsmeCount(Request $request)
	{
		$year = $request->has('year') && $request->year 
			? $request->year 
			: now()->year;
		
		$authId = (string) AuthId();

		$data = DB::table('team_snpmsme_mapping as snpmap')
			->selectRaw('MONTHNAME(snpmap.created_at) as month_name, MONTH(snpmap.created_at) as month, COUNT(DISTINCT snpmap.id) as msme_count');

		if (hasRole('snp')) {
			$data->join('team_snp_scheme as snp','snpmap.snp_id', '=','snp.id');            
			$data->where('snp.user_id', $authId);
		}

		$data = $data
			->where('snpmap.status', 1)
			->whereYear('snpmap.created_at', $year)
			->groupByRaw('YEAR(snpmap.created_at), MONTH(snpmap.created_at)')
			->orderByRaw('MONTH(snpmap.created_at)')
			->get();  // ✅ Assign to $data (now a collection)

		// Ensure months with 0 count are included
		$months = collect([
			1=>'Jan',2=>'Feb',3=>'Mar',4=>'Apr',5=>'May',6=>'Jun',
			7=>'Jul',8=>'Aug',9=>'Sep',10=>'Oct',11=>'Nov',12=>'Dec'
		]);

		$result = $months->map(function ($name, $month) use ($data) {
			$found = $data->firstWhere('month', $month);
			return ['name' => $name, 'y' => $found->msme_count ?? 0];
		})->values();
		
		return response()->json($result);
	}

	
	
	/*public function getMsmeCategoryCountOnboarded()
	{
		$authId = (string) AuthId();

		// Base query for total count
		$totalCountQuery = DB::table('team_msme_schemes as tms')
			->join('team_snpmsme_mapping as tsm', 'tsm.msme_id', '=', 'tms.id');

		// ✅ Join only if user has SNP role
		if (hasRole('snp')) {
			$totalCountQuery->join('team_snp_scheme as tss', 'tss.id', '=', 'tsm.snp_id')
				->where('tss.user_id', $authId);
		}

		$totalCount = $totalCountQuery
			->where('tsm.status', 1)
			->count();

		// Base query for category stats
		$subDomainQuery = DB::table('team_msme_schemes as tms')
			->join('team_snpmsme_mapping as tsm', 'tsm.msme_id', '=', 'tms.id')
			->join('sub_domains as sd', function($join) {
				$join->whereRaw('JSON_CONTAINS(tms.product_category_id, JSON_QUOTE(sd.id))');
			});

		// ✅ Join only if user has SNP role
		if (hasRole('snp')) {
			$subDomainQuery->join('team_snp_scheme as tss', 'tss.id', '=', 'tsm.snp_id')
				->where('tss.user_id', $authId);
		}

		$subDomainStats = $subDomainQuery
			->where('tsm.status', 1)
			->select(
				'sd.name',
				DB::raw('ROUND((COUNT(tms.id)/'.$totalCount.')*100,2) as y'),
				DB::raw('COUNT(tms.id) as count')
			)
			->groupBy('sd.id', 'sd.name')
			->orderBy('sd.name', 'asc')
			->orderBy('y', 'asc')
			->orderBy('count', 'asc')
			->get()
			->map(function ($item) {
				return [
					'name'  => $item->name,
					'y'     => (float) $item->y,
					'count' => (int) $item->count,
				];
			})
			->toArray();

		return response()->json(['data' => $subDomainStats]);
	}*/

	public function getMsmeCategoryCountOnboarded()
	{
		$authId = (string) AuthId();

		// Base query for total count
		$totalCountQuery = DB::table('team_msme_schemes as tms')
			->join('team_snpmsme_mapping as tsm', 'tsm.msme_id', '=', 'tms.id');

		// ✅ Join only if user has SNP role
		if (hasRole('snp')) {
			$totalCountQuery->join('team_snp_scheme as tss', 'tss.id', '=', 'tsm.snp_id')
				->where('tss.user_id', $authId);
		}

		$totalCount = $totalCountQuery
			->where('tsm.status', 1)
			->count();

		// Base query for category stats
		$subDomainQuery = DB::table('team_msme_schemes as tms')
			->join('team_snpmsme_mapping as tsm', 'tsm.msme_id', '=', 'tms.id')
			->join('sub_domains as sd', function($join) {
				$join->whereRaw('JSON_CONTAINS(tms.product_category_id, JSON_QUOTE(sd.id))');
			});

		// ✅ Join only if user has SNP role
		if (hasRole('snp')) {
			$subDomainQuery->join('team_snp_scheme as tss', 'tss.id', '=', 'tsm.snp_id')
				->where('tss.user_id', $authId);
		}

		$subDomainStats = $subDomainQuery
			->where('tsm.status', 1)
			->select(
				'sd.name',
				DB::raw('ROUND((COUNT(tms.id)/'.$totalCount.')*100,2) as y'),
				DB::raw('COUNT(tms.id) as count')
			)
			->groupBy('sd.id', 'sd.name')
			->orderByDesc('count')   // 👈 Order by count (descending) to get top subdomains
			->limit(10)              // 👈 Take only top 10
			->get()
			->map(function ($item) {
				return [
					'name'  => $item->name,
					'y'     => (float) $item->y,
					'count' => (int) $item->count,
				];
			})
			->toArray();

		return response()->json(['data' => $subDomainStats]);
	}

	public function getMsmeStateWiseCount(Request $request)
	{
		$year = $request->has('syear') && $request->syear 
			? $request->syear 
			: now()->year;

		$authId = (string) AuthId();

		$query = DB::table('team_msme_schemes as tms')
			->join('team_snpmsme_mapping as tsm', 'tsm.msme_id', '=', 'tms.id')
			->join('states as st', 'st.id', '=', 'tms.state_id');

		// ✅ Join only if role is SNP
		if (hasRole('snp')) {
			$query->join('team_snp_scheme as tss', 'tss.id', '=', 'tsm.snp_id')
				->where('tss.user_id', $authId);
		}

		$subDomainStats = $query
			->where('tsm.status', 1)
			->whereYear('tsm.created_at', $year)
			->select(
				'st.name',
				DB::raw('COUNT(tms.id) as count')
			)
			->groupBy('st.id', 'st.name')
			->orderBy('st.name', 'asc')
			->orderBy('count', 'asc')
			->get()
			->map(function ($item) {
				return [
					'name' => $item->name,
					'y'    => (int) $item->count,
				];
			})
			->toArray();

		return response()->json($subDomainStats);
	}

	public function getMsmePercentageSellerAndCatlogues()
	{
		$authId = (string) AuthId();
		$baseQuery = DB::table('team_msme_schemes as tms')
			->join('team_snpmsme_mapping as tsm', 'tsm.msme_id', '=', 'tms.id');

		if (hasRole('snp')) {
			$baseQuery->join('team_snp_scheme as tss', 'tss.id', '=', 'tsm.snp_id')
					->where('tss.user_id', $authId);
		}
		$baseQuery->where('tsm.status', 1);
		$totalCount = (clone $baseQuery)->count();

		if ($totalCount == 0) {
			return response()->json(['data' => []]);
		}
		$counts = (clone $baseQuery)
			->selectRaw('
				SUM(CASE WHEN tms.is_uploaded IS NULL THEN 1 ELSE 0 END) AS sellers,
				SUM(CASE WHEN tms.is_uploaded IS NOT NULL THEN 1 ELSE 0 END) AS catalogues
			')
			->first();
		$data = [
			[
				'name'  => 'Sellers',
				'y'     => round(($counts->sellers / $totalCount) * 100, 2),
				'count' => (int) $counts->sellers,
			],
			[
				'name'  => 'Catalogues',
				'y'     => round(($counts->catalogues / $totalCount) * 100, 2),
				'count' => (int) $counts->catalogues,
			],
		];

		return response()->json(['data' => $data]);
	}
	// public function getClaimsSummary()
	// {
    // 	$authId = (string) AuthId();

	// 	$result = \DB::select('
	// 		SELECT 
	// 			ct.slug AS claimType,
	// 			ct.name AS claimTypeName,
	// 			COUNT(DISTINCT cs.id) AS totalClaims,
	// 			SUM(CASE WHEN cs.status IN (1, 2, 6) THEN 1 ELSE 0 END) AS pendingCount,
	// 			SUM(CASE WHEN cs.status = 3 THEN 1 ELSE 0 END) AS approvedCount,
	// 			SUM(CASE WHEN cs.status = 5 THEN 1 ELSE 0 END) AS revertedCount,
	// 			SUM(CASE WHEN cs.status = 4 THEN 1 ELSE 0 END) AS rejectedCount,
	// 			COALESCE(SUM(cs.amount), 0) AS totalAmount
	// 		FROM claim_types ct
	// 		LEFT JOIN claims cs 
	// 			ON cs.claim_type_id = ct.id
	// 			AND cs.created_by = ?
	// 		WHERE ct.slug IN (
	// 			"claim-for-catalogue-creation",
	// 			"claim-for-accounts-management",
	// 			"claim-for-logistics-and-transportation"
	// 		)
	// 		GROUP BY ct.slug, ct.name
	// 		ORDER BY ct.name ASC
	// 	', [$authId]);
	// 	return response()->json([
	// 		'success' => true,
	// 		'data' => $result
	// 	]);

	// }

	public function getClaimsSummary()
	{
		$authId = (string) AuthId();

		if (hasRole('ca')) {
			$column = 'cs.ca_review_status';
		} elseif (hasRole('ondc')) {
			$column = 'cs.ondc_review_status';
		} elseif (hasRole('nsic')) {
			$column = 'cs.nsic_review_status';
		} elseif (hasRole('nsicfinance')) {
			$column = 'cs.nsicfinance_review_status';
		} else {
			$column = 'cs.status';
		}

		$result = \DB::select("
			SELECT 
				ct.slug AS claimType,
				ct.name AS claimTypeName,
				COUNT(DISTINCT cs.id) AS totalClaims,
				SUM(CASE WHEN {$column} IN (1, 2, 6) THEN 1 ELSE 0 END) AS pendingCount,
				SUM(CASE WHEN {$column} = 3 THEN 1 ELSE 0 END) AS approvedCount,
				SUM(CASE WHEN {$column} = 5 THEN 1 ELSE 0 END) AS revertedCount,
				SUM(CASE WHEN {$column} = 4 THEN 1 ELSE 0 END) AS rejectedCount,
				COALESCE(SUM(cs.amount), 0) AS totalAmount
			FROM claim_types ct
			LEFT JOIN claims cs 
				ON cs.claim_type_id = ct.id
				AND (
					'{$column}' = 'cs.status' AND cs.created_by = '{$authId}'
					OR
					'{$column}' <> 'cs.status'
				)
			WHERE ct.slug IN (
				'claim-for-catalogue-creation',
				'claim-for-accounts-management',
				'claim-for-logistics-and-transportation'
			)
			GROUP BY ct.slug, ct.name
			ORDER BY ct.name ASC
		");
		//dd($result);
		return response()->json([
			'success' => true,
			'data' => $result
		]);
	}


	public function getStaticBarChart(Request $request)
	{
		$year = $request->has('year') && $request->year 
			? $request->year 
			: now()->year;
		
		$authId = (string) AuthId();

		$data = DB::table('team_snpmsme_mapping as snpmap')
			->selectRaw('MONTHNAME(snpmap.created_at) as month_name, MONTH(snpmap.created_at) as month, COUNT(DISTINCT snpmap.id) as msme_count');

		if (hasRole('snp')) {
			$data->join('team_snp_scheme as snp','snpmap.snp_id', '=','snp.id');            
			$data->where('snp.user_id', $authId);
		}

		$data = $data
			->where('snpmap.status', 1)
			->whereYear('snpmap.created_at', $year)
			->groupByRaw('YEAR(snpmap.created_at), MONTH(snpmap.created_at)')
			->orderByRaw('MONTH(snpmap.created_at)')
			->get();  // ✅ Assign to $data (now a collection)
		dd($data);
		// Ensure months with 0 count are included
		$months = collect([
			1=>'Jan',2=>'Feb',3=>'Mar',4=>'Apr',5=>'May',6=>'Jun',
			7=>'Jul',8=>'Aug',9=>'Sep',10=>'Oct',11=>'Nov',12=>'Dec'
		]);

		$result = $months->map(function ($name, $month) use ($data) {
			$found = $data->firstWhere('month', $month);
			return ['name' => $name, 'y' => $found->msme_count ?? 0];
		})->values();
		
		return response()->json($result);
	}


	public function getTopPerformerSnps()
	{
		$result = \DB::select('
			SELECT 
				tss.snp_id AS snp_name,
				tss.id AS snp_id,
				COUNT(tsm.msme_id) AS total
			FROM team_snp_scheme tss
			INNER JOIN team_snpmsme_mapping tsm ON tss.id = tsm.snp_id
			WHERE tsm.status = 1
			GROUP BY tss.id, tss.snp_name
			ORDER BY total DESC
			LIMIT 10
		');
		//dd($result);

		return response()->json([
			'data' => $result
		]);
	}

}