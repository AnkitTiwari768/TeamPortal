<?php 

declare(strict_types=1);

namespace App\Web\BNPRegistration;

use Illuminate\View\View;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use App\Observers\AuditTrailLog;
use App\Http\Controllers\ClientController;
use App\Core\BaseRequest;
use App\Traits\HasFileUpload;
use App\Http\Api\V1\User\UserService as UserService;


class BnpRegistrationController extends ClientController
{
	use HasFileUpload;
    private static string $module = 'signup.index';

	public function __construct(private BnpService $service, UserService $userservice)
    {
        $this->service = $service;
        $this->userservice = $userservice;
    }
	
    public function index(): View
    {
        
        $title = __('BNP Registration');
        return view('bnp_registration.registration');
    }

    public function create(Request $request): mixed 
    {
		$validator = Validator::make($request->all(), BnpRequest::getRules(), BnpRequest::messages());
        
        if ($validator->fails()) 
        {
            return $this->error($validator->errors());
        }
		
		$result = $this->service->store($validator->validated());
		
         return $this->success($result,'BNP Registration Successfully'
        );
		
    }

    public function updateBnp($id)
    {
        $title = __('Update Details');
        $row = (array) $this->userservice->getBnpDetail($id);
        //dd($row);
        return view('bnp_registration.registration', compact('title','id','row'));
    }

    public function updateRegistration(Request $request, string $id): mixed 
    {
		$validator = Validator::make($request->all(), BnpRequest::getRules($id), BnpRequest::messages());
        
        if ($validator->fails()) 
        {
            return $this->error($validator->errors());
        }
		
		$result = $this->service->store($validator->validated(),$id);
		
         return $this->success($result,'BNP Updated Successfully');
		
    }

}