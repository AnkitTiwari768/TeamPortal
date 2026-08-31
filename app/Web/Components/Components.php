<?php

namespace App\Web\Components;

use App\Traits\UUID;
use Illuminate\Database\Eloquent\Model;

class Components extends Model 
{
    use UUID;

    protected $table = 'components';
    public $module = 'Components';
    public $incrementing = false;
    public $timestamps = false;

    protected $fillable = [
        'id',
		'major_component_id',
        'name', 
        'slug',
        'status',
        'created_by',
        'updated_by',
        'created_at',
        'updated_at'
    ];

    protected $casts = [
        'status' => 'boolean'
    ];
}