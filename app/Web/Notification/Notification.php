<?php
namespace App\Web\Notification;

use Illuminate\Database\Eloquent\Model;

class Notification extends Model
{
    protected $table = 'notifications';

    public $incrementing = false;
    protected $keyType = 'string';
    public $timestamps = false;

    protected $fillable = [
        'id',
        'from_user_id',
        'to_user_id',
        'form_role',
        'to_role',
        'template_id',
        'message',
        'is_read',
        'read_at',
        'status',
        'type'
    ];

    public function template()
    {
        return $this->belongsTo(\App\Web\Notification\NotificationTemplate::class, 'template_id');
    }
}