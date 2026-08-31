<?php

declare(strict_types=1);

namespace App\Web\ApplicationStatus;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Field extends Model
{
    use SoftDeletes;

    protected $table = 'status_tab_fields';
    public $incrementing = false;
    protected $keyType = 'string';

    protected $fillable = [
        'id', 'status_tab_id', 'field_key', 'field_label',
        'field_type', 'placeholder', 'validation_regex', 'validation_message',
        'is_searchable', 'is_visible_on_result', 'is_required', 'is_enabled', 'sort_order',
        'created_by', 'updated_by',
    ];

    protected $casts = [
        'is_searchable'        => 'boolean',
        'is_visible_on_result' => 'boolean',
        'is_required'          => 'boolean',
        'is_enabled'           => 'boolean',
    ];

    public function tab()
    {
        return $this->belongsTo(StatusTab::class, 'status_tab_id');
    }
}
