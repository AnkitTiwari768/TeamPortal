<?php

declare(strict_types=1);

namespace App\Web\BatchWorkflow;

use Illuminate\Foundation\Http\FormRequest;

class StoreBatchClaimWorkflowRequest extends FormRequest
{
    public function authorize(): true
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'batch_id' => 'required|uuid|exists:batches,id',
            'claim_id' => 'required|uuid|exists:claims,id',
            'comments' => 'nullable|max:1000',
        ];
    }
}
