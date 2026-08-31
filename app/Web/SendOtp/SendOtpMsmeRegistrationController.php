<?php

declare(strict_types=1);

namespace App\Web\SendOtp;

use App\Traits\HasResponses;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

final class SendOtpMsmeRegistrationController
{
    use HasResponses;

    public function __invoke(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'username' => 'nullable',
            'email' => 'nullable|email',
        ]);
                 
        SendOtpMsmeRegistrationJob::dispatch($validated['username'],$validated['email']);

        return $this->success(message: __('auth.otp_resend'));
    }
}
