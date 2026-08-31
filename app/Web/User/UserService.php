<?php

declare(strict_types=1);

namespace App\Web\User;

use Illuminate\Support\Facades\DB;

class UserService
{
    public function getUserServiceCategories(string $userId): array
    {
        return DB::table('user_service_categories_mapping')
            ->where('user_id', $userId)
            ->pluck('service_category_id')
            ->toArray();
    }

    public function getUserRoles(string $userId): array
    {
        return DB::table('user_roles')
            ->where('user_id', $userId)
            ->pluck('role_id')
            ->toArray();
    }
}
