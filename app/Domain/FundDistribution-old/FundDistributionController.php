<?php

declare(strict_types=1);

namespace App\Domain\FundDistribution;

use App\Core\BaseRequest;
use App\Traits\HasAttribute;
use App\Traits\HasFileUpload;
use App\Traits\Respond;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use App\Domain\FundDistribution\FundDistributionRequest;
use App\Domain\FundDistribution\FundDistributionDTO;
use App\Domain\FundDistribution\StoreFundDistributionAction;
use App\Domain\FundDistribution\DeleteFundDistributionAction;
use App\Domain\FundDistribution\ListFundDistributionAction;
use App\Domain\FundDistribution\ListFundDistributionTransactionsAction;

class FundDistributionController
{
    use HasAttribute, HasFileUpload, Respond;

    private static string $module = 'fund-distributions';

    public function __construct(private FundDistributionService $service) {}

    public function index()
    {
        guard('fund-distribution-view');

        return view('fund_flow.fund_distribution.index')->with([
            'title' => __('fund_flow.fund_distribution_list'),
            'financialYears' => financial_year(),
            'components' => $this->listOf(code: 'major-components', skipParent: true),
            'duration' => $this->listOf(code: 'duration', skipParent: true),
            'module_url' => static::$module
        ]);
    }

    public function getSummaryList()
    {
        guard('fund-distribution-view');

        return $this->success(
            app(ListFundDistributionAction::class)->execute()
        );
    }

    public function showDetails(string $fy, string $majorId, ?string $subId = 'NULL')
    {
        guard('fund-distribution-view');

        $data = $this->service->getDrillDownGroupDetails($fy, $majorId, $subId);

        return view('fund_flow.fund_distribution.view')->with(array_merge([
            'title' => __('fund_flow.fund_distribution_details'),
            'fy' => $fy,
            'majorId' => $majorId,
            'subId' => $subId,
            'module_url' => static::$module,
        ], $data));
    }

    public function getGroupTransactions(string $fy, string $majorId, ?string $subId = 'NULL')
    {
        guard('fund-distribution-view');

        return $this->success(
            app(ListFundDistributionTransactionsAction::class)->execute($fy, $majorId, $subId)
        );
    }

    public function create()
    {
        guard('fund-distribution-create');

        return view('fund_flow.fund_distribution.form')->with([
            'title' => __('fund_flow.add_fund_distribution'),
            'financialYears' => financial_year(),
            'majorComponents' => $this->service->getAttributeValues(codeOrId: 'major-components'),
            'durations' => $this->service->getAttributeValues(codeOrId: 'duration'),
            'module_url' => static::$module
        ]);
    }

    public function show(string $id)
    {
        guard('fund-distribution-view');

        $data = $this->service->getFundDistributionViewDetails($id);

        return view('fund_flow.fund_distribution.show')->with(array_merge([
            'title' => __('fund_flow.fund_distribution_details'),
            'module_url' => static::$module,
        ], $data));
    }

    public function viewDocument(string $id)
    {
        guard('fund-distribution-view');

        try {
            $details = $this->service->getDocumentDetails($id);
            if (!$details) {
                abort(404, 'No document attached to this distribution.');
            }

            $path = base_path($details['file_path']);
            if (!file_exists($path)) {
                abort(404, 'Document file not found on server.');
            }

            return response()->file($path);
        } catch (\Exception $e) {
            \Log::error('Error viewing distribution document:', ['error' => $e->getMessage()]);
            abort(404);
        }
    }

    public function edit(string $id)
    {
        guard('fund-distribution-create');

        $data = $this->service->getFundDistributionEditDetails($id);

        return view('fund_flow.fund_distribution.form')->with([
            'title' => __('fund_flow.edit_fund_distribution'),
            'financialYears' => financial_year(),
            'majorComponents' => $this->service->getAttributeValues(codeOrId: 'major-components'),
            'durations' => $this->service->getAttributeValues(codeOrId: 'duration'),
            'module_url' => static::$module,
            'row' => $data['fundDistribution'],
            'subComponents' => $data['subComponents'],
            'subDurations' => $data['subDurations'],
            'docUrl' => $data['documentUrl'],
            'id' => $id,
        ]);
    }

    public function store(FundDistributionRequest $request, StoreFundDistributionAction $action, ?string $id = null)
    {
        guard('fund-distribution-create');

        try {
            $dto = FundDistributionDTO::fromArray($request->validated());
            $distribution = $action->execute($dto, $id);
            return $this->created($distribution, __('fund_flow.fund_distribution_saved'));
        } catch (\Exception $e) {
            return $this->error(['distribution_amount' => [$e->getMessage()]], $e->getMessage());
        }
    }

    public function destroy(string $id, DeleteFundDistributionAction $action)
    {
        guard('fund-distribution-create');

        $status = $action->execute($id);

        return $status
            ? $this->success(null, __('fund_flow.fund_distribution_deleted'))
            : $this->error(["generic" => "Record lookup failed"]);
    }

    public function getRealTimePoolData(Request $request)
    {
        if (!acl('fund-distribution-view') && !acl('fund-distribution-create')) {
            abort(403);
        }

        $poolService = app(\App\Web\Allocation\PoolService::class);

        $fy = (string) $request->input('financial_year');
        $durationId = (string) $request->input('duration_id');
        $subDurationId = (empty($request->input('sub_duration_id')) || strtolower((string)$request->input('sub_duration_id')) == 'null') ? null : (string)$request->input('sub_duration_id');
        $majorComponentId = (string) $request->input('major_component_id');
        $subComponentId = (empty($request->input('sub_component_id')) || strtolower((string)$request->input('sub_component_id')) == 'null') ? null : (string)$request->input('sub_component_id');

        $keyData = [
            'financial_year' => $fy,
            'duration_id' => $durationId,
            'sub_duration_id' => $subDurationId,
            'major_component_id' => $majorComponentId,
            'sub_component_id' => $subComponentId,
        ];

        $key = $poolService->resolvePoolKey($keyData);
        $pool = $poolService->findPool($key);

        $utilizationAmount = $poolService->getComponentUtilizationAmount(
            $fy, 
            $durationId, 
            $subDurationId, 
            $majorComponentId, 
            $subComponentId
        );

        if (!$pool && $utilizationAmount <= 0) {
            return response()->json([
                'found' => false,
                'message' => 'No allocation detected.'
            ]);
        }

        $baseAllocated = $pool ? (float) $pool->total_allocated_amount : 0.0;
        $baseDistributed = $pool ? (float) $pool->total_distributed_amount : 0.0;
        $baseRemaining = $pool ? (float) $pool->remaining_balance : 0.0;

        $totalAllocated = $baseAllocated;
        $effectiveRemaining = $baseRemaining + $utilizationAmount;

        return response()->json([
            'found' => true,
            'total_allocated' => $totalAllocated,
            'total_distributed' => $baseDistributed,
            'remaining_balance' => $effectiveRemaining,
            'utilization_amount' => $utilizationAmount,
        ]);
    }

    public function uploadAsset(Request $request)
    {
        guard('fund-distribution-create');

        return $this->uploadFileWithValidation(
            $request,
            'file',
            config('upload.allocation_document_path', 'allocation-documents'),
            BaseRequest::getCommonFileRules(5000),
            BaseRequest::getCommonFileRulesMessages()
        );
    }
}
