<?php

namespace App\Http\Api\V1\Designation; 
use App\Core\BaseModel;
class Designation extends BaseModel 
{ 
    protected $table = 'services';
    public $module = 'Services'; 

    protected $fillable = [
        'id',
        'department_id', 
        'name', 
        'slug',
		'created_by',
		'updated_by',
		'created_at',
		'updated_at',
        'status'
    ];

    protected $casts = [
        'status' => 'boolean'
    ];
}