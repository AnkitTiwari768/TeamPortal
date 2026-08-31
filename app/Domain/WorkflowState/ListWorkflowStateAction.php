<?php 

declare(strict_types=1);

namespace App\Domain\WorkflowState;

use App\Traits\DataTable;

class ListWorkflowStateAction
{
    use DataTable;

    protected array $columns = [
        1 => 'workflow_types.name',
        2 => 'dy_workflow_states.state_key',
        3 => 'dy_workflow_states.state_value',
        4 => 'dy_workflow_states.label',
        5 => 'dy_workflow_states.is_initial',
        6 => 'dy_workflow_states.is_final',
    ];

    public function execute()
    {
        [$limit, $order, $dir, $search, $page, $filters] = $this->getDataTableParams();

        $workflow_type_id = isset($filters['workflow_type_id']) ? $filters['workflow_type_id'] : null;
        $state_key = isset($filters['state_key']) ? $filters['state_key'] : null;
        $label = isset($filters['label']) ? $filters['label'] : null;
        
        $search ??= $this->escape_special_characters($search);

        $query = WorkflowState::select(
            'dy_workflow_states.*',
            'workflow_types.name as workflow_type_name'
        )
        ->leftJoin('workflow_types', 'dy_workflow_states.workflow_type_id', '=', 'workflow_types.id');
        
        if ($workflow_type_id) 
        {
            $query->where('dy_workflow_states.workflow_type_id', $workflow_type_id);
        }
        if ($state_key) 
        {
            $query->where('dy_workflow_states.state_key', $state_key);
        }
        if ($label) 
        {
            $query->where('dy_workflow_states.label', 'like', "%$label%");
        }

        if ($search) 
        {
            $query->where(function($query) use ($search) {
                $query->where('dy_workflow_states.state_key', 'like', "%$search%")
                      ->orWhere('dy_workflow_states.label', 'like', "%$search%")
                      ->orWhere('workflow_types.name', 'like', "%$search%");
            });
        }

        $query->orderBy($order, $dir); 
        
        if ($page) 
        {
            return $this->getDataTableResult(
                WorkflowStateResource::collection($query->paginate($limit))
            );
        }

        return WorkflowStateResource::collection($query->get());
    }
}
