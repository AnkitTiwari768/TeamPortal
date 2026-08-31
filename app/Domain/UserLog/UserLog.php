<?php

declare(strict_types=1);

namespace App\Domain\UserLog;

use App\Models\User;
use App\Traits\UUID;
use Illuminate\Database\Eloquent\Model;

class UserLog extends Model
{
    use UUID;

    protected $table = 'user_logs';
    public $timestamps = false;
    public $incrementing = false;
    protected $keyType = 'string';

    protected $fillable = [
        'id',
        'user_id',
        'performed_by',
        'action',
        'description',
        'old_values',
        'new_values',
        'created_at',
    ];

    protected $casts = [
        'old_values' => 'array',
        'new_values' => 'array',
        'created_at' => 'datetime',
    ];

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id', 'id');
    }

    public function performedByUser()
    {
        return $this->belongsTo(User::class, 'performed_by', 'id');
    }
}
