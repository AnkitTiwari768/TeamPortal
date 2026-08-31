<?php

declare(strict_types=1);

namespace App\Web\Dashboard;

use App\Http\Controllers\ClientController;
use Illuminate\Http\Request;


/**
 * Dashboard controller dedicated to the NSIC role.
 *
 * Responsibilities:
 *  - Fetch MSE summary data via DashboardService (same service as other roles).
 *  - Return dashboard.nsic view for normal requests.
 *  - Return JSON card data for AJAX card-reload requests.
 *
 * Roles that land here  : nsic
 * Roles NOT here        : nsic-finance, mo-mse, ca  → still use dashboard.others
 */
final class NsicDashboardController extends ClientController
{
    use DashboardTrait;

    public function __construct(
        private DashboardService      $service,
        private AdminDashboardService $adminService
    ) {}

    /*
    |--------------------------------------------------------------------------
    | Main page + AJAX card-reload (same endpoint, detected via $request->ajax())
    |--------------------------------------------------------------------------
    */
    public function index(Request $request)
    {
        [$selectedYear, $fromDate, $toDate, $type] = $this->resolveFilters($request);

        $data = $this->buildMseDashboardData($selectedYear, $fromDate, $toDate, $type);

        if ($request->ajax()) {
            return response()->json($data);
        }

        return view('dashboard.nsic', array_merge($data, ['selectedYear' => $selectedYear]))
            ->with('title', __('message.dashboard_list'));
    }

    /*
    |--------------------------------------------------------------------------
    | Helpers
    |--------------------------------------------------------------------------
    */

    /**
     * Resolve year / date-range / type filter from the incoming request.
     *
     * @return array{0: string|null, 1: string|null, 2: string|null, 3: int}
     */
    private function resolveFilters(Request $request): array
    {
        // Default type = 1 (all-time, no date filter) when not supplied.
        $type = $request->type !== null ? (int) $request->type : 1;
        $fromDate = null;
        $toDate = null;
        $selectedYear = null;

        if (!empty($request->from_date_new) && !empty($request->to_date_new)) {
            $fromDate = $request->from_date_new;
            $toDate = $request->to_date_new;
        } elseif (!empty($request->year)) {
            $selectedYear = $request->year;
        }

        return [$selectedYear, $fromDate, $toDate, $type];
    }

    /**
     * Fetch all data needed for the MSE stat cards.
     * Uses the same DashboardService methods as the 'others' role branch.
     */
    private function buildMseDashboardData(
        ?string $year,
        ?string $fromDate,
        ?string $toDate,
        ?int $type
    ): array {
        // NSIC-specific claim filter columns
        $status = 'nsic_review_status';
        $sentStatus = 'is_sent_nsic';

        $fundMetrics = null;
        if (acl(config('permissions.allocation-view')) || acl(config('permissions.fund-distribution-view'))) {
            $fundMetrics = $this->service->getFundManagementMetricsData($year, $fromDate, $toDate, $type);
        }

        return [
            'fundMetrics'          => $fundMetrics,
            'registeredMsmeCounts' => $this->service->getOthersRegisteredMsmeCount($year, $fromDate, $toDate, $type),
            'msmeOpenCounts'       => $this->service->getSnpOtherRolesOpenMsmeCount($year, $fromDate, $toDate, $type),
            'msmechoosenCounts'    => $this->service->getSnpOtherRolesChoosenMsmeCount($year, $fromDate, $toDate, $type),
            'msmeOnboardedCounts'  => $this->service->getSnpOtherRolesOnboardedCount($year, $fromDate, $toDate, $type),
            'claimCounts'          => $this->service->getOtherClaimCountById($status, $sentStatus, $year, $fromDate, $toDate),
            // NP Registration Counts (filter-aware, via AdminDashboardService)
            'snpCount'             => $this->adminService->getSnpRegistrationCount($year, $fromDate, $toDate, $type),
            'bnpCount'             => $this->adminService->getBnpRegistrationCount($year, $fromDate, $toDate, $type),
            'lspCount'             => $this->adminService->getLspRegistrationCount($year, $fromDate, $toDate, $type),
            'associationsCount'    => $this->adminService->getAssociationsCount($year, $fromDate, $toDate, $type),

            'total_batches_submitted_by_np' => $this->getTotalSubmittedByNPs($year, $fromDate, $toDate, $type, null),
            'total_claims_submitted_by_np' => $this->getTotalClaimsSubmittedByNPs($year, $fromDate, $toDate, $type, null),
            'total_batches_pending_with_ondc' => $this->getTotalBatchesPendingWithONDC($year, $fromDate, $toDate, $type, null),
            'total_claims_pending_with_ondc' => $this->getTotalClaimsPendingWithONDC($year, $fromDate, $toDate, $type, null),
            'total_batches_pending_with_nsic' => $this->getTotalBatchesPendingWithNSIC($year, $fromDate, $toDate, $type, null),
            'total_claims_pending_with_nsic' => $this->getTotalClaimsPendingWithNSIC($year, $fromDate, $toDate, $type, null),
            'total_batches_pending_with_nps' => $this->getTotalBatchesPendingWithNPs($year, $fromDate, $toDate, $type, null),
            'total_claims_pending_with_nps' => $this->getTotalClaimsPendingWithNPs($year, $fromDate, $toDate, $type, null),
            'total_amount_pending_with_nps' => $this->getTotalAmountPendingWithNPs($year, $fromDate, $toDate, $type, null),
            'total_batches_pending_with_finance' => $this->getTotalBatchesPendingWithFinance($year, $fromDate, $toDate, $type, null),
            'total_claims_pending_with_finance' => $this->getTotalClaimsPendingWithFinance($year, $fromDate, $toDate, $type, null),
            'total_approved_batches' => $this->getTotalApprovedBatches($year, $fromDate, $toDate, $type, null),
            'total_approved_claims' => $this->getTotalApprovedClaims($year, $fromDate, $toDate, $type, null),
            'total_rejected_batches' => $this->getTotalRejectedBatches($year, $fromDate, $toDate, $type, null),
            'total_rejected_claims' => $this->getTotalRejectedClaims($year, $fromDate, $toDate, $type, null),
            'total_amount_submitted_by_nps' => $this->getTotalAmountSubmittedByNPs($year, $fromDate, $toDate, $type, null),
            'total_amount_pending_with_ondc' => $this->getTotalAmountPendingWithONDC($year, $fromDate, $toDate, $type, null),
            'total_amount_pending_with_nsic' => $this->getTotalAmountPendingWithNSIC($year, $fromDate, $toDate, $type, null),
            'total_amount_pending_with_finance' => $this->getTotalAmountPendingWithFinance($year, $fromDate, $toDate, $type, null),
            'total_amount_approved' => $this->getTotalApprovedAmount($year, $fromDate, $toDate, $type, null),
            'total_amount_rejected' => $this->getTotalRejectedAmount($year, $fromDate, $toDate, $type, null),
            'total_amount_pending' => $this->getTotalAmountPending($year, $fromDate, $toDate, $type, null),
            'total_batches_pending' => $this->getTotalBatchesPending($year, $fromDate, $toDate, $type, null),
            'total_claims_pending' => $this->getTotalClaimsPending($year, $fromDate, $toDate, $type, null),
            'total_payment_completed_claims' => $this->getTotalPaymentCompletedClaims($year, $fromDate, $toDate, $type, null),
            'total_payment_completed_batches' => $this->getTotalPaymentCompletedBatches($year, $fromDate, $toDate, $type, null),
            'total_amount_payment_completed' => $this->getTotalPaymentCompletedAmount($year, $fromDate, $toDate, $type, null),
        ];
    }
}
