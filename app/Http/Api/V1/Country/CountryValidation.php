<?php 

declare(strict_types=1);

namespace App\Http\Api\V1\Country;

use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;
use App\Rules\AlphaSpace;

class CountryValidation
{
	public static function getRules($request, ?string $id = null)
    {  
		 $validator = Validator::make($request, [
			'name' => [
                'bail',
                'required',
                'string',
                'min:1',
                'max:100',
                Rule::unique('countries')->ignore($id),
                new AlphaSpace
            ],
			'country_code' => [
                'bail',
                'required',
                //'min:1',
                //'max:100',
				Rule::unique('countries')->ignore($id),
                
            ],
			'iso2_code' => ['required',Rule::unique('countries')->ignore($id)],
			'iso3_code' => ['required',Rule::unique('countries')->ignore($id)],
			'status' => 'required|integer'
		]);
		return $validator;
	}
}