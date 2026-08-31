<?php

namespace App\Http\Api\V1\Sms;

use App\Core\BaseModel;

class Sms extends BaseModel 
{
    protected $table = 'Sms_logs';
    public $module = 'Sms';
    
    protected $fillable = [
        'id',
        'from_email',
        'to_email',
        'cc_email',
        'subject',
        'body',
		'created_at',
        'created_by'
    ];
}