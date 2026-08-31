<?php

namespace App\Web\MajorComponents;

use App\Traits\UUID;
use Illuminate\Database\Eloquent\Model;
//use App\Http\Api\V1\CountryType\CountryType;

class MajorComponents extends Model 
{
    use UUID;

    protected $table = 'major-components';
    public $module = 'Major Components';
    public $incrementing = false;
    public $timestamps = false;


    protected $fillable = [
        'id',
        'name', 
        'slug',
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