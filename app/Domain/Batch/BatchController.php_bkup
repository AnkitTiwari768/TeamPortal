<?php

declare(strict_types=1);

namespace App\Domain\Batch;

use App\Traits\Respond;
use Illuminate\Http\Request;

final readonly class BatchController
{
    use Respond;

    public function index(BatchListQuery $query, ?string $claimTypeSlug = null)
    {
        // dd($claimTypeSlug);
        return $this->success(data: $query->execute($claimTypeSlug));
    }



    public function create(CreateBatchRequest $request, CreateBatchAction $action)
    {
        $result = $action->execute(CreateBatchDto::fromValidated($request->validated()));
        return $this->created($result, 'Batch Created Successfully');
    }



    public function proceed(Request $request, ProcessBatchAction $action)
    {
        //dd($request->all());
        $rules = [
            'batch_id' => ['required', 'uuid', 'exists:dy_batches,id'],
            'claim_type' => ['required', 'exists:claim_types,slug'],
            'comments' => 'nullable|max:1000',
            'action' => 'nullable',
            'gst_type' => ['nullable', 'in:1,2'],
            'gst_percentage' => [
                'nullable',
                'required_if:gst_type,1',
                'numeric',
                'between:0,100',
            ],

            'cgst_percentage' => [
                'nullable',
                'required_if:gst_type,2',
                'numeric',
                'between:0,100',
            ],

            'sgst_percentage' => [
                'nullable',
                'required_if:gst_type,2',
                'numeric',
                'between:0,100',
            ],
            'tds' => 'nullable|numeric',
            'sgst' => 'nullable|numeric',
            'cgst' => 'nullable|numeric',
            'igst' => 'nullable|numeric',
            'sanction_order_number' => 'nullable|string|max:255',
            'sanction_order_date' => 'nullable|date',
        ];


        if ($request->action != 'proceed-batch-workflow-to-ca') {
            if (hasRole('ca') || hasRole('snp') || hasRole('bnp') || hasRole('lsp')) { //added snp role for invoice
                $rules['file_upload_id'] = [
                    'required',
                    'exists:file_uploads,file_system_name',
                    function ($attribute, $value, $fail) {
                        $file = \Illuminate\Support\Facades\DB::table('file_uploads')->where('file_system_name', $value)->first();
                        if ($file) {
                            if (strtolower($file->file_extension) !== 'pdf') {
                                $fail('Please upload a file in pdf format.');
                            }
                            if ($file->file_size > 10485760) {
                                $fail('maximum size should be 10MB.');
                            }
                        }
                    }
                ];
                $rules['document_category_id'] = 'required|uuid|exists:document_categories,id';
            }
        }
        // Custom validation messages
        $messages = [
            'file_upload_id.required' => 'File upload is mandatory. Kindly upload the required file to proceed.',
            'file_upload_id.exists' => 'The selected file is invalid.',
        ];

        $validator = \Illuminate\Support\Facades\Validator::make($request->all(), $rules, $messages);

        if ($validator->fails()) {
            return response()->json([
                'status' => false,
                'message' => $validator->errors()->first(),
                'errors' => $validator->errors()
            ], 422);
        }

        $validated = $validator->validated();

        try {
            $result = $action->execute($validated);
            $msg = 'Batch processed successfully';
            if (session()->has('warning_message')) {
                $msg .= '. ' . session('warning_message');
            }
            return $this->created($result, $msg);
        } catch (\Exception $e) {
            return response()->json([
                'status' => false,
                'message' => $e->getMessage()
            ], 422);
        }
    }


    public function batchPaymentCompleted(Request $request, BatchPaymentAction $action)
    {

        $rules = [
            'batch_id' => ['required', 'uuid', 'exists:dy_batches,id'],
            'claim_type' => ['required', 'exists:claim_types,slug'],
            'pfms_number' => 'required|string|max:255',
            'sanction_order_number' => 'nullable|string|max:255',
            'sanction_order_date' => 'nullable|date',
            'comment' => 'nullable|max:1000'
        ];


        $validated = $request->validate($rules);

        try {
            $result = $action->execute($validated);
            $msg = 'Payment completed';
            if (session()->has('warning_message')) {
                $msg .= '. ' . session('warning_message');
            }
            return $this->created($result, $msg);
        } catch (\Exception $e) {
            return response()->json([
                'status' => false,
                'message' => $e->getMessage()
            ], 422);
        }
    }

    public function exportClaims(string $batchId)
    {
        $batch = \Illuminate\Support\Facades\DB::table('dy_batches')
            ->join('claim_types', 'claim_types.id', '=', 'dy_batches.claim_type_id')
            ->where('dy_batches.id', $batchId)
            ->select('dy_batches.batch_number', 'claim_types.slug')
            ->first();

        if (!$batch) {
            abort(404, 'Batch not found');
        }

        $export = new BatchClaimsExport($batchId, $batch->slug);
        $fileName = 'claims_' . str_replace('/', '_', $batch->batch_number) . '.xlsx';

        return \Maatwebsite\Excel\Facades\Excel::download($export, $fileName);
    }
}
