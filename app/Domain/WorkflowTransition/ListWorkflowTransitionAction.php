<?php 

declare(strict_types=1);

namespace App\Domain\WorkflowTransition;

use App\Traits\DataTable;

class ListWorkflowTransitionAction
{
    use DataTable;

    protected array $columns = [
        1 => 'workflow_types.name',
        2 => 'from_states.label',
        3 => 'to_states.label',
        4 => 'dy_workflow_transitions.action',
        5 => 'dy_workflow_transitions.auto_execute',
        6 => 'dy_workflow_transitions.allowed_roles',
        7 => 'dy_workflow_transitions.shown_roles',
    ];

    public function execute()
    {
        [$limit, $order, $dir, $search, $page, $filters] = $this->getDataTableParams();

        $workflow_type_id = isset($filters['workflow_type_id']) ? $filters['workflow_type_id'] : null;
        
        $search ??= $this->escape_special_characters($search);

        $query = WorkflowTransition::select(
            'dy_workflow_transitions.*',
            'workflow_types.name as workflow_type_name',
            'from_states.label as from_state_label',
            'to_states.label as to_state_label'
        )
        ->leftJoin('workflow_types', 'dy_workflow_transitions.workflow_type_id', '=', 'workflow_types.id')
        ->leftJoin('dy_workflow_states as from_states', 'dy_workflow_transitions.from_state_id', '=', 'from_states.id')
        ->leftJoin('dy_workflow_states as to_states', 'dy_workflow_transitions.to_state_id', '=', 'to_states.id');
        
        if ($workflow_type_id) 
        {
            $query->where('dy_workflow_transitions.workflow_type_id', $workflow_type_id);
        }

        if ($search) 
        {
            $query->where(function($query) use ($search) {
                $query->where('dy_workflow_transitions.action', 'like', "%$search%")
                      ->orWhere('workflow_types.name', 'like', "%$search%")
                      ->orWhere('from_states.label', 'like', "%$search%")
                      ->orWhere('to_states.label', 'like', "%$search%");
            });
        }

        $query->orderBy($order, $dir); 
        
        if ($page) 
        {
            return $this->getDataTableResult(
                WorkflowTransitionResource::collection($query->paginate($limit))
            );
        }

        return WorkflowTransitionResource::collection($query->get());
    }
}
