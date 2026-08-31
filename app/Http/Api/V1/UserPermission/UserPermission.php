<?php

namespace App\Http\Api\V1\UserPermission;
 
use App\Traits\Mutators; 
use Illuminate\Database\Eloquent\Model;

class UserPermission extends Model 
{
    use Mutators;

    protected $table = 'user_permissions';
    public $module = 'user permissions';
    public $timestamps = false;

    protected $fillable = [
        'user_id',
        'permission_id',
        'is_default',
        //'group_permission_id'
    ];

    public function scopeUserId($query, $userId)
    {
        return $query->where('user_id', $userId);
    }

    public function scopeDefault($query)
    {
        return $query->where('is_default', true);
    }
}