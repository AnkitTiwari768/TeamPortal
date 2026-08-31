<?php

declare(strict_types=1);

namespace App\Web\Workshop;

use Illuminate\Foundation\Http\FormRequest;

class UploadSupportingDocumentRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'file' => [
                'required',
                'file',
                'mimes:pdf,jpg,jpeg,png,webp',
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
            'file.required' => 'Please upload a file.',
            'file.file'     => 'The uploaded file is invalid.',
            'file.mimes'    => 'Only PDF, JPG, JPEG, PNG, WEBP formats are allowed.',
            'file.max'      => 'The file must be smaller than 5MB.',
        ];
    }
}
