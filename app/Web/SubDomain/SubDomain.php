<?php

namespace App\Web\SubDomain;

use App\Traits\Mutators;
use Illuminate\Database\Eloquent\Model;
use App\Core\BaseModel;

class SubDomain extends BaseModel 
{     
    protected $table = 'sub_domains';
    public $module = 'SubDomain';  
    protected $fillable = [
        'id',
        'domain_id',
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