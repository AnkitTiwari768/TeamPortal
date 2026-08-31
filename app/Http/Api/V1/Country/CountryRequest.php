<?php 

declare(strict_types=1);

namespace App\Http\Api\V1\Country;

use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;
use App\Rules\AlphaSpace;

class CountryRequest
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
                Rule::unique('countries')->ignore($id),
                
            ],
			'country_code'=>[
				'bail',
                'required',
                'min:1',
                'max:10',
			],
			'iso2_code'=> [
				'bail',
                'required',
                'string',
                'min:1',
                'max:100',
				//'regex:/^[a-zA-Z0-9\s]*$/',
                Rule::unique('countries')->ignore($id),
			],
			'iso3_code'=> [
				'bail',
                'required',
                'string',
                'min:1',
                'max:100',
				//'regex:/^[a-zA-Z0-9\s]*$/',
                Rule::unique('countries')->ignore($id),
			],
            'status' => [
                'required',
                'integer'
            ]
		];
    }
}