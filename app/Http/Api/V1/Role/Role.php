<?php

namespace App\Http\Api\V1\Role;

use App\Core\BaseModel;

class Role extends BaseModel 
{
    protected $table = 'roles';
    public $module = 'Role';
   
    protected $fillable = [
        'id',
        'name', 
        'slug',
        'status',
        'department_id',
        'role_type_id',
		'created_at',
		'updated_at',
        'created_by',
		'updated_by',
        'description'
    ];

    protected $casts = [
        'status' => 'int'
    ];
    
}