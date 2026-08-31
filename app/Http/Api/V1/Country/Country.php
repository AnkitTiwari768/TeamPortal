<?php

namespace App\Http\Api\V1\Country;

use App\Traits\UUID;
use Illuminate\Database\Eloquent\Model;
//use App\Http\Api\V1\CountryType\CountryType;

class Country extends Model 
{
    use UUID;

    protected $table = 'countries';
    public $module = 'Country';
    public $incrementing = false;
    public $timestamps = false;


    protected $fillable = [
        'id',
        'name', 
        'slug',
		'country_code',
		'iso2_code',
        'iso3_code',
        'status',
        'created_by',
        'updated_by',
        'created_at',
        'updated_at',
    ];

    protected $casts = [
        'status' => 'int'
    ];
 
}