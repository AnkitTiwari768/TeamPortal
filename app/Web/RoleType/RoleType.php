<?php

namespace App\Web\RoleType;

use App\Core\BaseModel;

class RoleType extends BaseModel
{
    protected $table = 'role_types';
    public $module = 'Role Type';

    protected $fillable = [
        'id',
        'name',
        'slug',
        'status',
        'created_at',
        'updated_at'
    ];

    protected $casts = [
        'status' => 'int'
    ];
}
