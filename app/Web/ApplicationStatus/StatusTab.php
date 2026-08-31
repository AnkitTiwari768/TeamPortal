<?php

declare(strict_types=1);

namespace App\Web\ApplicationStatus;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\HasMany;

class StatusTab extends Model
{
    use SoftDeletes;

    protected $table = 'status_tabs';
    public $incrementing = false;
    protected $keyType = 'string';

    protected $fillable = [
        'id', 'tab_key', 'label', 'tab_icon', 'tab_order',
        'placeholder_text', 'hint_text', 'tracker_class', 'source_table',
        'is_enabled', 'is_otp_required', 'is_receipt_enabled',
        'is_timeline_enabled', 'meta', 'created_by', 'updated_by',
    ];

    protected $casts = [
        'is_enabled'          => 'boolean',
        'is_otp_required'     => 'boolean',
        'is_receipt_enabled'  => 'boolean',
        'is_timeline_enabled' => 'boolean',
        'meta'                => 'array',
    ];

    public function fields(): HasMany
    {
        return $this->hasMany(Field::class, 'status_tab_id')
                    ->orderBy('sort_order');
    }

    public function searchableFields(): HasMany
    {
        return $this->hasMany(Field::class, 'status_tab_id')
                    ->where('is_searchable', true)
                    ->where('is_enabled', true)
                    ->orderBy('sort_order');
    }

    public function timelines(): HasMany
    {
        return $this->hasMany(Timeline::class, 'status_tab_id')
                    ->orderBy('sequence');
    }

    public function searchLogs(): HasMany
    {
        return $this->hasMany(SearchLog::class, 'status_tab_id');
    }

    public function scopeEnabled($query)
    {
        return $query->where('is_enabled', true)->orderBy('tab_order');
    }
}
