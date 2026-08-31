<?php 

declare(strict_types=1);

namespace App\Domain\WorkflowState;

use Illuminate\Http\Resources\Json\JsonResource;

class WorkflowStateResource extends JsonResource
{
    public function toArray($request): array
    {
        return [
            'id' => $this->id,
            'workflow_type_name' => $this->workflow_type_name ?? ($this->workflowType ? $this->workflowType->name : ''),
            'workflow_type_id' => $this->workflow_type_id,
            'state_key' => $this->state_key,
            'state_value' => $this->state_value,
            'label' => $this->label,
            'is_initial' => (bool)$this->is_initial,
            'is_final' => (bool)$this->is_final,
            'shown_roles' => $this->shown_roles,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at
        ];
    }
}
