<?php

declare(strict_types=1);

namespace App\Web\FundDistribution;

use App\Http\Controllers\ClientController;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Illuminate\Support\Facades\Validator;
use App\Web\Allocation\AllocationService;
use App\Web\Allocation\PoolService;
use App\Traits\HasFileUpload;
use App\Core\BaseRequest;

class DistributionController extends ClientController
{
    use HasFileUpload;

    public function __construct(
        private DistributionService $service,
        private AllocationService $allocService
    ) {
    }

    /**
     * Render Enterprise Listing (Grouped)
     */
    public function index(): View
    {
        guard(config('permissions.fund-distribution-view'));

        $title = __('Fund Distribution Summary');

        // Fetch real major components for filter directly via service integration
        $majorComponents = $this->allocService->getMajorComponents();

        return view('web.distribution.index', compact('title', 'majorComponents'));
    }

    /**
     * Dynamic Data Provider (Listing)
     */
    public function getSummaryList()
    {
        guard(config('permissions.fund-distribution-view'));

        return $this->success(
            $this->service->getAggregatedSummaryList()
        );
    }

    /**
     * Dynamic Drill Down View Renderer
     */
    public function showDetails(string $fy, string $majorId, string $subId = 'NULL'): View
    {
        guard(config('permissions.fund-distribution-view'));

        $data = $this->service->getDrillDownGroupDetails($fy, $majorId, $subId);
        $title = __('Distribution Analysis');

        return view('web.distribution.view', array_merge(
            compact('title', 'fy', 'majorId', 'subId'),
            $data
        ));
    }

    /**
     * Advanced Transactions List Generator
     */
    public function getGroupTransactions(string $fy, string $majorId, string $subId = 'NULL')
    {
        guard(config('permissions.fund-distribution-view'));

        return $this->success(
            $this->service->getRawTransactionsList($fy, $majorId, $subId)
        );
    }

    /**
     * Allocation Creator Canvas
     */
    public function create(): View
    {
        guard(config('permissions.fund-distribution-create'));

        $title = __('New Fund Distribution');
        $majorComponents = $this->allocService->getMajorComponents();
        $durations = $this->allocService->getDurations();

        return view('web.distribution.create', compact('title', 'majorComponents', 'durations'));
    }

    /**
     * Individual Transaction Inspection View
     */
    public function show(string $id): View
    {
        guard(config('permissions.fund-distribution-view'));

        $row = $this->service->getRecord($id);
        if (!$row)
            abort(404, 'Transaction missing or revoked.');

        $title = __('Transaction View Detailed');

        // Additional metadata enhancement
        $row->major_component_name = \Illuminate\Support\Facades\DB::table('attribute_values')->where('id', $row->major_component_id)->value('attribute_value') ?? 'N/A';
        $row->sub_component_name = $row->sub_component_id ? (\Illuminate\Support\Facades\DB::table('attribute_values')->where('id', $row->sub_component_id)->value('attribute_value') ?? 'N/A') : 'N/A';
        $row->duration_name = $row->duration_id ? (\Illuminate\Support\Facades\DB::table('attribute_values')->where('id', $row->duration_id)->value('attribute_value') ?? 'Consolidated') : 'Consolidated';
        $row->sub_duration_name = $row->sub_duration_id ? (\Illuminate\Support\Facades\DB::table('attribute_values')->where('id', $row->sub_duration_id)->value('attribute_value') ?? 'N/A') : 'N/A';

        return view('web.distribution.show', compact('title', 'row'));
    }

    /**
     * Stream inline viewable file content, matching standard enterprise allocation standards
     */
    public function viewDocument(string $id)
    {
        guard(config('permissions.fund-distribution-view'));

        try {
            $row = $this->service->getRecord($id);
            if (!$row || empty($row->doc_link)) { // Note: doc_link captures file_path in my join
                abort(404, 'Record has no active file attachment.');
            }

            $path = base_path($row->doc_link);
            if (!file_exists($path)) {
                abort(404, 'Physical file not located.');
            }

            return response()->file($path);
        } catch (\Exception $e) {
            abort(404);
        }
    }

    /**
     * Dynamic Modification Canvas
     */
    public function edit(string $id): View
    {
        guard(config('permissions.fund-distribution-create'));

        $row = $this->service->getRecord($id);
        if (!$row)
            abort(404, 'Record unavailable.');

        $title = __('Edit Fund Distribution');

        $majorComponents = $this->allocService->getMajorComponents();
        $durations = $this->allocService->getDurations();

        // Pre-resolve existing cascading lists to stabilize static dropdown load
        $subComponents = $this->allocService->getSubComponents($row->major_component_id);
        $subDurations = [];
        if ($row->duration_id) {
            $subDurations = $this->allocService->getAttributeValues(
                config('allocation.duration_code', 'duration'),
                $row->duration_id
            );
        }

        return view('web.distribution.edit', compact(
            'title',
            'row',
            'majorComponents',
            'durations',
            'subComponents',
            'subDurations'
        ));
    }

    /**
     * Safe State Modifying Entry-Point
     */
    public function store(Request $request, ?string $id = null)
    {
        guard(config('permissions.fund-distribution-create'));

        $validator = Validator::make(
            $request->all(),
            DistributionRequest::getRules(),
            DistributionRequest::messages()
        );

        if ($validator->fails()) {
            return $this->error($validator->errors());
        }

        try {
            $result = $this->service->processSafeStore($validator->validated(), $id);
            return $this->success($result, "Successfully " . ($id ? 'updated' : 'created') . " distribution impact record.");
        } catch (\Exception $e) {
            return $this->error(['fatal' => $e->getMessage()], 422);
        }
    }

    /**
     * Safe Destroy (Soft-Delete Wrapper)
     */
    public function destroy(string $id)
    {
        guard(config('permissions.fund-distribution-create')); // Requires high perm to rollback

        $status = $this->service->performSafeDelete($id);

        return $status
            ? $this->success(true, "Successfully reversed distribution transaction.")
            : $this->error(["generic" => "Record lookup failed"]);
    }

    /**
     * High Performance Dynamic Real-time Pool Fetch API
     */
    public function getRealTimePoolData(Request $request)
    {
        guard(config('permissions.fund-distribution-view'));

        $poolService = app(PoolService::class);

        $keyData = [
            'financial_year' => $request->input('financial_year'),
            'duration_id' => $request->input('duration_id'),
            'sub_duration_id' => (empty($request->input('sub_duration_id')) || strtolower($request->input('sub_duration_id')) == 'null') ? null : $request->input('sub_duration_id'),
            'major_component_id' => $request->input('major_component_id'),
            'sub_component_id' => (empty($request->input('sub_component_id')) || strtolower($request->input('sub_component_id')) == 'null') ? null : $request->input('sub_component_id'),
        ];

        $key = $poolService->resolvePoolKey($keyData);
        $pool = $poolService->findPool($key);

        if (!$pool) {
            return response()->json([
                'found' => false,
                'message' => 'No allocation detected.'
            ]);
        }

        return response()->json([
            'found' => true,
            'total_allocated' => (float) $pool->total_allocated_amount,
            'total_distributed' => (float) $pool->total_distributed_amount,
            'remaining_balance' => (float) $pool->remaining_balance
        ]);
    }

    /**
     * Document handling hook (isolated endpoint map)
     */
    public function uploadAsset(Request $request)
    {
        guard(config('permissions.fund-distribution-create'));

        return $this->uploadFileWithValidation(
            $request,
            'file',
            config('upload.allocation_document_path'),
            BaseRequest::getCommonFileRules(5000),
            BaseRequest::getCommonFileRulesMessages()
        );
    }
}
