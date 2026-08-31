<?php

namespace App\Http\Api\V1\Email;

use App\Core\BaseModel;

class Email extends BaseModel 
{
    protected $table = 'email_logs';
    public $module = 'Email';
    
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