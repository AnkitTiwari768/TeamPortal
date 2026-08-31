<?php

declare(strict_types=1);

namespace App\Domain\MsmeAuth;

use App\Traits\Respond;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

final class MsmeAuthController
{
    use Respond;

    public function sendOtp(Request $request, MsmeSendOtpAction $action): JsonResponse
    {
        $validated = $request->validate([
            'username' => 'required|exists:team_msme_schemes,udyam_no',
            'captcha' => ($request->query('resend') === 'true') ? 'nullable' : 'required|captcha'
        ]);

        $result = $action->execute($validated['username']);

        return $this->success(message: __('auth.otp_resend'), data: $result);
    }

    public function verifyOtp(Request $request, MsmeVerifyOtpAction $action): JsonResponse
    {
        $validated = $request->validate([
            'username' => 'required|exists:team_msme_schemes,mobile',
            'otp' => 'required',
        ]);

        $result = $action->execute($validated);

        if (! $result['status']) {
            return $this->error(message: $result['message']);
        }

        return $this->success(message: $result['message']);
    }
}
