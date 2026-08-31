<?php

declare(strict_types=1);

namespace App\Domain\Batch;

use Illuminate\Foundation\Http\FormRequest;

class CreateBatchRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /**
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'claim_type_id' => [
                'required',
                'string',
                'exists:claim_types,slug'
            ],
            'claim_id' => [
                'required',
                'array',
            ],
            'claim_id.*' => [
                'required',
                'uuid',
                'exists:claims,id',
            ],
            'declaration_id' => [
                'nullable',
                'uuid'
            ],
            'is_declaration_agreed' => [
                'nullable',
                'integer'
            ]
        ];
    }

    public function after(): array
    {
        return [
            new ValidateMonthlyBatch
        ];
    }
}
