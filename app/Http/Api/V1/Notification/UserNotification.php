<?php

namespace App\Http\Api\V1\Notification;

use App\Core\BaseModel;

class UserNotification extends BaseModel 
{
    protected $table = 'user_notifications';
    public $module = 'User Notification';
   
    protected $fillable = [
        'id',
        'notification_id',
        'user_id',
        'is_read',
        'read_at',
    ];

    protected $casts = [
        'is_read' => 'boolean'
    ];
}