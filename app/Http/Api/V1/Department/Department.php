<?php

namespace App\Http\Api\V1\Department;

use App\Traits\Mutators;
use Illuminate\Database\Eloquent\Model;
use App\Core\BaseModel;

class Department extends BaseModel 
{     
    protected $table = 'departments';
    public $module = 'Department';  
    protected $fillable = [
        'id',
        'name', 
        'slug',
        'status',
		'created_by',
		'updated_by',
		'created_at',
		'updated_at'
    ];

    protected $casts = [
        'status' => 'int'
    ];
}