<?php

namespace App\Traits;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;

trait SubUserTrait
{
    /**
     * Get all user IDs including current user and their sub-users
     * 
     * @param string|int|null $userId
     * @return array
     */
    public function getUserIdsWithSubUsers($userId = null)
    {
        if ($userId === null) {
            $userId = Auth::id();
        }

        // Get current user details
        $user = DB::table('users')->where('id', $userId)->first();
        
        // If user is a sub-user, get their parent ID
        $parentId = !empty($user->parent_user_id) ? $user->parent_user_id : $userId;
        
        // Get all user IDs (parent + all sub-users)
        $userIds = [$parentId];
        
        // Get all sub-users under this parent
        $subUsers = DB::table('users')
            ->where('parent_user_id', $parentId)
            ->pluck('id')
            ->toArray();
        
        return array_merge($userIds, $subUsers);
    }

    /**
     * Apply sub-user filter to a query
     * 
     * @param \Illuminate\Database\Query\Builder|\Illuminate\Database\Eloquent\Builder $query
     * @param string $column (default: 'created_by')
     * @param string|int|null $userId
     * @return \Illuminate\Database\Query\Builder|\Illuminate\Database\Eloquent\Builder
     */
    public function applySubUserFilter($query, $column = 'created_by', $userId = null)
    {
        $userIds = $this->getUserIdsWithSubUsers($userId);
        return $query->whereIn($column, $userIds);
    }

    /**
     * Get count with sub-user filter
     * 
     * @param string $table
     * @param string $column (default: 'created_by')
     * @param string|int|null $userId
     * @return int
     */
    public function countWithSubUserFilter($table, $column = 'created_by', $userId = null)
    {
        $userIds = $this->getUserIdsWithSubUsers($userId);
        return DB::table($table)->whereIn($column, $userIds)->count();
    }
}