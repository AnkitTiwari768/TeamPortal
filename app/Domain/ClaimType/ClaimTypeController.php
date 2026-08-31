<?php 

declare(strict_types=1);

namespace App\Domain\ClaimType;

use App\Domain\ClaimType\ClaimType;
use App\Traits\Respond;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class ClaimTypeController
{
    use Respond;
    
    public function index()
    {
        $title = __('Claim Type List');
        return view('claim-type.index', compact('title'));
    }

    public function getClaimTypes(ListClaimTypeAction $action)
    {
        return $this->success(
            message: 'Claim types retrieved successfully.',
            data: $action->execute(),
        );
    }

    public function create()
    {
        $module_url = 'claim-types';
        $title = __('Create Claim Type');
        return view('claim-type.form', compact('title', 'module_url'));
    }

    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required',
            'slug' => 'required',
            'short_name' => 'required',
            'status' => 'required'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => 'error',
                'message' => 'Validation failed',
                'errors' => $validator->errors()
            ]);
        }

        $claimType = ClaimType::create($request->all());
        
        return response()->json([
            'status' => 'success',
            'message' => 'Claim type created successfully',
            'data' => $claimType
        ]);
    }

    public function edit($id)
    {
        $module_url = 'claim-types';
        $row = ClaimType::findOrFail($id);
        $title = __('Edit Claim Type');
        return view('claim-type.form', compact('title', 'row', 'module_url'));
    }

    public function update(Request $request, $id)
    {
        $claimType = ClaimType::findOrFail($id);
        $validator = Validator::make($request->all(), [
            'name' => 'required',
            'slug' => 'required',
            'short_name' => 'required',
            'status' => 'required'
        ])->validate();

        if ($validator->fails()) {
            return response()->json([
                'status' => 'error',
                'message' => 'Validation failed',
                'errors' => $validator->errors()
            ]);
        }

        $claimType->update($request->all());

        return response()->json([
            'status' => 'success',
            'message' => 'Claim type updated successfully',
            'data' => $claimType
        ]);
    }

    public function destroy($id)
    {
        $claimType = ClaimType::findOrFail($id);
        $claimType->delete();
        return response()->json([
            'status' => 'success',
            'message' => 'Claim type deleted successfully',
            'data' => $claimType
        ]);
    }
}