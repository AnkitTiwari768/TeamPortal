<?php

declare(strict_types=1);

namespace App\Web\Signup;

use App\Traits\HasResponses;
use App\Utils\Mask;
use Illuminate\Http\JsonResponse;

final class SendOtpController
{
    use HasResponses;

    public function __invoke(SendOtpRequest $request): JsonResponse
    {
        $dto = $request->toDto();

        SendOtpJob::dispatch($dto->mobile);

        return $this->successWithCsrf(
            message: __('auth.otp_resend'),
            data: [
                'mobile' => Mask::maskMobileNumber($dto->mobile)
            ]
        );
    }
}
