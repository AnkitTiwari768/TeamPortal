<?php

namespace App\Web\Claim;

class CatalogueBulkZipRequest
{
    public static function rules()
    {
        return [
            'file' => [
                'required',
                'mimes:zip',
                'max:20480',
                function (string $attribute, mixed $value, \Closure $fail) {
                    $originalName = request()->file('file')->getClientOriginalName();
                    $files = explode('.', $originalName);
                    if (count($files) > 2)
                        $fail('Double extension not allowed');
                    [$name, $extension] = explode('.', $originalName);

                    if (strtolower($extension) !== 'zip') {
                        $fail('The file format must be a zip');
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
            'file.mimes' => 'The file must be an zip file.',
        ];
    }
}
