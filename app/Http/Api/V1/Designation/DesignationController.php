<?php 

declare(strict_types=1);

namespace App\Http\Api\V1\Designation;

use App\Http\Controllers\ApiController;
use Illuminate\Support\Facades\Validator;

class DesignationController extends ApiController
{
    public function __construct(private DesignationService $DesignationService) {}

    public function storeDesignation(Request $request, ?string $id = null)
    {
        $validator = Validator::make($request->all(), DesignationRequest::getRules($id));
        
        if ($validator->fails()) 
        {
            return $this->error($validator->errors());
        }
       
        return $this->created(
            $this->DesignationService->storeDesignation($validator->validated())
        );
    }

    public function getDesignations()
    {    
        return $this->success($this->DesignationService->getDesignations());
    }
}