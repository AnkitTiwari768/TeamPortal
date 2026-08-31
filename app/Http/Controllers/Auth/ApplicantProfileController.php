<?php declare(strict_types=1);

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\ClientController;
use App\Http\Api\V1\User\UserValidation as Validation;
use Illuminate\Http\Request;
use App\Rules\FileName;
use App\Http\Api\V1\User\UserService as UserService;
use App\Http\Api\V1\ApplicantProfile\{ApplicantProfileService, ApplicantProfileValidation};
use Illuminate\View\View;
use Illuminate\Support\Facades\Validator;
use App\Http\Api\V1\FileUpload\FileUploadService;
use App\Http\Services\CommonService;
use Session;

final class ApplicantProfileController extends ClientController 
{   
    protected $module_url='applicant_signup.edit';
    protected $service;
	
    public function __construct(UserService $service,ApplicantProfileService $applicantProfileService,CommonService $commonService)
    {
        $this->service = $service;
        $this->applicantProfileService = $applicantProfileService;
        $this->commonService = $commonService;
    }

    public function index()
    {
        crypto_secrets(); 
        $id = auth()->user()->id;
		//dd($id);
        $row = (array) $this->applicantProfileService->getApplicantDetail($id);
		//dd($row);
		$module_url = $this->module_url;
		$isProfile = true;
        return view('applicant_profile.edit',compact('id','row','isProfile','module_url'))
            ->with('title', __('message.your_profile'))
			->with('details', $this->service->getDetails($id))
            ->with('crypto_salt', session('crypto_salt'))
            ->with('crypto_iv', session('crypto_iv'))
            ->with('crypto_key', session('crypto_key'))
            ->with('crypto_key_size', session('crypto_key_size'))
            ->with('crypto_iterations', session('crypto_iterations'));
    }


    public function update(Request $request, $id)
    {    
        try 
        {	
			$ifExistResult=$this->checkIfExistData($request->all()); 
			//For verification
            $is_Mobile_Verified = Session::get('mobile');
            //$is_email_Verified = Session::get('email');
           
            if($is_Mobile_Verified==null)
            {  
                return $this->error([],__('message.vefircation_fields_error'));
            }

            //For checking if post value and session value is same for email and mobile
            $mobile=Session::get('mobile_value'); 
            $email=Session::get('email_value');

            if($mobile != $request->mobile )
            {  
                return $this->error([],__('message.vefircation_value_mobile_error'));
            }            
            //if($email != $request->email)
            //{  
            //    return $this->error([],__('message.vefircation_value_email_error'));
            //}

			$validator = ApplicantProfileValidation::validate($request->all(),AuthId());
			
            if ($validator->fails()) 
               return $this->error($validator->errors());
			
            (new ApplicantProfileService())->save($request->all(),$id);

            Session::forget('mobile');  
            Session::forget('email'); 
        
            return $this->updated();
        }
        catch (Throwable $exception) 
        {
            return $this->handler($error);
        }
    }


    
    
    public function checkIfExistData($payload)
    {
        if($payload['mobile']){
			
           $result1=$this->commonService->getExistData('users','mobile',$payload['mobile']); 
           if($result1){
                Session::put('mobile', "1");
                Session::put('mobile_value', $payload['mobile']);
            }else{
                if(Session::get('mobile') != 1)
                {
                    Session::put('mobile', "");
                    Session::put('mobile_value', "");
                }                   
            }
          
        }

        /*if($payload['email']){
            $result2=$this->commonService->getExistData('users','email',$payload['email']); 
            if($result2){
             Session::put('email', "1");
             Session::put('email_value', $payload['email']);
             }else{
                if(Session::get('email') != 1)
                {
                    Session::put('email', "");
                    Session::put('email_value', "");
                }

             }
         }*/
  
    }
 
}
