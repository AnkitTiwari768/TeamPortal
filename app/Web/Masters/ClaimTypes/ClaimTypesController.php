<?php 

declare(strict_types=1);

namespace App\Web\Masters\ClaimTypes;

use Illuminate\View\View;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use App\Observers\AuditTrailLog;
use App\Http\Controllers\ClientController;
use App\Core\BaseRequest;
use App\Web\Masters\ClaimTypes\ClaimTypesService;
use App\Web\Masters\ClaimTypes\ClaimTypesRequest;
use App\Web\Masters\ClaimTypes\ClaimTypesResource;

class ClaimTypesController extends ClientController
{
    
	public function __construct(private ClaimTypesService $service)
    {
        $this->service = $service;
    }

    public function index(): View
    {      
        $title = 'Claim Types List';        
        return view('masters.claim_types.index', compact('title'));  
    }

    public function getDataClaimTypes()
    {      
        return $this->success($this->service->getDataClaimTypes());
    }

    public function create(): View
    {      
        $title = 'Add Claim Types';
        $module_url = 'Claim-Type-list';
        return view('masters.claim_types.form',compact('title','module_url'));
    }
     public function claimTypesCreate(Request $request)
    {   
        $validator = Validator::make($request->all(),ClaimTypesRequest::getRules(),ClaimTypesRequest::messages());        
        if ($validator->fails()) 
        {
            return response()->json([
                'status' => false,
                'errors' => $validator->errors()
            ],422);
        }

        $this->service->storeClaimType($validator->validated());

        return response()->json([
            'status' => true,
            'message' => 'Claim Types Created Successfully'
        ]);
    }
    public function edit($id): View
    {      
        $row = $this->service->getClaimTypesById($id);
        $title = 'Edit Claim Types';
        $module_url = 'Claim-Types-list';
        return view('masters.claim_types.form',compact('title','module_url','row','id'));
    }
     public function claimTypesUpdate(Request $request,string $id)
    {   
        $validator = Validator::make($request->all(),ClaimTypesRequest::getRules($id));        
        if ($validator->fails()) 
        {
            return response()->json([
                'status' => false,
                'errors' => $validator->errors()
            ],422);
        }       
        $this->service->storeClaimType($validator->validated(),$id);
        return response()->json([
            'status' => true,
            'message' => 'Claim Types  Updated Successfully'
        ]);
    }



   
   

}