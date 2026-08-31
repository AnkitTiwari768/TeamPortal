<?php

namespace App\Web\Claim;

class ClaimBulkImportRequest
{
    public static function rules()
    {
        return [
            'claim_type_id' => 'required|string|exists:claim_types,id',
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

    public static function messages()
    {
        return [
            'file.required' => 'Please upload a file.',
            'file.file' => 'The uploaded file must be valid.',
            'file.mimes' => 'The file must be an Excel or CSV file.',
        ];
    }
}
