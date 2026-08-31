<?php 
declare(strict_types=1);

namespace App\Web\Masters\Attributes;

use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule; 

class AttributesRequest
{
    public static function getRules(?string $id = null): array 
    {
        return [
            'name' => [
                'bail',
                'required',
                'string',
                'min:1',
               
            ],
            'status' => [
                'required',
                'integer',
            ],
        ];
    }

    public static function messages(): array
    {
        return [
            // Name messages
            'name.required' => 'Name is required.',

            // Status messages
            'status.required' => 'Status is required.',
            'status.integer' => 'Status must be a valid number.',
        ];
    }
}