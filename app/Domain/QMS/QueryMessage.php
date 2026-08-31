<?php

namespace App\Domain\QMS;

use App\Traits\UUID;
use Illuminate\Database\Eloquent\Model;
use App\Models\User;

class QueryMessage extends Model
{
    use UUID;

    protected $table = 'qms_messages';

    protected $fillable = [
        'id',
        'query_id',
        'sender_id',
        'receiver_id',
        'receiver_role_id',
        'message',
        'read_at',
    ];

    public function parentQuery()
    {
        return $this->belongsTo(Query::class, 'query_id');
    }

    public function sender()
    {
        return $this->belongsTo(User::class, 'sender_id');
    }

    public function receiver()
    {
        return $this->belongsTo(User::class, 'receiver_id');
    }

    public function receiverRole()
    {
        return $this->belongsTo(\App\Http\Api\V1\Role\Role::class, 'receiver_role_id');
    }

    public function attachments()
    {
        return $this->hasMany(QueryAttachment::class, 'message_id');
    }
}
