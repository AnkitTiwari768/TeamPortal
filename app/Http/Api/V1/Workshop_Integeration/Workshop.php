<?php

namespace App\Http\Api\V1\Workshop_Integeration;

use App\Traits\Mutators;
use Illuminate\Database\Eloquent\Model;
use App\Core\BaseModel;

class Workshop extends BaseModel 
{     
    protected $table = 'workshops';
    public $module = 'workshops';  
    protected $fillable = [
         'id',
        'event_title',
        'event_for',
        'organiser_name',
        'venue_address',
        'remark',
        'event_description',
        'schedules',
        'state_id',
        'district_id',
        'pincode',
        'latitude',
        'longitude',
        'uploaded_ids',
        'created_by',
        'updated_by'
    ];

    protected $casts = [
        'schedules' => 'array',
    ];
}