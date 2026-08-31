<?php 

declare(strict_types=1); 

namespace App\Http\Api\V1\Auth;

use Illuminate\Support\Facades\Validator;

class AuthValidation 
{
    public static function getAuthValidator(array $request, ?string $id = null)
    {
        return Validator::make($request, [
            'username' => 'required',
            //'password' => 'required',
        ]);
    }

    public static function getVerifyOtpValidator(array $request)
    { 
        return Validator::make($request, [
            'username' => 'required',
            'otp' => 'required',
        ]);
    }

    public static function getVerifyOtpWithCaptchaValidator(array $request)
    { 
        return Validator::make($request, [
            'username' => 'required',
            'otp' => 'required',
            'captcha' => 'required|captcha'
        ]);
    }
}