<?php

declare(strict_types=1);

namespace App\Domain\User;
use Illuminate\Support\Facades\DB;

class ChangePasswordAction
{
    public function __construct(
        private readonly ChangePasswordService $changePasswordService
    ) {
    }

    public function execute(string $userId, string $password): bool
    {
        return DB::transaction(function () use ($userId, $password) {
            $user = $this->changePasswordService->getUser($userId);

            $this->changePasswordService->changePassword(
                user: $user,
                password: $password
            );

            return true;
        });
    }
}