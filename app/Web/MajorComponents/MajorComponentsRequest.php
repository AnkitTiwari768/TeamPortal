<?php 

declare(strict_types=1);

namespace App\Web\MajorComponents;

use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;
use App\Rules\AlphaSpace;

class MajorComponentsRequest
{
    public static function getRules(?string $id = null): array 
    {
        return [
			
			'name' => [
                'bail',
                'required',
                'string',
                'min:1',
                'max:255',
				// 'regex:/^[a-zA-Z0-9\s]*$/',
                Rule::unique('major-components')->ignore($id),
                
            ],
			
            'status' => [
                'required',
                'integer'
            ]
		];
    }
}