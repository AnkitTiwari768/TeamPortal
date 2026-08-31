<?php

namespace App\Web\SubComponents;
use App\Traits\UUID;
use Illuminate\Database\Eloquent\Model;

class SubComponents extends Model 
{
    use UUID;

    protected $table = 'sub-components';
    public $module = 'Sub Components';
    public $incrementing = false;
    public $timestamps = false;

    protected $fillable = [
        'id',
		'major_component_id',
		'component_id',
        'name', 
        'slug',
        'status',
        'created_by',
        'updated_by',
        'created_at',
        'updated_at',
    ];

    protected $casts = [
        'status' => 'boolean'
    ];
}