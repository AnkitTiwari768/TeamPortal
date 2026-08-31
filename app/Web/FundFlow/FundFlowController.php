<?php

declare(strict_types=1);

namespace App\Web\FundFlow;

use Illuminate\View\View;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use App\Http\Controllers\ClientController;
use App\Traits\HasFileUpload;
use App\Core\BaseRequest;


class FundFlowController extends ClientController
{
    use HasFileUpload;
    private static string $module = 'fund-allocations';

    public function __construct(private FundFlowService $service) {}


  
    public function index(): View
    {
        guard('fund-allocation-view');    
        return view('fund_flow.index')
            ->with('title', 'Allocation List');
    }

    public function getAllocationList()
    {
        return $this->success($this->service->getAllocationList());
    }

    public function getDetails($id): View
    {
        $title = __('Allocation Detail');    
        $row = (array) $this->service->getAllocationDetail($id);
        $module_url = static::$module;
        //dd($row);
		return view('fund_flow.details', compact('title', 'id' ,'row','module_url')); 
    }
    

    public function create(): View
    {
        guard('fund-allocation-create');    
        $title = __('Add Allocation');
        $module_url = static::$module;
        $details = (object) $this->service->getDetails();
        return view('fund_flow.allocation_form', compact('title', 'module_url','details'));
        
    }

    public function edit(string $id): View
    {
        guard('fund-allocation-create');    
        $title = __('Edit Allocation');
        $module_url = static::$module;
        $details = (object) $this->service->getDetails();
        $row = (array) $this->service->getAllocationDetail($id);
        //dd($row);
        return view('fund_flow.allocation_form', compact('title', 'module_url','details','row'));
        
    }

    public function uploadAllocationdocument(Request $request)
    {
        return $this->uploadFileWithValidation(
            $request, 'file', config('upload.allocation_document_path'), 
            BaseRequest::getCommonFileRules(5000),
            BaseRequest::getCommonFileRulesMessages()
        );
    }

    public function deleteAllocationDocument(Request $request)
    { 
        return $this->service->deleteDocuments($request->all());
         
    }

    public function store(Request $request, ?string $id = null): mixed 
    {
        
        $validator = Validator::make($request->all(), FundFlowRequest::getRules(),FundFlowRequest::messages());
        
        if ($validator->fails()) 
        {
            return $this->error($validator->errors());
        }
       
        return $this->created(
            $this->service->storeAllocation($validator->validated(), $id)
        );
    }

    public function getTotalAllocation(Request $request)
    {
        $query = \DB::table('fund_allocations_map')
            ->where('fund_allocations_map.financial_year', $request->financial_year)
            ->where('fund_allocations_map.major_component_id', $request->major_component_id);

        // optional filters
        if (!empty($request->component_id)) {
            $query->where('fund_allocations_map.component_id', $request->component_id);
        } else {
            $query->whereNull('fund_allocations_map.component_id');
        }

        /*if (!empty($request->sub_component_id)) {
            $query->where('fund_allocations_map.sub_component_id', $request->sub_component_id);
        } else {
            $query->whereNull('fund_allocations_map.sub_component_id');
        }*/
        if (!empty($request->sub_component_id)) {
            $query->where('fund_allocations_map.sub_component_id', $request->sub_component_id);
        } 

        $totalAllocated = $query->sum('fund_allocations_map.amount');

        $result = \DB::table('fund_distribution')
            ->where('fund_distribution.financial_year', $request->financial_year)
            ->where('fund_distribution.major_component_id', $request->major_component_id);

        // optional filters
        if (!empty($request->component_id)) {
            $result->where('fund_distribution.component_id', $request->component_id);
        } else {
            $result->whereNull('fund_distribution.component_id');
        }

        /*if (!empty($request->sub_component_id)) {
            $result->where('fund_distribution.sub_component_id', $request->sub_component_id);
        } else {
            $result->whereNull('fund_distribution.sub_component_id');
        }*/
        
        if (!empty($request->sub_component_id)) {
            $result->where('fund_distribution.sub_component_id', $request->sub_component_id);
        }

        $usedAmount = $result->sum('fund_distribution.amount_allocated');
        // $total_distributed_amt = $result->sum(\DB::raw(
        // 'amount_allocated - (amount_allocated * IFNULL(tds,0) / 100)'
        // ));

        return response()->json([
            'total'     => $totalAllocated,
            'remaining' => $totalAllocated - $usedAmount,
            'total_distributed_amt' => $usedAmount
        ]);
    }


}
