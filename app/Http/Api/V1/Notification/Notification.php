<?php

namespace App\Http\Api\V1\Notification;

use App\Core\BaseModel;

class Notification extends BaseModel 
{
    protected $table = 'notifications';
    public $module = 'Notification';
   
    protected $fillable = [
        'id',
        'notification_type_id',
        'user_id',
        'message',
        'subject',
        'created_at'
    ];

    protected $casts = [
        'notification_type_id' => 'int',
        'is_read' => 'boolean'
    ];
}