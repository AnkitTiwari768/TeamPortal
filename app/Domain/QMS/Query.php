<?php

namespace App\Domain\QMS;

use App\Traits\UUID;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use App\Models\User;

class Query extends Model
{
    use UUID, SoftDeletes;

    protected $table = 'qms_queries';

    protected $fillable = [
        'id',
        'sender_id',
        'sender_role_id',
        'receiver_id',
        'receiver_role_id',
        'subject',
        'status',
    ];

    public function sender()
    {
        return $this->belongsTo(User::class, 'sender_id');
    }

    public function receiver()
    {
        return $this->belongsTo(User::class, 'receiver_id');
    }

    public function senderRole()
    {
        return $this->belongsTo(\App\Http\Api\V1\Role\Role::class, 'sender_role_id');
    }

    public function receiverRole()
    {
        return $this->belongsTo(\App\Http\Api\V1\Role\Role::class, 'receiver_role_id');
    }

    public function messages()
    {
        return $this->hasMany(QueryMessage::class, 'query_id')->orderBy('created_at', 'asc');
    }

    /**
     * Check if a specific user can reply to this query turn.
     */
    public function canUserReply($user): bool
    {
        if ($this->status === 'closed') {
            return false;
        }

        // If the query is specifically assigned to this user
        if ($this->receiver_id === $user->id) {
            return true;
        }

        // Logic for role-based turn:
        // 1. User must have the receiver_role_id
        // 2. The LAST message must NOT have been sent by anyone with that same role
        
        $userRoles = \DB::table('user_roles')
            ->where('user_id', $user->id)
            ->pluck('role_id')
            ->toArray();

        if ($this->receiver_role_id && in_array($this->receiver_role_id, $userRoles)) {
            $lastMessage = $this->messages()->latest()->first();
            
            if (!$lastMessage) {
                return true; // No messages yet, receiver role can start (though usually sender sends first)
            }

            // Check if the last message was sent by someone from the same role
            $lastSender = $lastMessage->sender;
            if ($lastSender && in_array($lastSender->role_id, $userRoles)) {
                return false; // Already replied in this turn
            }

            return true;
        }

        // If the user is the original sender, they can always reply
        if ($this->sender_id === $user->id) {
            return true;
        }

        return false;
    }

    /**
     * Check if a specific user can close this query.
     */
    public function canUserClose($user): bool
    {
        if ($this->status === 'closed') {
            return false;
        }

        // Creator can always close
        if ($this->sender_id === $user->id) {
            return true;
        }

        // Check for specific roles: snp, bnp, lsp
        $userRoles = \DB::table('user_roles')
            ->join('roles', 'user_roles.role_id', '=', 'roles.id')
            ->where('user_roles.user_id', $user->id)
            ->pluck('roles.slug')
            ->toArray();

        $allowedRoles = ['snp', 'bnp', 'lsp'];
        foreach ($allowedRoles as $role) {
            if (in_array($role, $userRoles)) {
                return true;
            }
        }

        return false;
    }

    public function getUnreadCountForUser(string $userId): int
    {
        $userRoles = \DB::table('user_roles')
            ->where('user_id', $userId)
            ->pluck('role_id')
            ->toArray();

        return $this->messages()
            ->where(function($q) use ($userId, $userRoles) {
                $q->where('receiver_id', $userId)
                  ->orWhereIn('receiver_role_id', $userRoles);
            })
            ->whereNull('read_at')
            ->count();
    }
}
