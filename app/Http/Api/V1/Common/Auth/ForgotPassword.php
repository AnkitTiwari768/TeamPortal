<?php 
  
namespace App\Http\Api\V1\Auth;
   
use App\Http\Controllers\ClientController;
use Illuminate\Validation\ValidationException;
use App\Http\Services\VerificationService;
use Illuminate\Support\Facades\Password;
use Illuminate\Validation\Rules;
use Illuminate\Support\Str;
use Illuminate\Http\Request; 
use App\Models\User; 
use Carbon\Carbon; 
use Validator;
use Session;
use Mail; 
use Hash;
use URL;
use DB; 

class ForgotPassword extends ClientController
{ 
   
     public function getAuthUser($type,$username)
    {
        if($type=='email'){
             return User::where('email', $username)
            ->orWhere('alternate_email', $username) 
            ->first();

        }else{
        return User::where('mobile', $username)
            ->orWhere('alternate_mobile', $username)
            ->first();
        } 
    }

	public function sent_email_link(Request $request)
	{
		$user= $this->getAuthUser('email',$request->email);
        if (!$user) {  
             return response()->json([
                    'status' => false,
                    'errors' =>  __('auth.failed'),
                    'message' => 'validation Error'
                ]);    
        }       

		$token = Str::random(64); 
		DB::table('password_resets')->insert([
			'email' => $request->email, 
			'token' => $token, 
			'created_at' => Carbon::now()
		]);

		 Mail::send('emails.forgetPassword', ['token' => $token], function($message) use($request){
			$message->to($request->email);
			$message->subject('Reset Password');
		});

		return response()->json([
                    'status' => true,
                    'token' => $token,
                    'message' => 'Email sent successfully'
                ]);		 
	}  
     
    public function emailResetPassword(Request $request)
    {   
         $validator = Validator::make($request->all(), [ 
                'email' => 'required|exists:users,email',
                'password' => ['required', 'confirmed', Rules\Password::min(8)->mixedCase()->numbers()->symbols()->uncompromised()],
                'password_confirmation' => 'required',  
                'token' => 'required',  
            ]); 
		 
         if($validator->fails()){
             return response()->json([
                    'status' => false,
                    'errors' => $validator->errors(),
                    'message' => 'validation Error'
                ]);     
        }
		  
        $ifTokenNotExist = DB::table('password_resets')
						  ->where([
							'email' => $request->email, 
							'token' => $request->token
						  ])
						  ->first();
  
        if(!$ifTokenNotExist){
            return response()->json([
                    'status' => false,
                    'errors' => 'Invalid token!',
                    'message' => 'validation Error'
                ]);    
        }
  
        $user = User::where('email', $request->email)
                    ->orWhere('alternate_email', $request->email) 
                    ->update(['password' => Hash::make($request->password)]);
 
        DB::table('password_resets')->where(['email'=> $request->email])->delete();
         return response()->json([
                    'status' => true, 
                    'message' => 'Your password has been changed!'
                ]);    
    }

    //Mobile Reset Password
     public function sent_reset_otp(Request $request)
    {  
    	$user= $this->getAuthUser('mobile',$request->mobile);
        if (!$user) {  
             return response()->json([
                    'status' => false,
                    'errors' =>  __('auth.failed'),
                    'message' => 'validation Error'
                ]);    
        }   

        $otp = generateOtp();
        VerificationService::sendVerificationCodeBySms(phone: $request['mobile'], code: $otp);

        return response()->json([ 'status' => true, 'mobile' => $request['mobile'],'expire_time'=> date('Y-m-d H:i:s'), 'message' => 'OTP has been sent' ]); 
    } 


    public function mobileResetPassword(Request $request)
    { 
    	// $request->merge([
        //     'password' => $request->get('password') ? crypto_decrypt($request->get('password')) : $request->get('password'),
        //     'password_confirmation' => $request->get('password_confirmation') ? crypto_decrypt($request->get('password_confirmation')) : $request->get('password_confirmation'),
        // ]);
        
        $validator = Validator::make($request->all(), [ 
                'otp' => 'required',
                'mobile' => 'required',
                'password' => ['required', 'confirmed', Rules\Password::min(8)->mixedCase()->numbers()->symbols()->uncompromised()],
                'password_confirmation' => 'required'
            ]); 
         
         if($validator->fails()){
             return response()->json([
                    'status' => false,
                    'errors' => $validator->errors(),
                    'message' => 'validation Error'
                ]);     
        }

        
        $result = VerificationService::verify($request['mobile'], $request['otp']);

        if (
            $result === VerificationService::CODE_INVALID || 
            $result === VerificationService::CODE_EXPIRED
        )
        {
            return response()->json([
                'status' => false,
                'message' => 'Entered OTP is incorrect or expired!'
            ]);
        } 
            DB::table('users')
                ->where('mobile', $request['mobile'])
                ->orWhere('alternate_mobile', $request['mobile'])
                ->update(['password' => Hash::make($request['password'])]);
            //For sending SMS
            // $password = $request['password'];
            // $tdetails=forgotPasswordSmsTemplate();
            // $message=str_replace("{#var#}",$password,$tdetails['msg']);
            // sendSingleUnicode($message,$mobile,$tdetails);    

             return response()->json([
                    'status' => true, 
                    'message' => 'Your password has been changed!'
                ]);  
    }

    public function resendOtp(Request $request)
    {
        $otp = generateOtp();
        VerificationService::sendVerificationCodeBySms(phone: $request['mobile'], code: $otp);
        return response()->json([
            'status' => true
        ]);
    }
}