<?php

namespace App\Web\Category;

use App\Traits\Mutators;
use Illuminate\Database\Eloquent\Model;
use App\Core\BaseModel;

class Category extends BaseModel 
{     
    protected $table = 'categories';
    public $module = 'Category';  
    protected $fillable = [
        'id',
        'ondc_type_id',
        'name', 
        'code',
        'status',
		'created_at',
		'updated_at'
    ];

    protected $casts = [
        'status' => 'int'
    ];
}