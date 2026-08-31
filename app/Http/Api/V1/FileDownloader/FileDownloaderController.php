<?php 

declare(strict_types=1);

namespace App\Http\Api\V1\LegalOfficer;

use App\Http\Controllers\ApiController;
use Illuminate\Support\Facades\Validator;

class LegalOfficerController extends ApiController
{
    public function __construct(private LegalOfficerService $LegalOfficerService) {}

    public function storeLiaisonOfficer(Request $request, ?string $id = null)
    {
        $validator = Validator::make($request->all(), LiaisonOfficerRequest::getLiaisonOfficer($id));
        
        if ($validator->fails()) 
        {
            return $this->error($validator->errors());
        }
       
        return $this->created(
            $this->LegalOfficerService->storeLegalOfficer($validator->validated())
        );
    }

    public function getLiaisonOfficer()
    {    
        return $this->success($this->LegalOfficerService->getLegalOfficer());
    }
}