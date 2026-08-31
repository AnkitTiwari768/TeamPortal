<?php 
declare(strict_types=1);

namespace App\Web\Masters\ClaimTypes;

use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule; 

class ClaimTypesRequest
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
            'short_name' => [
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

            'name.string' => 'Name must be a valid string.',
            'name.min' => 'Name must be at least 1 character long.',
            // Status messages
            'status.required' => 'Status is required.',
            'status.integer' => 'Status must be a valid number.',
        ];
    }
}