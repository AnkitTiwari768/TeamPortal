<?php

declare(strict_types=1);

namespace App\Web\Claim;

use Illuminate\Foundation\Http\FormRequest;

class UploadClaimRequest extends FormRequest
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
            'file' => [
                'bail',
                'required',
                'mimes:pdf',
                'max:5120', // 5 MB
                function (string $attribute, mixed $value, \Closure $fail) {
                    $originalName = request()->file('file')->getClientOriginalName();
                    $files = explode('.', $originalName);

                    if (count($files) > 2) {
                        $fail(__('validation.file.double_extension'));
                    }

                    [$name, $extension] = explode('.', $originalName);

                    if (strtolower($extension) !== 'pdf') {
                        
                        $fail(__('validation.file.invalid_format'));
                    }
                },
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

            'file.required'                    => __('validation.file.required'),
            'file.mimes'                       => __('validation.file.mimes'),
            'file.max'                         => __('validation.file.max'),
        ];
    }


    public function toDto(): UploadClaimDto
    {
        return UploadClaimDto::fromArray($this->validated());
    }
}
