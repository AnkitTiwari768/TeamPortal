<?php 

declare(strict_types=1);

namespace App\Http\Api\V1\SubDistrict;

use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;
use App\Rules\AlphaSpace;

class SubDistrictRequest
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
                Rule::unique('sub_districts')->ignore($id),
                
            ],
			'district_id'=> [
				'bail',
                'null',
                'string',
				//'exists:countries,id'
            ],
			'district_code'=> [
				'bail',
                'null',
                'string',
				//'exists:countries,id'
            ],
			'code'=> [
				'bail',
                'required',
                'string',
                'min:1',
                'max:100',
				 Rule::unique('sub_districts')->ignore($id),
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
				'district_id.required'=>'The country field is required',
				'name.required'=>'The name field is required'
				
		];
	}
}