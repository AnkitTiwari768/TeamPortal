<?php

declare(strict_types=1);

namespace App\Web\ApplicationStatus;

use Illuminate\Database\Eloquent\Model;

class AdminLog extends Model
{
    protected $table = 'status_tab_admin_logs';
    public $incrementing = false;
    protected $keyType = 'string';

    protected $fillable = [
        'id', 'user_id', 'action', 'target_type', 'target_id',
        'old_values', 'new_values', 'ip_address',
    ];

    protected $casts = [
        'old_values' => 'array',
        'new_values' => 'array',
    ];
}
