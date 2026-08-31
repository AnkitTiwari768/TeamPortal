<?php 
declare(strict_types=1);
namespace App\Modules\Verification; 
use App\Http\Controllers\ClientController; 
use Illuminate\Http\Request;
use Illuminate\View\View;
use App\Http\Services\VerificationService;
use App\Http\Api\V1\Verification\VerificationValidation as Validation;
use Session;

final class VerificationController extends ClientController
{ 
    
    public function __construct(private VerificationService $service)
    {
        $this->service = $service;
    } 
 
	public function sent_otp(Request $request)
    {
        $validator = Validation::getGenerateOtpRules($request->all());
            
        if ($validator->fails()) 
            {
                return $this->error($validator->errors()->first());
            }
             
            //Session::put($request->type, "1");  //For not verification                 

        if (isPhoneNumber($request?->keyValue))
        {
            $mobile_service= VerificationService::sendVerificationCodeBySms(phone: $request->keyValue, code: generateOtp());
            if($mobile_service==true){
                return response()->json([ 'status' => true, 'errors' => __('message.sent_mobile_otp_msg')]); 
            }else{
                 return response()->json([ 'status' => false, 'errors' => __('message.failed_request')]); 
            }
        }

        if (isEmail($request?->keyValue)) 
        {   
            $email_service= VerificationService::sendVerificationCodeByEmail(email: $request->keyValue, code: generateOtp());

            if($email_service==true){
                return response()->json([ 'status' => true, 'errors' => __('message.sent_email_otp_msg')]); 
            }else{
                 return response()->json([ 'status' => false, 'errors' => __('message.failed_request')]); 
            }
        } 
    }



    public function verify_otp(Request $request){
 
        $validator = Validation::getVerifyOtpRules($request->all());
            
        if ($validator->fails()) 
            {
                return $this->error($validator->errors()->first());
            }

		$result= VerificationService::verify((string) $request->keyValue, (int) $request->otp);

        if (
            $result === VerificationService::CODE_INVALID || 
            $result === VerificationService::CODE_EXPIRED
        )
        {
            return response()->json([
                'status' => false,
                'errors' => __('Please enter valid OTP')
            ]);
        }
        else{ 
            Session::put($request->type, "1");  //For verification  
            Session::put($request->type.'_value', $request->keyValue); // For storing value of email and mobile
            return response()->json([ 'status' => true, 'errors' => __('message.verified_msg')]); 
        }
       
        
    }
	
	
}