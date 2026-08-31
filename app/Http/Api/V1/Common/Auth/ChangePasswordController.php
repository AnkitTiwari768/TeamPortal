<?php 
declare(strict_types=1);
namespace App\Http\Api\V1\Auth;
use App\Http\Controllers\ApiController;
use App\Http\Api\V1\UserProfile\UserProfileService as Service;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rules;
use App\Rules\ValidateAuthPassword; 
use App\Rules\NotInPreviousPasswords; 
use App\Http\Api\V1\Auth\AuthService as AuthService;
use App\Models\User;
use App\Http\Services\VerificationService;

class ChangePasswordController extends ApiController 
{
    public function __construct(private Service $service, AuthService $authService) 
    {
        $this->service = $service;
        $this->authService = $authService;
    }

    public function sentOtp(Request $request)
    {
        $id = auth()->user()->id;
        $validator = Validator::make($request->all(), [
            'current_password' => ['required', new ValidateAuthPassword()],
            'password' => ['required', 'confirmed',Rules\Password::min(8)
            ->mixedCase()
            ->numbers()
            ->symbols()
            ->uncompromised(), new NotInPreviousPasswords(auth()->user())],
            'password_confirmation' => 'required',
        ]); 

        if ($validator->fails()) 
            return $this->error($validator->errors()); 
        
        $user = User::where('id', $id)
                ->first();
                
        $otp = $this->authService->sendVerificationCode($user?->email, true, $user);
        
        if($otp){
            return response()->json([ 'status' => true, 'message' => __('message.otp_sent_successfully')]); 
        }else{
             return response()->json([ 'status' => false, 'errors' => __('message.failed_request')]); 
        }
    }

    public function resendOtp(Request $request)
    {
        $id = auth()->user()->id;
        $user = User::where('id', $id)
            ->first();
        $otp = $this->authService->sendVerificationCode($user?->email, true, $user);
        
        if($otp){
            return response()->json([ 'status' => true, 'message' => __('message.otp_sent_successfully')]); 
        }else{
            return response()->json([ 'status' => false, 'errors' => __('message.failed_request')]); 
        }
    }

    public function changePassword(Request $request)
    {  
        
        try 
        {
                     
            $validator = Validator::make($request->all(), [
                'current_password' => ['required', new ValidateAuthPassword()],
                'password' => ['required', 'confirmed',Rules\Password::min(8)
                ->mixedCase()
                ->numbers()
                ->symbols()
                ->uncompromised(), new NotInPreviousPasswords(auth()->user())],
                'password_confirmation' => 'required',
                'otp' => 'required',
            ]); 

            if ($validator->fails()) 
                return $this->error($validator->errors()); 
         
            //dd($validator->validated());

            $id=auth()->user()->id; 
            $verifyOtp = $this->verifyOTP($validator->validated());
            if($verifyOtp)
            {
                $this->service->updatePassword($validator->validated(),$id);
                return $this->updated();
            }else{
                return response()->json([ 'status' => false, 'errors' => __('Entered OTP is incorrect or expired!')]); 
            }
        }
        catch (Throwable $exception) 
        {
            return $this->handler($error);
        }
    }

    public function verifyOTP($payload)
    {
        $id=auth()->user()->id; 
        $user = User::where('id', $id)
                ->first();
        $key = $user?->email;
        $code = (int) $payload['otp'];
        

        $result = VerificationService::verify($key, $code);
        
        if (
            $result === VerificationService::CODE_INVALID || 
            $result === VerificationService::CODE_EXPIRED
        )
        {
            return false;
        }
        
        if ($result === VerificationService::CODE_VERIFIED)
        { 
            return true;
        }
        
        
    }
    
}