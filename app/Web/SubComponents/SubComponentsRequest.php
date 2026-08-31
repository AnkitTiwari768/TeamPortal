<?php 

declare(strict_types=1);

namespace App\Web\SubComponents;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;
use App\Rules\AlphaSpace;

class SubComponentsRequest
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
                Rule::unique('sub-components')->ignore($id),
                
            ],
			'major_component_id'=> [
				'bail',
                'required',
                'string',
				'exists:major-components,id'
            ],
			'component_id'=> [
				'bail',
                'required',
                'string',
				'exists:components,id'
            ],
            'status' => [
                'required',
                'integer'
            ]
		];
    }
	
	public static function messages():array
	{
		return [
				'major_component_id.required'=>'The Major Component field is required', 
				'component_id.required'=>'The Component field is required'
				
		];
	}
}