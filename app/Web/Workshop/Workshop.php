<?php

namespace App\Web\Workshop;

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
        'branch_offices_id',
        'venue_address',
        'remark',
        'event_description',
        'schedules',
        'state_id',
        'district_id',
        'sub_district_id',
        'pincode',
        'latitude',
        'longitude',
        'uploaded_ids',
        'financial_year',
        'duration',
        'sub_duration',
        'workshop_category',
        'workshop_mode',
        'conducted_by',
        'target_audience',
        'workshop_start_date',
        'workshop_end_date',
        'created_by',
        'updated_by'
    ];

    protected $casts = [
        'schedules' => 'array',
        'event_for' => 'array',
    ];

    
}
