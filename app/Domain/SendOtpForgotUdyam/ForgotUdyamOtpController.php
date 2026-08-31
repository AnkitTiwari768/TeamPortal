<?php

declare(strict_types=1);

namespace App\Domain\SendOtpForgotUdyam;

use App\Traits\Respond;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

final class ForgotUdyamOtpController
{
    use Respond;

    public function sendOtp(Request $request, ForgotUdyamSendOtpAction $action): JsonResponse
    {
        //dd($request->all());
        $validated = $request->validate([
            'username' => 'required|exists:team_msme_schemes,email',
            'mobile_number' => 'required|exists:team_msme_schemes,mobile',
            'captcha' => ($request->query('resend') === 'true') ? 'nullable' : 'required|captcha'
        ],
        [
            'username.required' => 'Please enter your registered email address.',
            'username.exists' => 'This email is not registered with us.',

            'mobile_number.required' => 'Please enter your registered mobile number.',
            'mobile_number.exists' => 'This mobile number does not match our records.',

            'captcha.required' => 'Captcha is required.',
            //'captcha.captcha' => 'Invalid captcha code.'
        ]);

        $result = $action->execute($validated['username'],$validated['mobile_number']);

        return $this->success(message: __('auth.otp_resend'), data: $result);
    }

    public function verifyOtp(Request $request, ForgotUdyamVerifyOtpAction $action): JsonResponse
    {
        $validated = $request->validate([
            'username' => 'required|exists:team_msme_schemes,mobile',
            'mobile_number' => 'required|exists:team_msme_schemes,mobile',
            'email' => 'nullable',
            'otp' => 'required',
        ]);

        $result = $action->execute($validated);

        if (! $result['status']) {
            return $this->error(message: $result['message']);
        }

        return $this->success(message: $result['message']);
    }
}
