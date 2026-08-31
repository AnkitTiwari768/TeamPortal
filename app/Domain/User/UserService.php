<?php

declare(strict_types=1);

namespace App\Domain\User;

use Illuminate\Support\Facades\DB;

class UserService
{
    public function processStatus(?int $status): int
    {
        if (! hasRole('administrator')) {
            // Admin can set any status
            return UserStatus::ACTIVE->value;
        }

        // Default to active if status is not provided
        return $status ?? UserStatus::ACTIVE->value;
    }

    public function processUserRole(?string $roleId): ?string
    {
        if (! hasRole('administrator')) {
            return $this->getUserSessionActiveRole($roleId);
        }

        return $roleId;
    }

    public function getUserSessionActiveRole(?string $roleId = null): ?string
    {
        if (session()->has('active_role') && in_array(session()->get('active_role'), ['snp', 'bnp', 'lsp'])) {
            $roleId = DB::table('roles')->where('slug', session()->get('active_role'))->value('id');
        }

        return $roleId;
    }
}
