<?php 

declare(strict_types=1);

namespace App\Web\Registration;
use App\Traits\HasResponses;
use Illuminate\View\View;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use App\Observers\AuditTrailLog;
use App\Http\Controllers\ClientController;
use App\Http\Api\V1\Registration\{RegistrationService, RegistrationRequest};

use App\Http\Api\V1\User\User;
use Session;
use Mail;
use DB;
use App\Web\VerifyOtp\VerifyOtpAction;
use App\Web\VerifyOtp\VerifyOtpDto;
use App\Web\VerifyOtp\VerifyOtpStatus;
use Illuminate\Http\JsonResponse;
use Mews\Captcha\Facades\Captcha;
use Illuminate\Support\Str;
use App\Web\Notification\SendNotificationEvent;
use Illuminate\Support\Facades\Auth;



class RegistrationController 
{
	use HasResponses;

    private static string $module = 'signup.index';

    public function __construct(private AuditTrailLog $auditTrailLog, private RegistrationService $RegistrationService){
		$this->auditTrailLog = $auditTrailLog;
        $this->RegistrationService = $RegistrationService;
	}
	
	
	 public function registration(): View
    {

        $title = __('Create Your Account');
     	crypto_secrets();
        return view('applicant_registration.signup', compact('title'))
			->with('crypto_salt', session('crypto_salt'))
			->with('crypto_iv', session('crypto_iv'))
			->with('crypto_key', session('crypto_key'))
			->with('crypto_key_size', session('crypto_key_size'))
			->with('crypto_iterations', session('crypto_iterations'))
			->with('crypto_iterations', session('crypto_iterations'))
		  ->with('lists', (object) $this->RegistrationService->getDropdownList());
			  
    }
	

	public function create(Request $request)
    {
		$validator = Validator::make($request->all(), RegistrationRequest::getRules($request), RegistrationRequest::messages());
        
        if ($validator->fails()) 
        {
            return $this->error($validator->errors());
        }
		 
		$registration_types= $this->RegistrationService->store($request->all());
         //return $this->success(message: 'MSME Registration Successfully');
		 if($registration_types==1){

		    $fromUserId = authId();
			$fromRoleId = DB::table('user_roles')->where('user_id', $fromUserId)->value('role_id');
			$toRoleId = DB::table('roles')->where('slug', 'snp')->value('id');
			event(new SendNotificationEvent(templateKey: 'new-mse-added-to-validate-list',fromUserId: $fromUserId,toUserId:null,formRole: $fromRoleId,toRole: $toRoleId, message: [
				'MSE_NAME' => $request->entrepreneur_name
				]));

				
			 return response()->json([
				'status' => true,
				'registration_type' => 1,
				'message' => 'Your account has been created successfully. Now relevant SNP will connect with you for furthur process.',
				//'redirect' => url('signup')
				'redirect' => url('https://team.msme.gov.in/')
			]);
		 }else if($registration_types==2){
			 return response()->json([
				'status' => true,
				'registration_type' => 2,
				'message' => 'By clicking on OK you will be redirected to the Udyam Registration Page, please  register and keep the Udyam number handy with you, one of our SNP will connect with you soon.',
				'redirect' => 'https://udyamregistration.gov.in/Government-India/Ministry-MSME-registration.htm'
			]);
		 }else if($registration_types==3){
			 return response()->json([
				'status' => true,
				'registration_type' => 3,
				'message' => 'Your data has been shared with the helpdesk, you must receive a call from our executive soon. If you wish to connect, please call on 14475',
				'redirect' => 'https://web.utlhq.com/team_uat/contact-us'
			]);
		 }
		
    }
	

	
	public function snpSelectDetails(Request $request)
	{
		$stateUid=Str::isUuid($request->state_code);
		if($stateUid){
			$state_id=$request->state_code;
		}else{
			$state_id = $this->RegistrationService->getStateId('states', $request->state_code);			
		}
		
		$snpdetails=$this->RegistrationService->getSelectSnpDetails($state_id,$request->ondc_transaction_type_id,$request->sub_domain);
		if (empty($snpdetails) || count($snpdetails) == 0) {
			return response("
				<div class='alert alert-warning text-center mt-3'>
					No SNP data found for selected category. Please choose another filter.
				</div>
			");
		}
		// dd($snpdetails);
		return view('applicant_registration.snp-details')->with('snpdetails',$snpdetails);
			
	}
	
	
	


    
    
}