<?php 

declare(strict_types=1);

namespace App\Http\Api\V1\State;

use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;
use App\Rules\AlphaSpace;

class StateValidation
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
                new AlphaSpace,
				Rule::unique('states')->ignore($id)->where(function ($query) use($request) {
				  return $query->where('country_id',$request['country_id']);
				})
            ],
			'country_id' => ['required','exists:countries,id'],
			'code' => ['required',Rule::unique('states')->ignore($id)],
			'status' => 'required|integer'
		]);
		return $validator;
	}
}