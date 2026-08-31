<?php

namespace App\Http\Api\V1\SubDistrict;

use App\Traits\UUID;
use Illuminate\Database\Eloquent\Model;

class SubDistrict extends Model 
{
    use UUID;

    protected $table = 'sub_districts';
    public $module = 'SubDistrict';
    public $incrementing = false;
    public $timestamps = false;

    protected $fillable = [
        'id',
		'district_id',
		'district_code',
        'name', 
        'slug',
		'code',
        'status',
        'created_at',
        'updated_at',
    ];

    protected $casts = [
        'status' => 'boolean'
    ];
}