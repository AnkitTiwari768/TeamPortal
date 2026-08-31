<?php

declare(strict_types=1);

namespace App\Http\Api\V1\AdminWorkshop;

use Illuminate\Foundation\Http\FormRequest;

class UploadDocumentRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'claim_id' => 'nullable|exists:claims,id',
            'document_category_id' => 'nullable|exists:document_categories,id',
            'files' => 'required|array',
            'files.*' => [
                'bail',
                // 'mimes:png,jpg,jpeg',
                'mimes:pdf,xls,xlsx,csv',
                'max:5120',
                function ($attribute, $file, $fail) {
                    $originalName = $file->getClientOriginalName();
                    if (substr_count($originalName, '.') > 1) {
                        $fail('Double extension not allowed.');
                    }
                }
            ],
        ];
    }

    public function messages(): array
    {
        return [
            'claim_id.required'          => __('validation.claim_id.required'),
            'claim_id.exists'            => __('validation.claim_id.exists'),

            'document_category_id.required'    => __('validation.document_category_id.required'),
            'document_category_id.exists'      => __('validation.document_category_id.exists'),

            'files.required'                    => 'Please upload at least one file.',
            'files.array'                        => 'Invalid file structure.',
            'files.*.mimes'                      => 'Only PDF, Xls, Xlsx formats are allowed.',
            'files.*.max'                           => 'Each file must be smaller than 5MB.',
        ];
    }


    public function toDto(): UploadDocumentDto
    {
        return UploadDocumentDto::fromArray($this->validated());
    }
}
