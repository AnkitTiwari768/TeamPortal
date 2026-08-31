<?php 

declare(strict_types=1);

namespace App\Domain\WorkflowState;

use App\Domain\WorkflowState\WorkflowState;
use App\Domain\WorkflowType\WorkflowType;
use App\Traits\Respond;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class WorkflowStateController
{
    use Respond;
    
    public function index()
    {
        $title = __('Workflow State List');
        return view('workflow_states.index', compact('title'));
    }

    public function getWorkflowStates(ListWorkflowStateAction $action)
    {
        return $this->success(
            message: 'Workflow states retrieved successfully.',
            data: $action->execute(),
        );
    }

    public function create()
    {
        $module_url = 'workflow-states';
        $title = __('Create Workflow State');
        $workflowTypes = WorkflowType::all()->pluck('name', 'id');
        return view('workflow_states.form', compact('title', 'module_url', 'workflowTypes'));
    }

    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'workflow_type_id' => 'required',
            'state_key' => 'required',
            'state_value' => 'required|numeric',
            'label' => 'required',
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
        $data['is_initial'] = $request->boolean('is_initial') ? 1 : 0;
        $data['is_final'] = $request->boolean('is_final') ? 1 : 0;

        $workflowState = WorkflowState::create($data);
        
        return response()->json([
            'status' => 'success',
            'message' => 'Workflow state created successfully',
            'data' => $workflowState
        ]);
    }

    public function edit($id)
    {
        $module_url = 'workflow-states';
        $row = WorkflowState::findOrFail($id);
        $title = __('Edit Workflow State');
        $workflowTypes = WorkflowType::all()->pluck('name', 'id');
        return view('workflow_states.form', compact('title', 'row', 'module_url', 'workflowTypes'));
    }

    public function update(Request $request, $id)
    {
        $workflowState = WorkflowState::findOrFail($id);
        $validator = Validator::make($request->all(), [
            'workflow_type_id' => 'required',
            'state_key' => 'required',
            'state_value' => 'required|numeric',
            'label' => 'required',
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
        $data['is_initial'] = $request->boolean('is_initial') ? 1 : 0;
        $data['is_final'] = $request->boolean('is_final') ? 1 : 0;

        $workflowState->update($data);  

        return response()->json([
            'status' => 'success',
            'message' => 'Workflow state updated successfully',
            'data' => $workflowState
        ]);
    }

    public function destroy($id)
    {
        $workflowState = WorkflowState::findOrFail($id);
        $workflowState->delete();
        return response()->json([
            'status' => 'success',
            'message' => 'Workflow state deleted successfully',
            'data' => $workflowState
        ]);
    }
}
