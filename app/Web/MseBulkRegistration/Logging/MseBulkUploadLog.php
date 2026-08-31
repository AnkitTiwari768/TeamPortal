<?php

declare(strict_types=1);

namespace App\Web\MseBulkRegistration\Logging;

use App\Traits\UUID;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class MseBulkUploadLog extends Model
{
    use UUID;

    protected $table = 'team_msme_bulk_upload_logs';
    public $timestamps = false;
    public $incrementing = false;
    protected $keyType = 'string';

    protected $fillable = [
        'id',
        'type',
        'role_type',
        'status',
        'total_processed',
        'success_count',
        'failed_count',
        'retry_count',
        'skipped_count',
        'reconciled_count',
        'pending_count',
        'batch_id',
        'file_name',
        'file_size',
        'file_extension',
        'error_message',
        'created_by',
        'started_at',
        'completed_at',
        'duration_ms',
        'created_at',
        'updated_at',
    ];

    protected $casts = [
        'role_type' => 'integer',
        'total_processed' => 'integer',
        'success_count' => 'integer',
        'failed_count' => 'integer',
        'retry_count' => 'integer',
        'skipped_count' => 'integer',
        'reconciled_count' => 'integer',
        'pending_count' => 'integer',
        'file_size' => 'integer',
        'duration_ms' => 'integer',
        'started_at' => 'datetime',
        'completed_at' => 'datetime',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    public function details(): HasMany
    {
        return $this->hasMany(MseBulkUploadLogDetail::class, 'log_id', 'id');
    }
}
