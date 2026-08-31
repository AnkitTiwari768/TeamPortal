<?php

namespace App\Http\Api\V1\Permission;

use App\Traits\UUID;
use Illuminate\Database\Eloquent\Model;

class Permission extends Model 
{
    use UUID;

    protected $table = 'permissions';
    public $module = 'Permission';
    public $incrementing = false;

    protected $fillable = [
        'id',
        'name',
		'module_id',		
        'slug',
		'description',
        'status'
    ];

    protected $casts = [
        'status' => 'boolean'
    ];
	
	public function permissionRole()
    {
        return $this->hasMany(PermissionRole::class,'permission_id', 'id');
    }	
    	
}