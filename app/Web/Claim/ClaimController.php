<?php

namespace App\Web\Claim;

use App\Http\Controllers\Controller;
use App\Traits\HasResponses;

use App\Web\Claim\ClaimRequest;
use Illuminate\Http\Request;
use App\Domain\DemandGeneration\ListDemandGenerationClaimAction;
use Maatwebsite\Excel\Facades\Excel;

final class ClaimController extends Controller
{
    use HasResponses;


    public function store(ClaimRequest $request, StoreClaimAction $action)
    {

        // $action->execute($request->toDto());
        // return $this->created(message: 'Claim created successfully.');
        $result = $action->execute($request->toDto());
        //dd($result);

        if (($result['success'] ?? true) === false) {
            return response()->json([
                'status'  => false,
                'code'    => $result['code'] ?? null,
                'message' => $result['message'],
                'errors'  => $result['errors'] ?? [],

            ], 422);
        }

        return response()->json([
            'status'  => true,
            'message' => 'Claim created successfully.',
            'message_first'  => $result['message'] ?? [],

        ], 200);

        //return $this->created(message: 'Claim created successfully.');

    }

    public function list(ListClaimAction $action, ?string $claimTypeSlug = null)
    {
        if ($claimTypeSlug === 'claim-for-demand-generation') {
            return $this->success(data: app(ListDemandGenerationClaimAction::class)->execute());
        }

        if ($claimTypeSlug === 'claim-for-ai-cataloguing') {
            return $this->success(data: app(\App\Domain\AICataloguingClaim\ListAICataloguingClaimAction::class)->execute());
        }

        $claims = $action->execute($claimTypeSlug);

        return $this->success(data: $claims);
    }
    public function claimOrdersList(string $claimId, ListClaimOrderAction $action)
    {
        $orders = $action->execute($claimId);
        return $this->success(data: $orders);
    }

    public function exportClaimOrders(string $claimId)
    {
        return Excel::download(new ClaimOrdersExport($claimId), 'claim_orders' . '.xlsx');
    }

    public function reject_list(RejectListClaimAction $action, ?string $claimTypeSlug = null)
    {
        $claims = $action->execute($claimTypeSlug);
        return $this->success(data: $claims);
    }


    public function batchQueryList(BatchQueryListAction $action, ?string $claimTypeSlug = null)
    {
        $claims = $action->execute($claimTypeSlug);

        return $this->success(data: $claims);
    }


    public function batchClaimQueryList(BatchClaimQueryListAction $query, string $batchId)
    {
        return $this->success(data: $query->execute($batchId));
    }


    public function proceedBatchQuery(Request $request, ProcessBatchQueryAction $action)
    {
        $rules = [
            'batch_id' => ['required', 'uuid', 'exists:dy_batches,id'],
            'comments' => 'nullable|max:1000',
            'file_upload_id' => 'required|exists:file_uploads,file_system_name',
            'document_category_id' => 'required|uuid|exists:document_categories,id',
        ];

        $validated = $request->validate($rules);

        $result = $action->execute($validated);
        return $this->success(message: 'Batch processed successfully.', data: $result);
    }



    public function upload(UploadClaimRequest $request, UploadClaimAction $action)
    {
        $result = $action->execute($request, $request->toDto());
        return $this->success(message: 'Claim documents uploaded successfully.', data: $result);
    }


    public function getMsmeDetails(string $id, ClaimService $service)
    {
        $details = $service->getMsmeDetails($id);
        return $this->success(data: (array) $details);
    }


    public function claimMoveToDrafts(Request $request, ClaimService $service)
    {

        $validated = $request->validate([
            'claim_id' => 'required|uuid|exists:claims,id',
        ]);

        $result = $service->claimMoveToDrafts($validated);

        return $this->success(message: 'Moved To Drafts Successfully');
    }

    public function destroy(string $id, DeleteClaimAction $action)
    {
        $action->execute($id);
        return $this->success(message: 'Claim deleted successfully.');
    }
}
