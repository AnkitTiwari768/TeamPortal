<?php

declare(strict_types=1);

namespace App\Web\Batch;

use Illuminate\View\View;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use App\Http\Controllers\ClientController;
use Carbon\Carbon;
use App\Core\BaseRequest;
use App\Traits\HasFileUpload;

class BatchController extends ClientController
{
	use HasFileUpload;

    public function __construct(private BatchService $service)
    {
        $this->service = $service;
    }

    public function createBatch(Request $request): mixed
    {
        $today = Carbon::now();

        // Restrict: must be between 1st and 10th
        /*if ($today->day < 1 || $today->day > 10) {

            return $this->error(['message' => 'Batch should be created before 11th of every month']);
        }*/

        $validator = Validator::make($request->all(), BatchRequest::getRules(), BatchRequest::messages());

        if ($validator->fails()) {
            return $this->error($validator->errors());
        }
        $result = $this->service->store($validator->validated());

        return $this->created($result, 'Batch Created Successfully');
    }

    public function list(ListBatchClaimAction $action, ?string $claimTypeSlug = null)
    {
        $claims = $action->execute($claimTypeSlug);
        return $this->success(data: $claims);
    }

    public function getBatchClaims(ListBatchClaimAction $action, ?string $batchId = null, ?string $status = null)
    {
        $claims = $action->getBatchClaims($batchId, $status);
        return $this->success(data: $claims);
    }
	
	public function uploadCaCertificate(Request $request)
    {
        return $this->uploadFileWithValidation(
            $request, 'file', config('upload.ca_certificate_path'), 
            BaseRequest::getPdfRules(),
            BaseRequest::getPdfRuleMessages()
        );
    }

    public function updateBatch(Request $request): mixed
    {
       
        $result = $this->service->updateBatch($request->all());

        return $this->updated($result, 'Batch Send Successfully');
    }

    public function forwardToONDC(Request $request): mixed
    {
       
        $result = $this->service->sendBatchToONDC($request->all());

        return $this->updated($result, 'Batch Send Successfully');
    }

    public function moveToDrafts(Request $request): mixed
    {

       $validated = $request->validate([
        'batch_id' => 'nullable|uuid|exists:batches,id',
        'claim_id' => 'required|uuid|exists:claims,id',
        'review_status' => 'required'
       ]);

        $result = $this->service->moveToDrafts($validated);

        return $this->updated($result, 'Moved To Drafts Successfully');
    }
}
