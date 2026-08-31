<?php

namespace App\Web\AovCategory;

use App\Traits\Mutators;
use Illuminate\Database\Eloquent\Model;
use App\Core\BaseModel;

class AovCategory extends BaseModel 
{     
    protected $table = 'sub_domains';
    public $module = 'Aov Category';  
    protected $fillable = [
        'id',
        'name',
        'code',
        'ondc_domain_id',
        'aov_category_id',
        'aov_grouping_type',
        'minimum_order_value',
        'status',
		'created_at',
		'updated_at',
		'created_by',
		'updated_by'
    ];

    protected $casts = [
        'status' => 'int'
    ];
}