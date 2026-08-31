<?php

declare(strict_types=1);

namespace App\Web\VerifyOtp;

use App\Models\User;
use App\Traits\HasResponses;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

final class ApplicationStatusVerifyOtpController
{
    use HasResponses;

    public function __invoke(Request $request, VerifyOtpAction $action, VerifyOtpRepository $repository): View|JsonResponse
    {
        $validated = $request->validate(
            [
                'username' => 'required|exists:rts_services,application_number',
                'otp' => 'required'
            ],
            [
                'username' => [
                    'required' => 'Please enter the application number.',
                    'exists'   => 'The entered application number is not valid.',
                ],
                'otp' => [
                    'required' => 'Please enter the OTP.',
                ]
            ],
        );

        $dto = VerifyOtpDto::fromArray($validated);

        $verifyOtpStatus = $action->execute($dto);

        if ($verifyOtpStatus === VerifyOtpStatus::CODE_INVALID || $verifyOtpStatus === VerifyOtpStatus::CODE_EXPIRED) {
            return $this->expiredOrInvalidOtp();
        }

        $user = DB::table('rts_services as rs')
            ->select('u.id', 'u.status')
            ->join('users as u', 'rs.applied_by', '=', 'u.id')
            ->where('application_number', $validated['username'])
            ->first();


        if (! $user->status) {
            return $this->accountBlocked();
        }

        $applicationDetails = DB::table('rts_services as rs')
            ->select('rs.*', 'sc.name AS service_category')
            ->join('rts_service_categories AS sc', 'rs.service_category_id', '=', 'sc.id')
            ->where('application_number', $dto->username)
            ->first();

        return view('application.application_status_details', compact('applicationDetails'));
    }
}
