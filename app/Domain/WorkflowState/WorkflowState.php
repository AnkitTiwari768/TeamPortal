<?php

declare(strict_types=1);

namespace App\Domain\WorkflowState;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use App\Domain\WorkflowType\WorkflowType;

class WorkflowState extends Model
{
    use HasUuids;

    protected $table = 'dy_workflow_states';

    protected $fillable = [
        'id',
        'workflow_type_id',
        'state_key',
        'state_value',
        'label',
        'is_initial',
        'is_final',
        'shown_roles',
        'created_at',
        'updated_at'
    ];

    protected $casts = [
        'shown_roles' => 'array',
    ];

    public function workflowType()
    {
        return $this->belongsTo(WorkflowType::class, 'workflow_type_id', 'id');
    }
}
