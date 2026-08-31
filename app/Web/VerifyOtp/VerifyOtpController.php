<?php

declare(strict_types=1);

namespace App\Web\VerifyOtp;

use App\Models\User;
use App\Traits\HasResponses;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Auth;

final class VerifyOtpController
{
    use HasResponses;

    public function __invoke(VerifyOtpRequest $request, VerifyOtpAction $action, VerifyOtpRepository $repository): JsonResponse
    {
        $dto = $request->toDto();

        $verifyOtpStatus = $action->execute($dto);

        if ($verifyOtpStatus === VerifyOtpStatus::CODE_INVALID || $verifyOtpStatus === VerifyOtpStatus::CODE_EXPIRED) {
            return $this->expiredOrInvalidOtp();
        }

        $user = User::where('username', $dto->username)->first();

        if (! $user->status) {
            return $this->accountBlocked();
        }

        $userPermissions = array_values(array_unique($repository->getUserPermissions($user->id)));

        Auth::login($user, $request->boolean('remember'));

        session(['permissions' => $userPermissions]);

        return $this->verified(redirect: url('dashboard'));
    }
}
