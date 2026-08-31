<?php

namespace App\Http\Api\V1\LegalOfficer;

use App\Core\BaseModel;

class LegalOfficer extends BaseModel 
{
    protected $table = 'liaison_officers';
    public $module = 'LegalOfficer';
    
    protected $fillable = [
        'id',
        'full_name', 
        'email',
        'mobile',
        'contact_number',
        'designation',
        'address',
		'created_at',
		'updated_at',
        'created_by',
		'updated_by'
    ];
}