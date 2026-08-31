<?php 

declare(strict_types=1);

namespace App\Http\Api\V1\District;

use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;
use App\Rules\AlphaSpace;

class DistrictRequest
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
				'regex:/^[a-zA-Z0-9\s]*$/',
                Rule::unique('locations')->ignore($id),
                
            ],
			'country_id'=> [
				'bail',
                'required',
                'string',
				//'exists:countries,id'
            ],
			'state_id'=> [
				'bail',
                'required',
                'string',
				//'exists:countries,id'
            ],
			'code'=> [
				'bail',
                'required',
                'string',
                'min:1',
                'max:100',
				 Rule::unique('locations')->ignore($id),
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
				'country_id.required'=>'The country field is required',
				'state_id.required'=>'The state field is required'
				
		];
	}
}