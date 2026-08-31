<?php

declare(strict_types=1);

namespace App\Domain\SendOtpByUdyamNumber;

use App\Traits\Respond;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

final class SendOtpForgotUdyamNumberController
{
    use Respond;

    public function __invoke(Request $request, SendOtpForgotUdyamNumberAction $action): JsonResponse
    {
        $validated = $request->validate([
            'username' => 'required|exists:team_msme_schemes,udyam_no',
            'captcha' => ($request->query('resend') === 'true') ? 'nullable' : 'required|captcha'
        ]);

        $result = $action->execute($validated['username']);

        return $this->success(message: __('auth.otp_resend'), data: $result);
    }
}
