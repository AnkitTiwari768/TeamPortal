<?php 

declare(strict_types=1);

namespace App\Traits;

use DB;

trait UserDetail 
{
    private function queryUsersInfoByRole(string $role, array $columns)
    {
        return DB::table('users AS u')
            ->select($columns)
            ->join('user_roles AS ur', 'u.id', '=', 'ur.user_id')
            ->join('roles AS r', 'ur.role_id', '=', 'r.id')
            ->where('r.slug', $role)
            ->get();
    }

    public function getUsersEmailByRoleSlug(string $slug, ?bool $asArray = null) 
    {
        $results = $this->queryUsersInfoByRole('ffo', ['u.email']);

        if (! $results) return null;

        return ($asArray) ? $results->pluck('email')->toArray() : $results->pluck('email');
    }

    public function getUsersInfoCollectionByRole(string $role, ?bool $asArray = null) 
    {
        $results = $this->queryUsersInfoByRole('ffo', ['u.id', 'u.email']);

        if (! $results) return null;

        return [
            'id' => ($asArray) ? $results->pluck('id')->toArray() : $results->pluck('id'),
            'email' => ($asArray) ? $results->pluck('email')->toArray() : $results->pluck('email'),
        ];
    }
}
