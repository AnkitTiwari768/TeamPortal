<?php

declare(strict_types=1);

namespace App\Web\Dashboard;

use App\Domain\IARegistration\GetDashboardDetailsAction;
use App\Http\Controllers\ClientController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Http\Api\V1\Auth\AuthService;
use Carbon\Carbon;
use App\Domain\Msme\MsmeDetailsAction;
use App\Domain\Msme\MsmeJourneyAction;
use App\Domain\Msme\SnpDetailsAction;
use App\Web\Dashboard\GetClaimSummaryAction;




final class DashboardController extends ClientController
{
	use DashboardTrait, DashboardSnpTrait, DashboardIaTrait;
	public function __construct(private DashboardService $service)
	{
	}
	public function switchRole(Request $request)
	{
		$role = $request->input('role');
		$userRoles = getAuthUserRoles();
		$userRoleSlugs = array_column($userRoles, 'slug');

		// Standardize assigned slugs to lowercase for safe matching
		$slugsLower = array_map('strtolower', $userRoleSlugs);
		$matchedIndex = array_search(strtolower((string) $role), $slugsLower, true);

		// Security: ensure the requested role is actually assigned to this user
		if ($matchedIndex === false) {
			return response()->json([
				'success' => false,
				'message' => 'Role not assigned to user.',
			], 403);
		}

		$matchedRole = $userRoleSlugs[$matchedIndex];

		// Fetch only this role's permissions and overwrite the session
		$permissions = app(AuthService::class)->getUserPermissionsAssigned((string) authId(), $matchedRole);

		session([
			'active_role' => $matchedRole,
			'permissions' => $permissions,
		]);

		return response()->json(['success' => true]);
	}

	private function checkDashboardAccess($request)
	{
		if (!acl('Dashboard')) {
			if ($request->ajax()) {
				return response()->json([
					'success' => false,
					'message' => 'You do not have permission to view the dashboard.',
				], 403);
			}
			return view('dashboard.default')->with('title', __('message.dashboard_list'));
			// return view('dashboard.access-denied')->with([
			// 	'title' => __('Access Denied'),
			// 	'message' => 'You do not have permission to view the dashboard. Please contact your administrator.',
			// ]);
		}
		return null;
	}
	public function index(Request $request, $year = null)
	{
		$accessCheck = $this->checkDashboardAccess($request);
		if ($accessCheck) {
			return $accessCheck;
		}
		//dd($this->getNPDashboardDetails());
		$userRoles = array_column(getAuthUserRoles(), 'slug');
		$activeRole = getActiveRole();
		if (!$activeRole) {
			$activeRole = app(AuthService::class)->resolveDefaultRole($userRoles);
		}

		$type = $request->type ?? null;

		$fromDate = null;
		$toDate = null;
		$selectedYear = null;

		if (!empty($request->from_date_new) && !empty($request->to_date_new)) {

			$fromDate = $request->from_date_new;
			$toDate = $request->to_date_new;
		} elseif (!empty($request->year)) {

			$selectedYear = $request->year;
		} else {

			if ($type == 1) {
				$selectedYear = null;
			}
		}

		if ($activeRole === 'administrator') {
			return app(AdminDashboardController::class)->index($request);
		} elseif ($activeRole === 'snp') {
			return app(SnpDashboardController::class)->index($request);
		} elseif ($activeRole === 'nsic') {
			// ── NSIC gets its own dedicated dashboard ───────────────────────
			return app(NsicDashboardController::class)->index($request);

		} elseif ($activeRole === 'ondc-admin') {
			// ── ONDC Admin gets its own dedicated dashboard ─────────────────
			return app(OndcDashboardController::class)->index($request);

		} elseif (in_array($activeRole, ['nsic-finance', 'mo-mse', 'ca'])) {
			// ── Remaining roles continue to use dashboard.others ─────────────

			if ($activeRole === 'nsic-finance') {
				$status = 'nsicfinance_review_status';
				$sentStatus = 'is_sent_nsicfinance';
			}
			if ($activeRole === 'mo-mse') {
				$status = 'status';
				$sentStatus = 'is_sent_ondc';
			}
			if ($activeRole === 'ca') {
				$status = 'status';
				$sentStatus = 'is_sent_ca';
			}

			$registeredMsmeCounts = $this->service->getOthersRegisteredMsmeCount($selectedYear, $fromDate, $toDate, $type);

			$msmeOpenCounts = $this->service->getSnpOtherRolesOpenMsmeCount($selectedYear, $fromDate, $toDate, $type);

			$msmechoosenCounts = $this->service->getSnpOtherRolesChoosenMsmeCount($selectedYear, $fromDate, $toDate, $type);
			//dd($msmechoosenCounts);

			$msmeOnboardedCounts = $this->service->getSnpOtherRolesOnboardedCount($selectedYear, $fromDate, $toDate, $type);
			//dd($msmeOnboardedCounts);

			//$np = $this->getNPDashboardDetails($selectedYear, $fromDate, $toDate, $type);
			$snp = $this->getSnpDashboardDetails('snp', $selectedYear, $fromDate, $toDate, $type);
			$bnp = $this->getBlDashboardDetails('bnp', $selectedYear, $fromDate, $toDate, $type);
			$lsp = $this->getBlDashboardDetails('lsp', $selectedYear, $fromDate, $toDate, $type);


			$msmeCounts = $this->service->getOthersOnboardedAndMsmeChoosenMeCount($selectedYear, $fromDate, $toDate);
			$claimCounts = $this->service->getOtherClaimCountById($status, $sentStatus, $selectedYear, $fromDate, $toDate);
			$snpCount = $this->service->getSnpCount($selectedYear, $fromDate, $toDate);
			$bnpCount = $this->service->getBnpCount($selectedYear, $fromDate, $toDate);
			$caCountSummaryBatchWise = $this->service->getCADashboardBatchSummary($selectedYear, $fromDate, $toDate);

			$fundMetrics = null;
			if (acl(config('permissions.allocation-view')) || acl(config('permissions.fund-distribution-view'))) {
				$fundMetrics = $this->service->getFundManagementMetricsData($selectedYear, $fromDate, $toDate, $type);
			}

			if ($request->ajax()) {
				return response()->json(compact('registeredMsmeCounts', 'msmeOpenCounts', 'msmechoosenCounts', 'msmeOnboardedCounts', 'msmeCounts', 'bnp', 'lsp', 'snp', 'claimCounts', 'snpCount', 'bnpCount', 'caCountSummaryBatchWise', 'fundMetrics'));
			}
			// dd(2);
			return view('dashboard.others', compact('registeredMsmeCounts', 'msmeOpenCounts', 'msmechoosenCounts', 'msmeOnboardedCounts', 'msmeCounts', 'claimCounts', 'bnp', 'lsp', 'snp', 'snpCount', 'bnpCount', 'caCountSummaryBatchWise', 'selectedYear', 'fundMetrics'))
				->with('title', __('message.dashboard_list'));
		} elseif ($activeRole === 'msme') {

			$msme_dashboard_details = app(MsmeJourneyAction::class)->execute(authId());
			//dd($msme_dashboard_details);
			$msme_details = app(MsmeDetailsAction::class)->execute(authId());
			$snp_details = app(SnpDetailsAction::class)->execute(authId());

			//dd($msme_dashboard_details,$msme_details,$snp_details);
			// dd(3);
			return view('dashboard.msme', compact('msme_dashboard_details', 'msme_details', 'snp_details'))->with('title', __('message.dashboard_list'));
		} elseif ($activeRole === 'ia-registration') {

			$data = app(GetDashboardDetailsAction::class)->execute($selectedYear, $fromDate, $toDate, $type);

			$totalMseRegistered = $data['totalMseRegistered'];
			$totalMseMapped = $data['totalMseMapped'];

			if ($request->ajax()) {
				return response()->json(compact('totalMseRegistered', 'totalMseMapped'));
			}
			// dd(4);
			return view('dashboard.ia', compact('totalMseRegistered', 'totalMseMapped', 'selectedYear'))->with('title', __('message.dashboard_list'));
		} else if (in_array($activeRole, ['bnp', 'lsp'])) {

			return view('dashboard.np', compact('selectedYear'))->with('title', __('message.dashboard_list'));

		} else {
			// dd(5);
			return view('dashboard.default')->with('title', __('message.dashboard_list'));
		}
	}

	public function getFundManagementMetrics(Request $request)
	{
		$fromDate = null;
		$toDate = null;
		$selectedYear = null;
		$type = $request->type !== null ? (int) $request->type : 1;

		if (!empty($request->from_date_new) && !empty($request->to_date_new)) {
			$fromDate = $request->from_date_new;
			$toDate = $request->to_date_new;
		} elseif (!empty($request->year)) {
			$selectedYear = $request->year;
		}

		$data = $this->service->getFundManagementMetricsData($selectedYear, $fromDate, $toDate, $type);

		return response()->json($data);
	}

	public function getMappingMsmeCount(Request $request)
	{
		/*$year = $request->has('year') && $request->year
			? $request->year
			: now()->year;

		$authId = (string) AuthId();

		$data = DB::table('team_snpmsme_mapping as snpmap')
			->selectRaw('MONTHNAME(snpmap.created_at) as month_name, MONTH(snpmap.created_at) as month, COUNT(DISTINCT snpmap.id) as msme_count');

		if (hasRole('snp')) {
			$data->join('team_snp_scheme as snp', 'snpmap.snp_id', '=', 'snp.id');
			$data->where('snp.user_id', $authId);
		}

		$data->where('snpmap.status', 1);

		if ($request->filled('from_date') && $request->filled('to_date')) {
			$data->whereBetween(DB::raw('DATE(snpmap.created_at)'), [
				Carbon::parse($request->from_date)->startOfDay(),
				Carbon::parse($request->to_date)->endOfDay()
			]);

			if ($request->filled('year')) {
				$data->whereYear('snpmap.created_at', $year);
			}
		} else {
			$data->whereYear('snpmap.created_at', $year);
		}

		$data = $data
			->groupByRaw('YEAR(snpmap.created_at), MONTH(snpmap.created_at)')
			->orderByRaw('MONTH(snpmap.created_at)')
			->get();

		$months = collect([
			1 => 'Jan',
			2 => 'Feb',
			3 => 'Mar',
			4 => 'Apr',
			5 => 'May',
			6 => 'Jun',
			7 => 'Jul',
			8 => 'Aug',
			9 => 'Sep',
			10 => 'Oct',
			11 => 'Nov',
			12 => 'Dec'
		]);

		$result = $months->map(function ($name, $month) use ($data) {
			$found = $data->firstWhere('month', $month);
			return ['name' => $name, 'y' => $found->msme_count ?? 0];
		})->values();

		// return response()->json($result);*/

		$year = $request->filled('year') ? (int) $request->year : now()->year;
		$authId = (string) AuthId();

		/*
|--------------------------------------------------------------------------
| Base query
|--------------------------------------------------------------------------
*/
		$query = DB::table('team_snpmsme_mapping as snpmap')
			->join('team_msme_schemes as tms', 'tms.id', '=', 'snpmap.msme_id')
			->selectRaw('
        MONTH(snpmap.created_at) as month,
        COUNT(DISTINCT snpmap.id) as msme_count
    ')
			->where('snpmap.status', 1)
			->whereNotNull('tms.major_activity')
			->where('tms.major_activity', '!=', '');

		/*
|--------------------------------------------------------------------------
| SNP role filter
|--------------------------------------------------------------------------
*/
		// if (hasRole('snp')) {
		// 	$query->join('team_snp_scheme as snp', 'snpmap.snp_id', '=', 'snp.id')
		// 		->where('snp.user_id', $authId);
		// }
		if (hasRole('snp')) {
			$query->join('team_snp_scheme as snp', 'snpmap.snp_id', '=', 'snp.id')
				->whereIn('snp.user_id', getSubUserAndParentIds(authId()));
		}

		/*
|--------------------------------------------------------------------------
| Date filter (either date range OR year)
|--------------------------------------------------------------------------
*/
		if ($request->filled('from_date_new') && $request->filled('to_date_new')) {

			$query->whereBetween('snpmap.created_at', [
				Carbon::parse($request->from_date_new)->startOfDay(),
				Carbon::parse($request->to_date_new)->endOfDay(),
			]);
		} elseif ($request->type != 1) {

			$query->whereYear('snpmap.created_at', $year);
		}

		/*
|--------------------------------------------------------------------------
| Group & fetch
|--------------------------------------------------------------------------
*/
		$data = $query
			->groupByRaw('MONTH(snpmap.created_at)')
			->orderByRaw('MONTH(snpmap.created_at)')
			->get();

		/*
|--------------------------------------------------------------------------
| Month labels
|--------------------------------------------------------------------------
*/
		$months = collect([
			1 => 'Jan',
			2 => 'Feb',
			3 => 'Mar',
			4 => 'Apr',
			5 => 'May',
			6 => 'Jun',
			7 => 'Jul',
			8 => 'Aug',
			9 => 'Sep',
			10 => 'Oct',
			11 => 'Nov',
			12 => 'Dec',
		]);

		/*
|--------------------------------------------------------------------------
| Final response (ensure all months present)
|--------------------------------------------------------------------------
*/
		$total = 0;
		$result = $months->map(function ($name, $month) use ($data, &$total) {
			$found = $data->firstWhere('month', $month);
			$count = (int) ($found->msme_count ?? 0);
			$total += $count;

			return [
				'name' => $name,
				'y' => $count,
			];
		})->values();

		return response()->json([
			'data' => $result,
			'total' => $total
		]);
	}


	// 	public function getMsmeCategoryCountOnboarded(Request $request)
// 	{
// 		/*$authId = (string) AuthId();

	// 		$year = $request->has('year') && $request->year
// 			? $request->year
// 			: now()->year;

	// 		$totalCountQuery = DB::table('team_msme_schemes as tms')
// 			->join('team_snpmsme_mapping as tsm', 'tsm.msme_id', '=', 'tms.id');

	// 		if (hasRole('snp')) {
// 			$totalCountQuery->join('team_snp_scheme as tss', 'tss.id', '=', 'tsm.snp_id')
// 				->where('tss.user_id', $authId);
// 		}

	// 		if ($request->filled('from_date') && $request->filled('to_date')) {
// 			$totalCountQuery->whereBetween(DB::raw('DATE(tsm.created_at)'), [
// 				Carbon::parse($request->from_date)->startOfDay(),
// 				Carbon::parse($request->to_date)->endOfDay()
// 			]);
// 		} else {
// 			$totalCountQuery->whereYear('tsm.created_at', $year);
// 		}

	// 		$totalCount = $totalCountQuery
// 			->where('tsm.status', 1)
// 			->count();

	// 		$subDomainQuery = DB::table('team_msme_schemes as tms')
// 			->join('team_snpmsme_mapping as tsm', 'tsm.msme_id', '=', 'tms.id')
// 			->join('sub_domains as sd', function ($join) {
// 				$join->whereRaw('JSON_CONTAINS(tms.product_category_id, JSON_QUOTE(sd.id))');
// 			});

	// 		if (hasRole('snp')) {
// 			$subDomainQuery->join('team_snp_scheme as tss', 'tss.id', '=', 'tsm.snp_id')
// 				->where('tss.user_id', $authId);
// 		}


	// 		if ($request->filled('from_date') && $request->filled('to_date')) {
// 			$subDomainQuery->whereBetween(DB::raw('DATE(tsm.created_at)'), [
// 				Carbon::parse($request->from_date)->startOfDay(),
// 				Carbon::parse($request->to_date)->endOfDay()
// 			]);

	// 			if ($request->filled('year')) {
// 				$subDomainQuery->whereYear('tsm.created_at', $year);
// 			}
// 		} else {
// 			$subDomainQuery->whereYear('tsm.created_at', $year);
// 		}


	// 		$subDomainStats = $subDomainQuery
// 			->where('tsm.status', 1)
// 			->select(
// 				'sd.name',
// 				DB::raw('ROUND((COUNT(tms.id)/' . $totalCount . ')*100,2) as y'),
// 				DB::raw('COUNT(tms.id) as count')
// 			)
// 			->groupBy('sd.id', 'sd.name')
// 			->orderByDesc('count')
// 			->limit(10)
// 			->get()
// 			->map(function ($item) {
// 				return [
// 					'name'  => $item->name,
// 					'y'     => (float) $item->y,
// 					'count' => (int) $item->count,
// 				];
// 			})
// 			->toArray();

	// 		return response()->json(['data' => $subDomainStats]);*/



	// 		$authId = (string) AuthId();
// 		$year = $request->filled('year') ? (int) $request->year : null;
// 		$type = $request->filled('type') ? (int) $request->type : null;

	// 		/*
// |--------------------------------------------------------------------------
// | Base query (shared)
// |--------------------------------------------------------------------------
// */
// 		$baseQuery = DB::table('team_msme_schemes as tms')
// 			->join('team_snpmsme_mapping as tsm', 'tsm.msme_id', '=', 'tms.id')
// 			->join('team_snp_scheme as tss', 'tss.id', '=', 'tsm.snp_id')
// 			->where('tsm.status', 1)
// 			->whereNotNull('tms.major_activity')
// 			->where('tms.major_activity', '!=', '');

	// 		if (hasRole('snp')) {
// 			$baseQuery->where('tss.user_id', $authId);
// 		}

	// 		/*
// |--------------------------------------------------------------------------
// | Date filter (either range OR year)
// |--------------------------------------------------------------------------
// */
// 		if ($request->filled('from_date_new') && $request->filled('to_date_new')) {

	// 			$baseQuery->whereBetween('tsm.created_at', [
// 				Carbon::parse($request->from_date_new)->startOfDay(),
// 				Carbon::parse($request->to_date_new)->endOfDay(),
// 			]);
// 		} elseif ($year) {
// 			$baseQuery->whereYear('tsm.created_at', $year);
// 		} elseif ($type == 1) {
// 			// No date filter - show all time
// 		} else {
// 			$baseQuery->whereYear('tsm.created_at', now()->year);
// 		}

	// 		/*
// |--------------------------------------------------------------------------
// | Total count
// |--------------------------------------------------------------------------
// */
// 		$totalCount = (clone $baseQuery)->count();

	// 		if ($totalCount === 0) {
// 			return response()->json(['data' => []]);
// 		}

	// 		/*
// |--------------------------------------------------------------------------
// | Sub-domain stats query
// |--------------------------------------------------------------------------
// */
// 		$subDomainStats = (clone $baseQuery)
// 			->join('sub_domains as sd', function ($join) {
// 				$join->whereRaw(
// 					'JSON_CONTAINS(tms.product_category_id, JSON_QUOTE(sd.id))'
// 				);
// 			})
// 			->select(
// 				'sd.name',
// 				DB::raw('COUNT(tms.id) as count'),
// 				DB::raw('ROUND((COUNT(tms.id) / ?) * 100, 2) as y')
// 			)
// 			->addBinding($totalCount, 'select')
// 			->groupBy('sd.id', 'sd.name')
// 			->orderByDesc('count')
// 			->limit(10)
// 			->get()
// 			->map(function ($item) {
// 				return [
// 					'name' => $item->name,
// 					'y' => (float) $item->y,
// 					'count' => (int) $item->count,
// 				];
// 			})
// 			->toArray();

	// 		return response()->json(['data' => $subDomainStats]);
// 	}

	public function getMsmeStateWiseCount(Request $request)
	{
		/*$year = $request->filled('syear') ? $request->syear : now()->year;
		$authId = (string) AuthId();

		$data = DB::table('team_msme_schemes as tms')
			->join('team_snpmsme_mapping as tsm', 'tsm.msme_id', '=', 'tms.id')
			->join('states as st', 'st.id', '=', 'tms.state_id')
			->select('st.name', DB::raw('COUNT(tms.id) as count'));

		if (hasRole('snp')) {
			$data->join('team_snp_scheme as tss', 'tss.id', '=', 'tsm.snp_id');
			$data->where('tss.user_id', $authId);
		}

		$data->where('tsm.status', 1);

		if ($request->filled('from_date') && $request->filled('to_date')) {
			$data->whereBetween(DB::raw('DATE(tsm.created_at)'), [
				Carbon::parse($request->from_date)->startOfDay(),
				Carbon::parse($request->to_date)->endOfDay()
			]);

			if ($request->filled('syear')) {
				$data->whereYear('tsm.created_at', $year);
			}
		} else {
			$data->whereYear('tsm.created_at', $year);
		}

		$data = $data
			->groupBy('st.id', 'st.name')
			->orderBy('st.name', 'asc')
			->orderBy('count', 'asc')
			->get();

		$result = $data->map(function ($item) {
			return [
				'name' => $item->name,
				'y'    => (int) $item->count,
			];
		})->values();

		return response()->json($result);*/



		$year = $request->filled('year') ? (int) $request->year : now()->year;
		$type = $request->filled('type') ? (int) $request->type : null;
		$authId = (string) AuthId();

		$query = DB::table('team_msme_schemes as tms')
			->join('team_snpmsme_mapping as tsm', 'tsm.msme_id', '=', 'tms.id')
			->leftJoin('states as st', 'st.id', '=', 'tms.state_id')
			->where('tsm.status', 1)
			->whereNotNull('tms.major_activity')
			->where('tms.major_activity', '!=', '');

		/*
|--------------------------------------------------------------------------
| SNP role filter
|--------------------------------------------------------------------------
*/
		// if (hasRole('snp')) {
		// 	$query->join('team_snp_scheme as tss', 'tss.id', '=', 'tsm.snp_id')
		// 		->where('tss.user_id', $authId);
		// }

		if (hasRole('snp')) {
			$query->join('team_snp_scheme as snp', 'tsm.snp_id', '=', 'snp.id')
				->whereIn('snp.user_id', getSubUserAndParentIds(authId()));
		}

		/*
|--------------------------------------------------------------------------
| Date filter (either date range OR year)
|--------------------------------------------------------------------------
*/
		if ($request->filled('from_date_new') && $request->filled('to_date_new')) {

			$query->whereBetween('tsm.created_at', [
				Carbon::parse($request->from_date_new)->startOfDay(),
				Carbon::parse($request->to_date_new)->endOfDay(),
			]);
		} elseif ($type != 1) {

			$query->whereYear('tsm.created_at', $year);
		}

		/*
|--------------------------------------------------------------------------
| State-wise count
|--------------------------------------------------------------------------
*/
		$data = $query
			->select(DB::raw("COALESCE(st.name, 'Not Specified') as name"), DB::raw('COUNT(tms.id) as count'))
			->groupBy(DB::raw("COALESCE(st.name, 'Not Specified')"))
			->orderBy('count', 'asc')
			->get();

		/*
|--------------------------------------------------------------------------
| Format response
|--------------------------------------------------------------------------
*/
		$result = $data->map(function ($item) {
			return [
				'name' => $item->name,
				'y' => (int) $item->count,
			];
		})->values();

		return response()->json($result);
	}


	public function getMsmePercentageSellerAndCatlogues(Request $request)
	{
		/*$authId = (string) AuthId();
		$year = $request->filled('year') ? $request->year : now()->year;
		$baseQuery = DB::table('team_msme_schemes as tms')
			->join('team_snpmsme_mapping as tsm', 'tsm.msme_id', '=', 'tms.id');

		if (hasRole('snp')) {
			$baseQuery->join('team_snp_scheme as tss', 'tss.id', '=', 'tsm.snp_id')
				->whereIn('tss.user_id', getSubUserAndParentIds(authId()));
		}
		$baseQuery->where('tsm.status', 1);

		if ($request->filled('from_date') && $request->filled('to_date')) {
			$baseQuery->whereBetween(DB::raw('DATE(tsm.created_at)'), [
				Carbon::parse($request->from_date)->startOfDay(),
				Carbon::parse($request->to_date)->endOfDay()
			]);

			if ($request->filled('year')) {
				$baseQuery->whereYear('tsm.created_at', $year);
			}
		} else {
			$baseQuery->whereYear('tsm.created_at', $year);
		}
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

		return response()->json(['data' => $data]);*/



		$authId = (string) AuthId();
		$year = $request->filled('year') ? (int) $request->year : now()->year;

		$baseQuery = DB::table('team_msme_schemes as tms')
			->join('team_snpmsme_mapping as tsm', 'tsm.msme_id', '=', 'tms.id')
			->where('tsm.status', 1);

		/*
				|--------------------------------------------------------------------------
				| SNP role filter
				|--------------------------------------------------------------------------
				*/
		if (hasRole('snp')) {
			$baseQuery->join('team_snp_scheme as tss', 'tss.id', '=', 'tsm.snp_id')
				->whereIn('tss.user_id', getSubUserAndParentIds(authId()));
		}

		/*
				|--------------------------------------------------------------------------
				| Date filter (either date range OR year)
				|--------------------------------------------------------------------------
				*/
		if ($request->filled('from_date_new') && $request->filled('to_date_new')) {

			$baseQuery->whereBetween('tsm.created_at', [
				Carbon::parse($request->from_date_new)->startOfDay(),
				Carbon::parse($request->to_date_new)->endOfDay(),
			]);
		} elseif ($request->type != 1) {

			$baseQuery->whereYear('tsm.created_at', $year);
		}

		/*
				|--------------------------------------------------------------------------
				| Total count
				|--------------------------------------------------------------------------
				*/
		$totalCount = (clone $baseQuery)->count();

		if ($totalCount === 0) {
			return response()->json(['data' => []]);
		}

		/*
				|--------------------------------------------------------------------------
				| Sellers vs Catalogues count
				|--------------------------------------------------------------------------
				*/
		$counts = (clone $baseQuery)
			->selectRaw('
						SUM(CASE WHEN tms.is_uploaded IS NULL THEN 1 ELSE 0 END) AS sellers,
						SUM(CASE WHEN tms.is_uploaded IS NOT NULL THEN 1 ELSE 0 END) AS catalogues
					')
			->first();

		/*
				|--------------------------------------------------------------------------
				| Response data
				|--------------------------------------------------------------------------
				*/
		$data = [
			[
				'name' => 'Sellers',
				'y' => round(($counts->sellers / $totalCount) * 100, 2),
				'count' => (int) $counts->sellers,
			],
			[
				'name' => 'Catalogues',
				'y' => round(($counts->catalogues / $totalCount) * 100, 2),
				'count' => (int) $counts->catalogues,
			],
		];

		return response()->json(['data' => $data]);
	}


	public function getClaimsSummary(Request $request, GetClaimSummaryAction $action)
	{
		$type = $request->type ?? null;

		$fromDate = null;
		$toDate = null;
		$selectedYear = null;

		if (!empty($request->from_date_new) && !empty($request->to_date_new)) {

			$fromDate = $request->from_date_new;
			$toDate = $request->to_date_new;
		} elseif (!empty($request->year)) {

			$selectedYear = $request->year;
		} else {

			if ($type == 1) {
				$selectedYear = null;
			} else {
				// $selectedYear = date('Y') - 1;
				$selectedYear = null;
			}
		}

		$createdBy = null;
		$isCa = false;

		if (hasRole('snp') || hasRole('lsp') || hasRole('bnp')) {
			$createdBy = authId();
		} else if (hasRole('ca')) {
			$createdBy = DB::table('team_snpca_mapping')->where('ca_user_id', authId())->value('snp_user_id');
			$isCa = true;
		}


		$catalogClaims = $action->execute(claimTypeSlug: 'claim-for-catalogue-creation', year: (int) $selectedYear, fromDate: $fromDate, toDate: $toDate, type: (int) $type, createdBy: $createdBy, isCa: $isCa);
		$accountClaims = $action->execute(claimTypeSlug: 'claim-for-accounts-management', year: (int) $selectedYear, fromDate: $fromDate, toDate: $toDate, type: (int) $type, createdBy: $createdBy, isCa: $isCa);
		$packagingClaims = $action->execute(claimTypeSlug: 'claim-for-packaging', year: (int) $selectedYear, fromDate: $fromDate, toDate: $toDate, type: (int) $type, createdBy: $createdBy, isCa: $isCa);
		$logisticClaims = $action->execute(claimTypeSlug: 'claim-for-transportation-and-logistic', year: (int) $selectedYear, fromDate: $fromDate, toDate: $toDate, type: (int) $type, createdBy: $createdBy, isCa: $isCa);
		$demandGenerationClaims = $action->execute(claimTypeSlug: 'claim-for-demand-generation', year: (int) $selectedYear, fromDate: $fromDate, toDate: $toDate, type: (int) $type, createdBy: $createdBy, isCa: $isCa);


		if (hasRole('snp')) {
			$data = [$catalogClaims, $accountClaims, $packagingClaims];
			$allClaims = $action->execute(year: (int) $selectedYear, fromDate: $fromDate, toDate: $toDate, type: (int) $type, createdBy: $createdBy, isCa: $isCa);
		}
		if (hasRole('ca')) {
			$mappedUserRoles = \DB::table('user_roles')
				->join('roles', 'user_roles.role_id', '=', 'roles.id')
				->where('user_roles.user_id', $createdBy)
				->pluck('slug')
				->toArray();

			if (in_array('lsp', $mappedUserRoles)) {
				$data = [$logisticClaims];
			} else {
				$data = [$catalogClaims, $accountClaims, $packagingClaims];
			}

			$allClaims = $action->execute(year: (int) $selectedYear, fromDate: $fromDate, toDate: $toDate, type: (int) $type, createdBy: $createdBy, isCa: $isCa);
			return response()->json([
				'success' => true,
				'data' => $data,
				'all' => $allClaims,
			]);
		} else if (hasRole('bnp')) {
			$data = [$demandGenerationClaims];
			$allClaims = $action->execute(year: (int) $selectedYear, fromDate: $fromDate, toDate: $toDate, type: (int) $type, createdBy: $createdBy);
		} else if (hasRole('lsp')) {
			$data = [$logisticClaims];
			$allClaims = $action->execute(year: (int) $selectedYear, fromDate: $fromDate, toDate: $toDate, type: (int) $type, createdBy: $createdBy);
		} else {
			$data = [$catalogClaims, $accountClaims, $packagingClaims, $logisticClaims, $demandGenerationClaims];
			$allClaims = $action->execute(year: (int) $selectedYear, fromDate: $fromDate, toDate: $toDate, type: (int) $type, createdBy: $createdBy);
		}


		return response()->json([
			'success' => true,
			'data' => $data,
			'all' => $allClaims
		]);



		/*$authId   = (string) AuthId();
		$fromDate = $request->input('from_date_new');
		$toDate   = $request->input('to_date_new');
		$year     = $request->input('year');

		if (hasRole('ca')) {

			$dateFilter   = '';
			$dateBindings = [];

			if (!empty($fromDate) && !empty($toDate)) {
				$dateFilter = " AND bw.created_at BETWEEN ? AND ?";
				$dateBindings[] = Carbon::parse($fromDate)->startOfDay();
				$dateBindings[] = Carbon::parse($toDate)->endOfDay();
			} elseif (!empty($year)) {
				$dateFilter = " AND YEAR(bw.created_at) = ?";
				$dateBindings[] = (int) $year;
			}

			$result = DB::select("
				SELECT 
					ct.slug AS claimType,
					SUM(CASE WHEN bw.status_id = 3 THEN 1 ELSE 0 END) AS approved,
					SUM(CASE WHEN bw.status_id = 6 THEN 1 ELSE 0 END) AS pending,
					SUM(CASE WHEN bw.status_id = 5 THEN 1 ELSE 0 END) AS reverted
				FROM batch_review_statuses bw
				JOIN batches b ON bw.batch_id = b.id
				JOIN claim_types ct ON b.claim_type_id = ct.id
				WHERE bw.user_id = ?
				{$dateFilter}
				AND ct.slug IN (
					'claim-for-catalogue-creation',
					'claim-for-accounts-management',
					'claim-for-logistics-and-transportation'
				)
				GROUP BY ct.slug
			", array_merge([$authId], $dateBindings));

			$response = [
				'catalogueClaim' => [
					'name' => 'Claim for Catalogue Creation',
					'approved' => 0,
					'pending' => 0,
					'reverted' => 0,
					'total' => 0
				],
				'accountClaim' => [
					'name' => 'Claim for Accounts Management',
					'approved' => 0,
					'pending' => 0,
					'reverted' => 0,
					'total' => 0
				],
				'logisticClaim' => [
					'name' => 'Claim for Logistics and Transportation',
					'approved' => 0,
					'pending' => 0,
					'reverted' => 0,
					'total' => 0
				],
			];

			$slugMap = [
				'claim-for-catalogue-creation'        => 'catalogueClaim',
				'claim-for-accounts-management'       => 'accountClaim',
				'claim-for-logistics-and-transportation' => 'logisticClaim',
			];

			foreach ($result as $row) {
				if (!isset($slugMap[$row->claimType])) {
					continue;
				}

				$key = $slugMap[$row->claimType];

				$approved = (int) $row->approved;
				$pending  = (int) $row->pending;
				$reverted = (int) $row->reverted;

				$response[$key] = [
					'name'     => $response[$key]['name'],
					'approved' => $approved,
					'pending'  => $pending,
					'reverted' => $reverted,
					'total'    => $approved + $pending + $reverted,
				];
			}

			return response()->json([
				'success'  => true,
				'data'     => $response,
				'type_for' => 1,
			]);
		}



		// Decide review column safely
		if (hasRole('ondc')) {
			$column = 'cs.ondc_review_status';
		} elseif (hasRole('nsic')) {
			$column = 'cs.nsic_review_status';
		} elseif (hasRole('nsicfinance')) {
			$column = 'cs.nsicfinance_review_status';
		} else {
			$column = 'cs.status';
		}

		$dateFilter   = '';
		$dateBindings = [];

		if (!empty($fromDate) && !empty($toDate)) {
			$dateFilter = " AND cs.created_at BETWEEN ? AND ?";
			$dateBindings[] = Carbon::parse($fromDate)->startOfDay();
			$dateBindings[] = Carbon::parse($toDate)->endOfDay();
		} elseif (!empty($year)) {
			$dateFilter = " AND YEAR(cs.created_at) = ?";
			$dateBindings[] = (int) $year;
		}

		$result = DB::select("
			SELECT 
				ct.slug AS claimType,
				ct.name AS claimTypeName,
				COUNT(DISTINCT cs.id) AS totalClaims,
				SUM(CASE WHEN {$column} = 6 THEN 1 ELSE 0 END) AS pendingCount,
				SUM(CASE WHEN {$column} = 3 THEN 1 ELSE 0 END) AS approvedCount,
				SUM(CASE WHEN {$column} = 5 THEN 1 ELSE 0 END) AS revertedCount,
				SUM(CASE WHEN {$column} = 4 THEN 1 ELSE 0 END) AS rejectedCount,
				SUM(CASE WHEN {$column} = 7 THEN 1 ELSE 0 END) AS paymentCompletedCount,
				(
					SUM(CASE WHEN {$column} = 6 THEN 1 ELSE 0 END) +
					SUM(CASE WHEN {$column} = 3 THEN 1 ELSE 0 END) +
					SUM(CASE WHEN {$column} = 5 THEN 1 ELSE 0 END) +
					SUM(CASE WHEN {$column} = 4 THEN 1 ELSE 0 END) +
					SUM(CASE WHEN {$column} = 7 THEN 1 ELSE 0 END)
				) AS totalStatusCount,
				COALESCE(SUM(cs.amount), 0) AS totalAmount
			FROM claim_types ct
			LEFT JOIN claims cs 
				ON cs.claim_type_id = ct.id
			AND cs.created_by = ?
			{$dateFilter}
			WHERE ct.slug IN (
				'claim-for-catalogue-creation',
				'claim-for-accounts-management',
				'claim-for-logistics-and-transportation'
			)
			GROUP BY ct.slug, ct.name
			ORDER BY ct.name ASC
		", array_merge([$authId], $dateBindings));

		return response()->json([
			'success' => true,
			'data'    => $result,
		]);*/
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
			$data->join('team_snp_scheme as snp', 'snpmap.snp_id', '=', 'snp.id');
			$data->whereIn('snp.user_id', getSubUserAndParentIds(authId()));
		}

		$data = $data
			->where('snpmap.status', 1)
			->whereYear('snpmap.created_at', $year)
			->groupByRaw('YEAR(snpmap.created_at), MONTH(snpmap.created_at)')
			->orderByRaw('MONTH(snpmap.created_at)')
			->get();  // ✅ Assign to $data (now a collection)

		// Ensure months with 0 count are included
		$months = collect([
			1 => 'Jan',
			2 => 'Feb',
			3 => 'Mar',
			4 => 'Apr',
			5 => 'May',
			6 => 'Jun',
			7 => 'Jul',
			8 => 'Aug',
			9 => 'Sep',
			10 => 'Oct',
			11 => 'Nov',
			12 => 'Dec'
		]);

		$result = $months->map(function ($name, $month) use ($data) {
			$found = $data->firstWhere('month', $month);
			return ['name' => $name, 'y' => $found->msme_count ?? 0];
		})->values();

		return response()->json($result);
	}

	public function getTopPerformerSnps(Request $request)
	{
		// $authId = (string) AuthId();
		/*$year = $request->filled('year') ? $request->year : now()->year;
		$query = DB::table('team_snp_scheme as tss')
			->join('team_snpmsme_mapping as tsm', 'tss.id', '=', 'tsm.snp_id')
			->select(
				'tss.snp_name',
				'tss.id as snp_id',
				DB::raw('COUNT(tsm.msme_id) as total')
			)
			->where('tsm.status', 1);

		if ($request->filled('from_date') && $request->filled('to_date')) {
			$query->whereBetween(DB::raw('DATE(tsm.created_at)'), [
				Carbon::parse($request->from_date)->startOfDay(),
				Carbon::parse($request->to_date)->endOfDay()
			]);

			if ($request->filled('year')) {
				$query->whereYear('tsm.created_at', $year);
			}
		} else {
			$query->whereYear('tsm.created_at', $year);
		}

		// if (hasRole('snp')) {
		// 	$query->where('tss.user_id', $authId);
		// }

		$result = $query
			->groupBy('tss.id', 'tss.snp_name')
			->orderByDesc('total')
			->limit(10)
			->get();

		return response()->json([
			'data' => $result
		]);*/

		$year = $request->filled('year') ? (int) $request->year : now()->year;

		$query = DB::table('team_snp_scheme as tss')
			->join('team_snpmsme_mapping as tsm', 'tss.id', '=', 'tsm.snp_id')
			->select(
				'tss.snp_name',
				'tss.id as snp_id',
				DB::raw('COUNT(tsm.msme_id) as total')
			)
			->where('tsm.status', 1);

		/*
|--------------------------------------------------------------------------
| Date filter (either date range OR year)
|--------------------------------------------------------------------------
*/
		if ($request->filled('from_date_new') && $request->filled('to_date_new')) {

			$query->whereBetween('tsm.created_at', [
				Carbon::parse($request->from_date_new)->startOfDay(),
				Carbon::parse($request->to_date_new)->endOfDay(),
			]);
		} else {

			$query->whereYear('tsm.created_at', $year);
		}

		/*
|--------------------------------------------------------------------------
| SNP role filter (optional)
|--------------------------------------------------------------------------
*/
		// if (hasRole('snp')) {
		//     $query->where('tss.user_id', $authId);
		// }

		/*
|--------------------------------------------------------------------------
| Group, order & limit
|--------------------------------------------------------------------------
*/
		$result = $query
			->groupBy('tss.id', 'tss.snp_name')
			->orderByDesc('total')
			->limit(10)
			->get();

		return response()->json([
			'data' => $result,
		]);
	}


	public function getMsmeGenderPercantege(Request $request)
	{
		// $authId = (string) AuthId(); 
		/*$query = DB::table('team_msme_schemes as ms')
			->join('team_snpmsme_mapping as tsm', 'tsm.msme_id', '=', 'ms.id')
			->join('team_snp_scheme as tss', 'tsm.snp_id', '=', 'tss.id')
			->selectRaw('
				COALESCE(ms.gender, "Not Specified") AS gender,
				COUNT(*) AS total
			')
			->where('tsm.status', 1);

		// if (hasRole('snp')) {
		//     $query->where('tss.user_id', $authId);
		// }

		if ($request->filled('from_date') && $request->filled('to_date')) {
			$query->whereBetween(DB::raw('DATE(tsm.created_at)'), [
				Carbon::parse($request->from_date)->startOfDay(),
				Carbon::parse($request->to_date)->endOfDay()
			]);
		} elseif ($request->filled('year')) {
			$query->whereYear('tsm.created_at', $request->year);
		} else {
			$query->whereYear('tsm.created_at', now()->year);
		}

		$query->groupBy('ms.gender');

		$result = $query->get();

		$total = $result->sum('total');

		$data = $result->map(function ($row) use ($total) {
			$percentage = $total > 0 ? round(($row->total / $total) * 100, 2) : 0;
			return [
				'name' => ucfirst($row->gender),
				'count' => (int) $row->total,
				'percentage' => $percentage
			];
		});

		return response()->json([
			'data' => $data,
			'total' => $total
		]);*/
		$query = DB::table('team_msme_schemes as ms')
			->join('team_snpmsme_mapping as tsm', 'tsm.msme_id', '=', 'ms.id')
			->join('team_snp_scheme as tss', 'tsm.snp_id', '=', 'tss.id')
			->selectRaw('
        COALESCE(ms.gender, "Not Specified") AS gender,
        COUNT(*) AS total
    ')
			->where('tsm.status', 1);

		/*
|--------------------------------------------------------------------------
| Date filter (either date range OR year)
|--------------------------------------------------------------------------
*/
		if ($request->filled('from_date_new') && $request->filled('to_date_new')) {

			$query->whereBetween('tsm.created_at', [
				Carbon::parse($request->from_date_new)->startOfDay(),
				Carbon::parse($request->to_date_new)->endOfDay(),
			]);
		} else {

			$year = $request->filled('year') ? (int) $request->year : now()->year;
			$query->whereYear('tsm.created_at', $year);
		}

		/*
|--------------------------------------------------------------------------
| Group & fetch
|--------------------------------------------------------------------------
*/
		$result = $query
			->groupBy('ms.gender')
			->get();

		/*
|--------------------------------------------------------------------------
| Totals & percentages
|--------------------------------------------------------------------------
*/
		$total = (int) $result->sum('total');

		$data = $result->map(function ($row) use ($total) {
			$percentage = $total > 0
				? round(($row->total / $total) * 100, 2)
				: 0;

			return [
				'name' => ucfirst(strtolower($row->gender)),
				'count' => (int) $row->total,
				'percentage' => $percentage,
			];
		});

		return response()->json([
			'data' => $data,
			'total' => $total,
		]);
	}

	  /**
     * Check if sub-user has required permissions
     */
    private function checkSubUserPermissions(): bool
    {
        $userId = (string) authId();
        
        // Check if user is a sub-user
        $isSubUser = DB::table('users')
            ->where('id', $userId)
            ->value('is_sub_user');
            
        if (!$isSubUser) {
            return true; // Not a sub-user, proceed normally
        }
        
        // Check if sub-user has any explicit permissions
        $hasPermissions = DB::table('user_permissions')
            ->where('user_id', $userId)
            ->exists();
            
        if (!$hasPermissions) {
            return false; // Sub-user has no permissions
        }
        
        return true; // Sub-user has permissions
    }
}
//added the total field