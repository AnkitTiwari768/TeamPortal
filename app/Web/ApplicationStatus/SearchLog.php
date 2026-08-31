<?php

declare(strict_types=1);

namespace App\Web\ApplicationStatus;

use Illuminate\Database\Eloquent\Model;

class SearchLog extends Model
{
    protected $table = 'status_tab_search_logs';
    public $incrementing = false;
    protected $keyType = 'string';

    protected $fillable = [
        'id', 'status_tab_id', 'identifier_hash', 'identifier_type',
        'result_status_key', 'ip_address', 'user_agent',
    ];

    public function tab()
    {
        return $this->belongsTo(StatusTab::class, 'status_tab_id');
    }
}
