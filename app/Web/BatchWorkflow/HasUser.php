<?php

declare(strict_types=1);

namespace App\Web\BatchWorkflow;

use Illuminate\Support\Facades\DB;

trait HasUser
{
    public function fetchRoleId(string $userId): ?string
    {
        return DB::table('user_roles')->where('user_id', $userId)->first()?->role_id;
    }

    public function fetchRoleIdBySlug(string $slug): string
    {
        return DB::table('roles')->where('slug', $slug)->value('id');
    }
}
