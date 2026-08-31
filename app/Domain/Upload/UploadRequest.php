<?php

declare(strict_types=1);

namespace App\Domain\Upload;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\DB;

class UploadRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $documentValidationRules = $this->getDocumentValidationRules(request()->input('document_category_id'));
        // dd($documentValidationRules);
        return [
            'document_category_id' => 'required|exists:document_categories,slug',
            'file' => [
                'bail',
                'required',
                'mimes:' . $documentValidationRules['mimes'], // 'mimes:jpeg,jpg,png,pdf',
                'max:' . $documentValidationRules['max'], //'max:51200',
                function (string $attribute, mixed $value, \Closure $fail) {
                    $originalName = request()->file('file')->getClientOriginalName();
                    $files = explode('.', $originalName);

                    if (count($files) > 2) {
                        $fail(__('validation.file.double_extension'));
                    }

                    [$name, $extension] = explode('.', $originalName);

                    if (strtolower($extension) !== 'jpeg' && strtolower($extension) !== 'jpg' && strtolower($extension) !== 'png' && strtolower($extension) !== 'pdf') {
                        $fail(__('validation.file.invalid_format'));
                    }
                },
            ],
        ];
    }

    public function messages(): array
    {
        $documentValidationRules = $this->getDocumentValidationRules(
            request()->input('document_category_id')
        );

        $maxSizeKB = $documentValidationRules['max'] ?? 0;
        $maxSizeMB = round($maxSizeKB / 1024, 2);
        return [
            'document_category_id.required'    => __('validation.document_category_id.required'),
            'document_category_id.exists'      => __('validation.document_category_id.exists'),

            'file.required'                    => 'file is required',
            'file.mimes'                       => 'Only ' . strtoupper($documentValidationRules['mimes']) . ' file is allowed.',
            'file.max'                         => 'Maximum file size allowed is ' . $maxSizeMB . ' MB.',
        ];
    }


    private function getDocumentValidationRules(string $documentCategorySlug): array
    {
        $rules = DB::table('document_categories')
            ->where('slug', $documentCategorySlug)
            ->value('rules');

        if ($rules) {
            return (array) json_decode($rules);
        }

        return [];
    }
}
