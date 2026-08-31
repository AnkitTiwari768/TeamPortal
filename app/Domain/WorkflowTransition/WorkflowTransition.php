<?php

declare(strict_types=1);

namespace App\Domain\WorkflowTransition;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use App\Domain\WorkflowType\WorkflowType;
use App\Domain\WorkflowState\WorkflowState;

class WorkflowTransition extends Model
{
    use HasUuids;

    protected $table = 'dy_workflow_transitions';

    protected $fillable = [
        'id',
        'workflow_type_id',
        'from_state_id',
        'to_state_id',
        'action',
        'allowed_roles',
        'shown_roles',
        'auto_execute',
        'created_at',
        'updated_at'
    ];

    protected $casts = [
        'allowed_roles' => 'array',
        'shown_roles' => 'array',
        'auto_execute' => 'integer'
    ];

    public function workflowType()
    {
        return $this->belongsTo(WorkflowType::class, 'workflow_type_id', 'id');
    }

    public function fromState()
    {
        return $this->belongsTo(WorkflowState::class, 'from_state_id', 'id');
    }

    public function toState()
    {
        return $this->belongsTo(WorkflowState::class, 'to_state_id', 'id');
    }
}
