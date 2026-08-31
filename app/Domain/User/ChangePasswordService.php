<?php

declare(strict_types=1);

namespace App\Domain\User;

use App\Domain\EmailTemplate\EmailTemplateService;
use App\Domain\UserLog\UserLogService;
use App\Models\User;
use Exception;
use Illuminate\Support\Facades\Hash;

class ChangePasswordService
{
    public function __construct(
        private readonly EmailTemplateService $emailTemplateService,
        private readonly UserLogService $userLogService
    ) {
    }

    public function changePassword(User $user, string $password): void
    {
        $user->update([
            'password' => $password,
            'updated_at' => now(),
            'updated_by' => authId(),
        ]);

        $this->userLogService->logPasswordChanged($user->id, authId());

        $this->emailTemplateService->send(
            templateKey: 'change-password',
            toEmail: $user->email,
            data: [
                'user_id' => $user->email,
                'password' => $password,
                'user_name' => $user->first_name . ' ' . $user->last_name,
            ]
        );
    }

    public function getUser(string $userId): User
    {
        $user = User::find($userId);

        if (!$user) {
            throw new Exception('User not found.');
        }

        return $user;
    }
}