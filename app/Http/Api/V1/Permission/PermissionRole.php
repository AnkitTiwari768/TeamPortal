<?php

namespace App\Http\Api\V1\Permission;

use App\Traits\Mutators;
use Illuminate\Database\Eloquent\Model;
use App\Http\Api\V1\Role\Role;

class PermissionRole extends Model 
{
    use Mutators;

    protected $table = 'permission_role_mapping';
    public $module = 'permission_role';

    protected $fillable = [
        'permission_id',
        'role_id'
    ];

    protected $casts = [
        'status' => 'boolean'
    ];
	
	public function role()
    {
        return $this->belongsTo(Role::class)->withDefault();
    }

}