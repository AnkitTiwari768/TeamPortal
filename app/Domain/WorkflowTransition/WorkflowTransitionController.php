<?php 

declare(strict_types=1);

namespace App\Domain\WorkflowTransition;

use App\Domain\WorkflowState\WorkflowState;
use App\Domain\WorkflowType\WorkflowType;
use App\Http\Api\V1\Role\Role;
use App\Traits\Respond;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class WorkflowTransitionController
{
    use Respond;
    
    public function index()
    {
        $title = __('Workflow Transition List');
        return view('workflow_transitions.index', compact('title'));
    }

    public function getWorkflowTransitions(ListWorkflowTransitionAction $action)
    {
        return $this->success(
            message: 'Workflow transitions retrieved successfully.',
            data: $action->execute(),
        );
    }

    public function create()
    {
        $module_url = 'workflow-transitions';
        $title = __('Create Workflow Transition');
        $workflowTypes = WorkflowType::all()->pluck('name', 'id');
        $workflowStates = WorkflowState::all(); // Full instances to access workflow_type_id logic in blade
        $roles = Role::select('id', 'name')->get();
        return view('workflow_transitions.form', compact('title', 'module_url', 'workflowTypes', 'workflowStates', 'roles'));
    }

    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'workflow_type_id' => 'required',
            'from_state_id' => 'required',
            'to_state_id' => 'required',
            'action' => 'required',
            'allowed_roles' => 'required|array',
            'shown_roles' => 'nullable|array'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => 'error',
                'message' => 'Validation failed',
                'errors' => $validator->errors()
            ]);
        }

        $data = $request->all();
        $data['auto_execute'] = $request->boolean('auto_execute') ? 1 : 0;

        $workflowTransition = WorkflowTransition::create($data);
        
        return response()->json([
            'status' => 'success',
            'message' => 'Workflow transition created successfully',
            'data' => $workflowTransition
        ]);
    }

    public function edit($id)
    {
        $module_url = 'workflow-transitions';
        $row = WorkflowTransition::findOrFail($id);
        $title = __('Edit Workflow Transition');
        $workflowTypes = WorkflowType::all()->pluck('name', 'id');
        $workflowStates = WorkflowState::all();
        $roles = Role::select('id', 'slug', 'name')->get();
        return view('workflow_transitions.form', compact('title', 'row', 'module_url', 'workflowTypes', 'workflowStates', 'roles'));
    }

    public function update(Request $request, $id)
    {
        $workflowTransition = WorkflowTransition::findOrFail($id);
        $validator = Validator::make($request->all(), [
            'workflow_type_id' => 'required',
            'from_state_id' => 'required',
            'to_state_id' => 'required',
            'action' => 'required',
            'allowed_roles' => 'required|array',
            'shown_roles' => 'nullable|array'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => 'error',
                'message' => 'Validation failed',
                'errors' => $validator->errors()
            ]);
        }

        $data = $request->all();
        $data['auto_execute'] = $request->boolean('auto_execute') ? 1 : 0;

        $workflowTransition->update($data);

        return response()->json([
            'status' => 'success',
            'message' => 'Workflow transition updated successfully',
            'data' => $workflowTransition
        ]);
    }

    public function destroy($id)
    {
        $workflowTransition = WorkflowTransition::findOrFail($id);
        $workflowTransition->delete();
        return response()->json([
            'status' => 'success',
            'message' => 'Workflow transition deleted successfully',
            'data' => $workflowTransition
        ]);
    }
}
