<?php

declare(strict_types=1);

namespace App\Web\SendOtp;

use App\Models\User;
use App\Traits\HasResponses;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

final class ApplicationStatusSendOtpController
{
    use HasResponses;

    public function __invoke(Request $request): JsonResponse
    {
        $validated = $request->validate(
            [
                'username' => 'required|exists:rts_services,application_number',
            ],
            [
                'username' => [
                    'required' => 'Please enter the application number.',
                    'exists'   => 'The entered application number is not valid.',
                ],
            ],
        );

        $user = DB::table('rts_services as rs')
            ->select('u.id')
            ->join('users as u', 'rs.applied_by', '=', 'u.id')
            ->where('application_number', $validated['username'])
            ->first();


        if (! $user) {
            return $this->unauthenticated();
        }

        SendOtpJob::dispatch($user->id);

        return $this->success(message: __('auth.otp_resend'));
    }
}
