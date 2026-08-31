<?php 

declare(strict_types=1);

namespace App\Domain\WorkflowTransition;

use Illuminate\Http\Resources\Json\JsonResource;
use App\Http\Api\V1\Role\Role;

class WorkflowTransitionResource extends JsonResource
{
    public function toArray($request): array
    {
        $roleIds = $this->allowed_roles;
        if (is_string($roleIds)) {
            $roleIds = json_decode($roleIds, true);
        }

        $roleNames = [];
        if (is_array($roleIds) && count($roleIds) > 0) {
            $roleNames = Role::whereIn('id', $roleIds)->pluck('name')->toArray();
        }

        $shownroleIds = $this->shown_roles;
        if (is_string($shownroleIds)) {
            $shownroleIds = json_decode($shownroleIds, true);
           
        }
 
        $shownroleNames = [];
        if (is_array($shownroleIds) && count($shownroleIds) > 0) {
            $shownroleNames = Role::whereIn('id', $shownroleIds)->pluck('name')->toArray();
        }

        return [
            'id' => $this->id,
            'workflow_type_name' => $this->workflow_type_name ?? ($this->workflowType ? $this->workflowType->name : ''),
            'from_state_label' => $this->from_state_label ?? ($this->fromState ? $this->fromState->label : ''),
            'to_state_label' => $this->to_state_label ?? ($this->toState ? $this->toState->label : ''),
            'action' => $this->action,
            'allowed_roles' => $roleIds,
            'allowed_roles_names' => implode(', ', $roleNames),
            'shown_roles' => $this->shown_roles,
            'shown_roles_names' => implode(', ', $shownroleNames),
            'auto_execute' => (bool)$this->auto_execute,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at
        ];
    }
}
