<?php

namespace App\Web\PmvProductCategory;

use App\Core\BaseModel;

class PmvProductCategory extends BaseModel
{
    protected $table = 'pm_vishwakarma_categories';

    protected $fillable = [
        'name',
        'slug',
        'subdomain_id',
        'status',
        'created_by',
        'updated_by'
    ];

    protected $casts = [
        'status' => 'integer'
    ];
}