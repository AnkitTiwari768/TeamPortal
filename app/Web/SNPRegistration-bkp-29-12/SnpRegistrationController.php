<?php 

declare(strict_types=1);

namespace App\Web\SNPRegistration;

use Illuminate\View\View;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use App\Observers\AuditTrailLog;
use App\Http\Controllers\ClientController;
use App\Core\BaseRequest;
use App\Traits\HasFileUpload;
use App\Http\Api\V1\User\UserService as UserService;
use App\Web\ProductDomainMapping\ProductDomainMappingService as DomainService;
//use App\Http\Api\V1\User\User;
//use App\Notifications\Email\Signup as SignupNotification;
//use Session;
//use Mail;

class SnpRegistrationController extends ClientController
{
	use HasFileUpload;
    private static string $module = 'signup.index';

	public function __construct(private SnpService $service, UserService $userservice,DomainService $domaintype)
    {
        $this->service = $service;
        $this->userservice = $userservice;
        $this->domaintype = $domaintype;
    }
	
    public function index(): View
    {
        
        $title = __('SNP Registration');
     	//crypto_secrets();
        $domain_type = $this->domaintype->getAllDomainCategory();
        $roleList = $this->service->getRoles();
        $lang = \DB::table('language')->select('*')->get();
        //dd($lang);
        return view('snp_registration.registration', compact('title','domain_type','roleList','lang'))
				->with('lists', (object) $this->service->getDropdownList());
    }

	public function getsubdomains(Request $request)
	{
		//dd($request['domain_ids']);
		return $this->success(
            $this->service->getsubdomains($request['domain_ids'])
        ); 
		//$response = $this->service->getsubdomains($request['domain_ids']);
		//return $response;
	}

	public function uploadFlyerPresentationCertificate(Request $request)
    {
        return $this->uploadFileWithValidation(
            $request, 'file', config('upload.flyer_presentation_certificate_document_file_path'), 
            BaseRequest::getPdfRules(1536),
            BaseRequest::getCommonFileRulesMessages(1536)
        );
    }
	public function uploadAuthorizedCertificate(Request $request)
    {
        return $this->uploadFileWithValidation(
            $request, 'file', config('upload.authorized_certificate_document_file_path'), 
            BaseRequest::getCommonFileRules(5120),
            BaseRequest::getCommonFileRulesMessages(5120)
        );
    }

    public function uploadCancelledCheque(Request $request)
    {
        return $this->uploadFileWithValidation(
            $request, 'file', config('upload.cancelled_cheque_document_file_path'), 
            BaseRequest::getCommonFileRules(200),
            BaseRequest::getCommonFileRulesMessages(200)
        );
    }

    public function uploadCommercialModel(Request $request)
    {
        return $this->uploadFileWithValidation(
            $request, 'file', config('upload.commercial_model_document_file_path'), 
            BaseRequest::getCommonFileRules(5120),
            BaseRequest::getCommonFileRulesMessages(5120)
        );
    }

    public function uploadDescription(Request $request)
    {
        return $this->uploadFileWithValidation(
            $request, 'file', config('upload.description_document_file_path'), 
            BaseRequest::getPdfRules(5120),
            BaseRequest::getPdfRuleMessages(5120)
        );
    }

    public function deleteSnpDocument(Request $request)
    { 
        return $this->service->deleteDocuments($request->all());
         
    }

    public function create(Request $request): mixed 
    {
        dd($request->all());
		$validator = Validator::make($request->all(), SnpRequest::getRules(), SnpRequest::messages());
        
        if ($validator->fails()) 
        {
            return $this->error($validator->errors());
        }
		
		$result = $this->service->store($validator->validated());
		
         return $this->success($result,'SNP Registration Successfully'
        );
		
    }

    public function updateSnp($id)
    {
        $title = __('Update Details');
        $row = (array) $this->userservice->getSnpDetail($id);
        //dd($row);
        return view('snp_registration.registration', compact('title','id','row'))
				  ->with('lists', (object) $this->service->getDropdownList());
				  //->with('title', __('Create Your Account'));
    }

    public function updateRegistration(Request $request, string $id): mixed 
    {
		$validator = Validator::make($request->all(), SnpRequest::getRules($id), SnpRequest::messages());
        
        if ($validator->fails()) 
        {
            return $this->error($validator->errors());
        }
		
		$result = $this->service->store($validator->validated(),$id);
		
         return $this->success($result,'SNP Updated Successfully'
        );
		
    }

}