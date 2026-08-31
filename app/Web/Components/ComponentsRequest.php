<?php 

declare(strict_types=1);

namespace App\Web\Components;

use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;
use App\Rules\AlphaSpace;

class ComponentsRequest
{
    public static function getRules(?string $id = null): array 
    {
        return [
			
			'name' => [
                'bail',
                'required',
                'string',
                'min:1',
                'max:100',
				// 'regex:/^[a-zA-Z0-9\s]*$/',
                Rule::unique('components')->ignore($id),
                
            ],
			'major_component_id'=> [
				'bail',
                'required',
                'string',
				'exists:major-components,id'
            ],
            'status' => [
                'required',
                'integer'
            ]
		];
    }
	
	public static function messages(): array
	{
		return [
			'major_component_id.required'=>'The Major Component field is required'
		];
	}
}