<?php

declare(strict_types=1);

namespace App\Domain\FundAllocation;

use Illuminate\Http\Request;
use App\Core\BaseRequest;
use App\Traits\HasAttribute;
use App\Traits\HasFileUpload;
use App\Traits\Respond;
use Illuminate\Support\Facades\Validator;
use App\Domain\FundAllocation\FundAllocationRequest;
use App\Domain\FundAllocation\FundAllocationDTO;
use App\Domain\FundAllocation\StoreFundAllocationAction;
use App\Domain\FundAllocation\ListFundAllocationAction;

class FundAllocationController
{
    use HasAttribute, HasFileUpload, Respond;

    private static string $module = 'fund-allocation';

    public function __construct(private FundAllocationService $service) {}

    public function getDataTable()
    {
        return $this->success(data: app(ListFundAllocationAction::class)->execute());
    }

    public function index()
    {
        return view('fund_flow.fund_allocation.index')->with([
            'title' => __('fund_flow.fund_allocation'),
            'financialYears' => financial_year(),
            'components' => $this->listOf(code: 'major-components', skipParent: true),
            'duration' => $this->listOf(code: 'duration', skipParent: true),
            'module_url' => static::$module
        ]);
    }

    public function create()
    {
        return view('fund_flow.fund_allocation.form')->with([
            'title' => __('fund_flow.fund_allocation'),
            'financialYears' => financial_year(),
            'components' => $this->listOf(code: 'major-components', skipParent: true),
            'duration' => $this->listOf(code: 'duration', skipParent: true),
            'module_url' => static::$module
        ]);
    }

    public function uploadDocuments(Request $request)
    {
        $validator = Validator::make(
            $request->all(),
            BaseRequest::getCommonFileRules(5000),
            BaseRequest::getCommonFileRulesMessages()
        );

        if ($validator->fails()) {
            return $this->error($validator->errors());
        }

        $uploaded = $this->uploadFile(
            $request->file('file'),
            config('upload.allocation_document_path', 'allocation-documents')
        );

        return $this->success(message: __('fund_flow.file_upload_success'), data: [
            'uploaded_ids' => $uploaded->id
        ]);
    }

    public function store(FundAllocationRequest $request, StoreFundAllocationAction $action, ?string $id = null)
    {
        try {
            $dto = FundAllocationDTO::fromArray($request->validated());
            $allocation = $action->execute($dto, $id);

            return $this->created($allocation, __('fund_flow.fund_allocation_saved'));
        } catch (\RuntimeException $e) {
            return $this->error(['generic' => [$e->getMessage()]], $e->getMessage());
        }
    }

    public function destroy(string $id, DeleteFundAllocationAction $action)
    {
        try {
            $action->execute($id);

            return $this->success(null, __('fund_flow.fund_allocation_deleted'));
        } catch (\RuntimeException $e) {
            return $this->error(['generic' => [$e->getMessage()]], $e->getMessage());
        }
    }

    public function edit(string $id)
    {
        $data = $this->service->getFundAllocationEditDetails($id);

        return view('fund_flow.fund_allocation.form')->with([
            'title' => __('fund_flow.edit_allocation'),
            'financialYears' => financial_year(),
            'components' => $this->listOf(code: 'major-components', skipParent: true),
            'duration' => $this->listOf(code: 'duration', skipParent: true),
            'module_url' => static::$module,
            'row' => $data['fundAllocation'],
            'lines' => $data['componentMappings'],
            'docUrl' => $data['documentUrl'],
            'id' => $id,
        ]);
    }

    public function show(string $id)
    {
        $data = $this->service->getFundAllocationViewDetails($id);

        return view('fund_flow.fund_allocation.view')->with([
            'title' => __('fund_flow.fund_allocation_details'),
            'row' => $data['fundAllocation'],
            'duration_name' => $data['fundAllocation']['duration_name'] ?? '-',
            'sub_duration_name' => $data['fundAllocation']['sub_duration_name'] ?? '-',
            'lines' => $data['componentMappings'],
            'history' => $data['allocationHistory'],
            'docUrl' => $data['documentUrl'],
            'module_url' => static::$module,
        ]);
    }

    public function getAttributeValues(Request $request, string $attributeId)
    {
        $values = $this->service->getAttributeValues($attributeId, $request->parent_id);
        return response()->json(['data' => $values]);
    }

    public function viewDocument(string $id)
    {
        try {
            $details = $this->service->getDocumentDetails($id);
            if (!$details) {
                abort(404, 'No document attached to this allocation.');
            }

            $path = base_path($details['file_path']);
            if (!file_exists($path)) {
                abort(404, 'Document file not found on server.');
            }

            return response()->file($path);
        } catch (\Exception $e) {
            \Log::error('Error viewing allocation document:', ['error' => $e->getMessage()]);
            abort(404);
        }
    }

    public function downloadDocument(string $id)
    {
        try {
            $details = $this->service->getDocumentDetails($id);
            if (!$details) {
                abort(404, 'No document attached to this allocation.');
            }

            $path = base_path($details['file_path']);
            if (!file_exists($path)) {
                abort(404, 'Document file not found on server.');
            }

            return response()->download($path, $details['file_name']);
        } catch (\Exception $e) {
            \Log::error('Error downloading allocation document:', ['error' => $e->getMessage()]);
            abort(404);
        }
    }

    public function getPeriodSummary(Request $request)
    {
        $financialYear = (string) $request->query('financial_year', '');
        $durationId = (string) $request->query('duration_id', '');
        $subDurationId = $request->query('sub_duration_id') ? (string) $request->query('sub_duration_id') : null;
        $allocationId = $request->query('allocation_id') ? (string) $request->query('allocation_id') : null;

        $summary = $this->service->getPeriodSummary($financialYear, $durationId, $subDurationId, $allocationId);

        return $this->success(data: $summary);
    }
    public function getComponentBalance(Request $request)
    {
        $financialYear = (string) $request->query('financial_year', '');
        $durationId = (string) $request->query('duration_id', '');
        $subDurationId = $request->query('sub_duration_id') ? (string) $request->query('sub_duration_id') : null;
        $majorComponentId = (string) $request->query('major_component_id', '');
        $subComponentId = $request->query('sub_component_id') ? (string) $request->query('sub_component_id') : null;

        // FundAllocationService::getComponentBalance() already returns the correct
        // "Current Allocation" (direct allocation + received via Component Utilization
        // Mapping - transferred away - distributed) in both 'remaining_balance' (display)
        // and 'already_allocated' (feeds the Opening Balance arithmetic) -- no further
        // override needed here.
        $balanceData = $this->service->getComponentBalance(
            $financialYear,
            $durationId,
            $subDurationId,
            $majorComponentId,
            $subComponentId
        );

        return $this->success(data: $balanceData);
    }

    public function checkPreviousBalance(Request $request)
    {
        $financialYear = (string) $request->query('financial_year', '');
        $durationId = (string) $request->query('duration_id', '');
        $subDurationId = $request->query('sub_duration_id') ? (string) $request->query('sub_duration_id') : null;

        $res = $this->service->checkPreviousRemainingBalance($financialYear, $durationId, $subDurationId);

        return $this->success(data: $res);
    }

    public function getYearlyNoteBalance(Request $request)
    {
        $financialYear = (string) $request->query('financial_year', '');
        
        $res = $this->service->getYearlyNoteBalance($financialYear);

        return $this->success(data: $res);
    }
}
