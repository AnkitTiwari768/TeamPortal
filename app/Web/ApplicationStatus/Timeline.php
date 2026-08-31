<?php

declare(strict_types=1);

namespace App\Web\ApplicationStatus;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Timeline extends Model
{
    use SoftDeletes;

    protected $table = 'status_tab_timelines';
    public $incrementing = false;
    protected $keyType = 'string';

    protected $fillable = [
        'id', 'status_tab_id', 'stage_key', 'stage_label',
        'sequence', 'icon', 'color_hex', 'badge_class', 'is_terminal', 'is_enabled',
        'created_by', 'updated_by',
    ];

    protected $casts = [
        'is_enabled' => 'boolean',
    ];

    public function tab()
    {
        return $this->belongsTo(StatusTab::class, 'status_tab_id');
    }
}
