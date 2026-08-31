<?php

declare(strict_types=1);

namespace App\Web\Signup;

use App\Traits\HasResponses;
use App\Web\VerifyOtp\GenericVerifyOtpRequest;
use App\Web\VerifyOtp\VerifyOtpAction;
use App\Web\VerifyOtp\VerifyOtpStatus;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;

final class VerifyOtpController
{
    use HasResponses;

    public function __invoke(GenericVerifyOtpRequest $request, VerifyOtpAction $action): JsonResponse
    {
        $dto = $request->toDto();

        $verifyOtpStatus = $action->execute($dto);

        if ($verifyOtpStatus === VerifyOtpStatus::CODE_INVALID || $verifyOtpStatus === VerifyOtpStatus::CODE_EXPIRED) {
            return $this->expiredOrInvalidOtp();
        }

        return $this->successWithCsrf(
            message: __('signup.otp_verified'),
            data: [
                'verified_at' => Carbon::now()
            ]
        );
    }
}
