<?php 

declare(strict_types=1);

namespace App\Domain\WorkflowType;

use App\Domain\WorkflowType\WorkflowType;
use App\Traits\Respond;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class WorkflowTypeController
{
    use Respond;
    
    public function index()
    {
        $title = __('Workflow Type List');
        return view('workflow_types.index', compact('title'));
    }

    public function getWorkflowTypes(ListWorkflowTypeAction $action)
    {
        return $this->success(
            message: 'Workflow types retrieved successfully.',
            data: $action->execute(),
        );
    }

    public function create()
    {
        $module_url = 'workflow-types';
        $title = __('Create Workflow Type');
        return view('workflow_types.form', compact('title', 'module_url'));
    }

    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required',
            'slug' => 'required',
            'status' => 'required'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => 'error',
                'message' => 'Validation failed',
                'errors' => $validator->errors()
            ]);
        }

        $workflowType = WorkflowType::create($request->all());
        
        return response()->json([
            'status' => 'success',
            'message' => 'Workflow type created successfully',
            'data' => $workflowType
        ]);
    }

    public function edit($id)
    {
        $module_url = 'workflow-types';
        $row = WorkflowType::findOrFail($id);
        $title = __('Edit Workflow Type');
        return view('workflow_types.form', compact('title', 'row', 'module_url'));
    }

    public function update(Request $request, $id)
    {
        $workflowType = WorkflowType::findOrFail($id);
        $validator = Validator::make($request->all(), [
            'name' => 'required',
            'slug' => 'required',
            'status' => 'required'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => 'error',
                'message' => 'Validation failed',
                'errors' => $validator->errors()
            ]);
        }

        $workflowType->update($request->all());

        return response()->json([
            'status' => 'success',
            'message' => 'Workflow type updated successfully',
            'data' => $workflowType
        ]);
    }

    public function destroy($id)
    {
        $workflowType = WorkflowType::findOrFail($id);
        $workflowType->delete();
        return response()->json([
            'status' => 'success',
            'message' => 'Workflow type deleted successfully',
            'data' => $workflowType
        ]);
    }
}
