<?php

declare(strict_types=1);

namespace App\Web\MseBulkRegistration\FailedMsmeList;

use App\Http\Controllers\ClientController;
use Illuminate\Http\JsonResponse;
use Illuminate\View\View;

/**
 * Failed MSME list.
 *
 * Lists the bulk-upload drafts the pending cron has permanently given up on
 * (status = Failed after MseDraftBulkUploadService::MAX_API_ATTEMPTS attempts).
 *
 * Follows the same Controller -> Service/Action -> Resource -> Blade layout and
 * the same dataTableInit() list UI used by msme/index and ia/registered-msme.
 */
class FailedMsmeListController extends ClientController
{
    /**
     * Failed MSME list screen.
     *
     * Filter options are rendered server-side and made searchable with select2,
     * matching the existing filter-bar pattern in msme/index.blade.php.
     */
    public function index(FailedMsmeListService $service): View
    {
        return view('msme.FailedMsmeList.index', [
            'title'   => 'Failed MSME List',
            'summary' => $service->getSummary(),
            'snpList' => $service->getSnpOptions(),
            'iaList'  => $service->getIaOptions(),
        ]);
    }

    /**
     * Server-side DataTable feed.
     *
     * dataTableInit() reads json.data.draw / json.data.data, so the payload is
     * wrapped by the shared Respond::success() helper.
     */
    public function datalist(FailedMsmeListAction $action): JsonResponse
    {
        try {
            return $this->success($action->execute(), 'Failed MSME list fetched successfully.');
        } catch (\Throwable $e) {
            \Log::error('Failed MSME list fetch failed: ' . $e->getMessage());

            // Keep the DataTables envelope so the grid renders its empty state
            // instead of breaking, and surface a message for the error handler.
            return response()->json([
                'status'  => false,
                'message' => 'Unable to load failed MSME records. Please try again.',
                'data'    => [
                    'draw'            => (int) request()->input('draw'),
                    'recordsTotal'    => 0,
                    'recordsFiltered' => 0,
                    'data'            => [],
                ],
            ], 500);
        }
    }
}
