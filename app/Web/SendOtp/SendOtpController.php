<?php

declare(strict_types=1);

namespace App\Web\SendOtp;

use App\Models\User;
use App\Traits\HasResponses;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

final class SendOtpController
{
    use HasResponses;

    public function __invoke(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'username' => 'nullable',
            'email' => 'nullable|email',
        ]);

        $user = User::where('email', $validated['email'])
            ->orWhere('username', $validated['username'])
            ->first();
       

        if (! $user) {
            return $this->unauthenticated();
        }
        //dd($validated['username']);
        //(new SendOtpJob($validated['username']))->handle();

        SendOtpJob::dispatch($user->id);

        return $this->success(message: __('auth.otp_resend'));
    }
}
