<?php 

declare(strict_types=1);

namespace App\Http\Api\V1\District;

use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;
use App\Rules\AlphaSpace;

class DistrictValidation
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
                Rule::unique('locations')->ignore($id),
                new AlphaSpace
            ],
			'country_id' => ['required','exists:countries,id'],
			'state_id' => ['required','exists:states,id'],
			'code' => ['required',Rule::unique('locations')->ignore($id)],
			'status' => 'required|integer'
		]);
		return $validator;
	}
}