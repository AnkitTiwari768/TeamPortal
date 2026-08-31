<?php

namespace App\Http\Api\V1\District;

use App\Traits\UUID;
use Illuminate\Database\Eloquent\Model;

class District extends Model 
{
    use UUID;

    protected $table = 'locations';
    public $module = 'Locations';
    public $incrementing = false;
    public $timestamps = false;

    protected $fillable = [
        'id',
		'country_id',
		'state_id',
        'name', 
        'slug',
		'code',
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