<?php

declare(strict_types=1);

namespace App\Core;

use App\Rules\FileName;
use App\Enums\ReviewStatus;
use Illuminate\Validation\Rule;

class BaseRequest
{
    public static function getZipOrPdfRules(): array
    {
        return [
            'file' => 'required'
        ];
    }

    public static function getPdfRules(?int $defaultSize = 10240): array
    {
        return [
            'file' => [
                'bail',
                'required',
                //'mimes:pdf',
                'max:' . $defaultSize,
                function (string $attribute, mixed $value, \Closure $fail) {
                    $originalName = request()->file('file')->getClientOriginalName();
                    $files = explode('.', $originalName);
                    //dd($files);
                    if (count($files) > 2)
                        $fail('Double extension not allowed');

                    [$name, $extension] = explode('.', $originalName);

                    if (strtolower($extension) !== 'pdf')
                        $fail('The file format must be a pdf');
                },


                //new FileName
            ]
        ];
    }

    public static function getExcelRules(): array
    {
        return [
            'file' => [
                'required',
                'mimes:xlsx,xls',
                'mimetypes:application/csv,application/excel,application/vnd.ms-excel,application/vnd.msexcel,application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
                'max:20480',
                function (string $attribute, mixed $value, \Closure $fail) {
                    $originalName = request()->file('file')->getClientOriginalName();
                    $files = explode('.', $originalName);
                    if (count($files) > 2)
                        $fail('Double extension not allowed');
                    [$name, $extension] = explode('.', $originalName);

                    if (strtolower($extension) !== 'xlsx' && strtolower($extension) !== 'xls') {
                        $fail('The file format must be a xls or xlsx');
                    }
                },
                //new FileName
            ]
        ];
    }

    public static function getPdfExcelRules(?int $defaultSize = 20480): array
    {
        return [
            'file' => [
                'bail',
                'required',
                'mimes:pdf,csv,xlsx,xls,txt,octet-stream', // Basic extension validation
                'mimetypes:application/pdf,application/csv,application/vnd.ms-excel,application/vnd.openxmlformats-officedocument.spreadsheetml.sheet,text/csv,text/plain,application/octet-stream', // Add text/plain
                'max:' . $defaultSize,
                function (string $attribute, mixed $value, \Closure $fail) {
                    $file = request()->file('file');
                    $originalName = $file->getClientOriginalName();

                    // Check for double extensions
                    if (substr_count($originalName, '.') > 1) {
                        $fail('Double extension is not allowed.');
                    }

                    // Ensure only specific extensions
                    $extension = strtolower($file->getClientOriginalExtension());
                    if (!in_array($extension, ['xls', 'xlsx', 'pdf', 'csv'])) {
                        $fail('The file format must be one of: csv, xls, xlsx, pdf.');
                    }
                }
            ]
        ];
    }

    public static function getImageRules(?int $defaultSize = 100): array
    {
        return [
            'file' => [
                'bail',
                'required',
                'mimes:jpeg,jpg,png,pdf',
                'max:' . $defaultSize,
                function (string $attribute, mixed $value, \Closure $fail) {
                    $originalName = request()->file('file')->getClientOriginalName();
                    $files = explode('.', $originalName);
                    if (count($files) > 2)
                        $fail('Double extension not allowed');
                    [$name, $extension] = explode('.', $originalName);
                    if (strtolower($extension) !== 'jpeg' && strtolower($extension) !== 'jpg' && strtolower($extension) !== 'png' && strtolower($extension) !== 'pdf') {
                        $fail('The file format must be a png,jpg,jpeg,pdf');
                    }
                },
                //new FileName
            ]
        ];
    }


    public static function customFileUploadSecurityRules(string $mimes)
    {
        return function (string $attribute, mixed $value, \Closure $fail) use ($mimes) {
            $originalName = request()->file('file')->getClientOriginalName();
            $files = explode('.', $originalName);
            if (count($files) > 2)
                $fail(__('validation.double_extension_error'));
            [$name, $extension] = explode('.', $originalName);
            if (strtolower($extension) !== 'jpeg' && strtolower($extension) !== 'jpg' && strtolower($extension) !== 'png' && strtolower($extension) !== 'pdf') {
                $fail('validation_mimes_error', ['mimes' => $mimes]);
            }
        };
    }

    public static function getCommonFileRules(?int $defaultSize = 200, ?string $mimes = null): array
    {
        if (! $mimes) {
            $mimes = 'jpeg,jpg,png,pdf';
        }

        return [
            'file' => [
                'bail',
                'required',
                'mimes:jpeg,jpg,png,pdf',
                'max:' . $defaultSize,
                static::customFileUploadSecurityRules(mimes: $mimes)

            ]
        ];
    }


    public static function getReviewStatusRules(): array
    {
        return [
            'bail',
            'required',
            Rule::in([
                ReviewStatus::Approve->value,
                ReviewStatus::Revert->value,
                ReviewStatus::Reject->value,
            ]),
        ];
    }

    public static function getRemarksRules(): array
    {
        return [
            'nullable',
            'string'
        ];
    }

    public static function getCommonRuleMessages(?int $defaultSize = 10): array
    {
        return [
            'file.required' => __('message.pdf_required'),
            'file.mimes' => __('message.pdf_mime'),
            'file.max' => __('message.pdf_size', ['filesize' => $defaultSize])
        ];
    }

    public static function getPdfRuleMessages(?int $defaultSize = 10240): array
    {
        return [
            'file.required' => __('message.pdf_required'),
            'file.mimes' => __('message.pdf_mime'),
            'file.max' => __('message.pdf_size', ['filesize' => $defaultSize / 1024])
        ];
    }

    public static function getExcelRuleMessages(): array
    {
        return [
            'file.required' => __('message.excel_required'),
            'file.mimes' => __('message.excel_mime'),
            'mimetypes' => __('message.excel_mime'),
            'file.max' => __('message.excel_size', ['filesize' => 20])
        ];
    }

    public static function getPdfExcelRuleMessages($defaultSize = 20): array
    {
        return [
            'file.required' => __('message.excel_required'),
            'file.mimes' => __('message.excel_mime'),
            'mimetypes' => __('message.excel_mime'),
            'file.max' => __('message.excel_size', ['filesize' => $defaultSize])
        ];
    }


    public static function getImageRuleMessages(?int $defaultSize = 100): array
    {
        return [
            'file.required' => __('message.image_required'),
            'file.mimes' => __('message.image_mime'),
            'file.max' => __('message.image_size', ['filesize' => $defaultSize])
        ];
    }

    public static function getCommonFileRulesMessages(?int $defaultSize = 200): array
    {
        return [
            'file.required' => __('message.image_required'),
            'file.mimes' => __('message.image_mime'),
            'file.max' => __('message.image_size', ['filesize' => $defaultSize])
        ];
    }

    public static function getZipOrPdfRuleMessages(): array
    {
        return [
            'file.required' => __('message.pdf_required')
        ];
    }
}
