<?php

namespace App\Http\Api\V1\Module;

use App\Traits\Mutators;
use Illuminate\Database\Eloquent\Model;

class Module extends Model 
{
    use Mutators;

    protected $table = 'modules';
    public $module = 'Module';
    public $incrementing = false;
	public $timestamps = false;

    protected $fillable = [
        'id',
		'parent_id',
        'name', 
        'slug',
		'url',
		'icon',
        'description',
		'created_by',
		'updated_by',
		'created_at',
		'updated_at',
        'status',
        'sort_order'
    ];

    protected $casts = [
        'status' => 'boolean'
    ];

}