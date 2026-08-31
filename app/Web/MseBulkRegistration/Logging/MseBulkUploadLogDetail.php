<?php

declare(strict_types=1);

namespace App\Web\MseBulkRegistration\Logging;

use App\Traits\UUID;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class MseBulkUploadLogDetail extends Model
{
    use UUID;

    protected $table = 'team_msme_bulk_upload_log_details';
    public $timestamps = false;
    public $incrementing = false;
    protected $keyType = 'string';

    protected $fillable = [
        'id',
        'log_id',
        'udyam_no',
        'mobile',
        'role_type',
        'status',
        'attempt_number',
        'dependency_details',
        'error_message',
        'reference_table',
        'reference_id',
        'created_at',
    ];

    protected $casts = [
        'role_type' => 'integer',
        'attempt_number' => 'integer',
        'dependency_details' => 'array',
        'created_at' => 'datetime',
    ];

    public function log(): BelongsTo
    {
        return $this->belongsTo(MseBulkUploadLog::class, 'log_id', 'id');
    }
}
