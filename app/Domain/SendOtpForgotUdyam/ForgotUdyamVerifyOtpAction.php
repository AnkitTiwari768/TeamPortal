<?php

declare(strict_types=1);

namespace App\Domain\SendOtpForgotUdyam;

use App\Models\User;
use App\Services\CryptoService;
use App\Services\OtpVerificationService;
use App\Domain\EmailTemplate\EmailTemplateService;
use App\Services\UserService;
use Illuminate\Support\Facades\Auth;

class ForgotUdyamVerifyOtpAction
{
    public function __construct(
        private CryptoService $cryptoService,
        private OtpVerificationService $otpVerificationService,
        private UserService $userService
    ) {}

    public function execute(array $data)
    {
        $decryptedOtp = (int) $this->cryptoService->decrypt($data['otp']);

        $result = $this->otpVerificationService->verifyOtp(key: $data['username'], otp: $decryptedOtp);

        if (! $result['status']) {
            return $result;
        }

        $user = User::where('mobile', $data['username'])->first();

        if ($result['status'] == true) {
            
            $res = \DB::table('team_msme_schemes')
            ->where('email', $data['email'])
            ->first();
            app(EmailTemplateService::class)->send(
                templateKey: 'msme-udyam-number-issued',
                toEmail: $data['email'],
                data: [
                    'name' => $res->entrepreneur_name,
                    'udyam_number' => $res->udyam_no,
                ]
            );
        }

        if (! $user->status) {
            return [
                'status' => false,
                'message' => 'Your account is blocked, please contact site administrator!'
            ];
        }

        // $userPermissions = $this->userService->getUserPermissions($user->id);

        // Auth::login($user);

        // session(['permissions' => $userPermissions]);

        return [
            'status' => true,
            'message' => 'Your udyam number has been successfully sent to your registered email address.'
        ];
    }
}
