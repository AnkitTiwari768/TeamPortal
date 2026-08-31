<?php

declare(strict_types=1);

namespace App\Web\FundFlow;

use Illuminate\View\View;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use App\Http\Controllers\ClientController;
use App\Traits\HasFileUpload;
use App\Core\BaseRequest;


class FundDistributionController extends ClientController
{
    use HasFileUpload;
    private static string $module = 'fund-distributions';

    public function __construct(private FundFlowService $service, private FundDistributionService $distributionservice) {}


  
    public function index(): View
    {
        guard('fund-distribution-view');    
        return view('fund_flow.distributionlist')
            ->with('title', 'Distribution List');
    }

    public function getDistributionList()
    {
        return $this->success($this->distributionservice->getDistributionList());
    }

    public function getDetails($id): View
    {
        $title = __('Distribution Detail');    
        $row = (array) $this->distributionservice->getDistributionDetail($id);
        $module_url = static::$module;
        //dd($row);
		return view('fund_flow.distributiondetails', compact('title', 'id' ,'row','module_url')); 
    }
    

    public function create(): View
    {
        guard('fund-distribution-create');    
        $title = __('Add Distribution');
        $module_url = static::$module;
        $details = (object) $this->service->getDetails();
        return view('fund_flow.distribution_form', compact('title', 'module_url','details'));
        
    }

    public function edit(string $id): View
    {
        guard('fund-distribution-create');    
        $title = __('Edit Distribution');
        $module_url = static::$module;
        $details = (object) $this->service->getDetails();
        $row = (array) $this->distributionservice->getDistributionDetail($id);
        //dd($row);
        return view('fund_flow.distribution_form', compact('title', 'module_url','details','row'));
        
    }

    public function store(Request $request, ?string $id = null): mixed 
    {
        
        $validator = Validator::make($request->all(), FundDistributionRequest::getRules(),FundDistributionRequest::messages());
        
        if ($validator->fails()) 
        {
            return $this->error($validator->errors());
        }
       
        $data = $validator->validated();

        $check = app(FundFlowController::class)->getTotalAllocation($request)->getData();

        $remaining = $check->remaining;

        if ((float)$data['amount_allocated'] > (float)$remaining) {
            return $this->error(['amount_allocated' => ['Amount exceeds remaining balance']]);
        }

        if ($remaining < 0) {
            return $this->error(['amount_allocated' => ['Invalid remaining balance']]);
        }

        return $this->created(
            $this->distributionservice->storeDistribution($validator->validated(), $id)
        );
    }


}
