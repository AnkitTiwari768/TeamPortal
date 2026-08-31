<?php 

declare(strict_types=1);

namespace App\Http\Api\V1\Module;

use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;
use App\Rules\AlphaSpace;

class ModuleValidation
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
                Rule::unique('modules')->ignore($id),
                new AlphaSpace
            ],
			'url' => [
                'bail',
                'required',
                'string',
                'min:1',
                'max:100',
                Rule::unique('modules')->ignore($id),
            ],
			'icon' => [
                'bail',
                'required',
                'min:1',
                'max:100',
            ],
			'sort_order' => [
                'bail',
                'nullable',
                'min:1',
                'max:100',
            ],
            'description' => [
                'nullable',
                'max:300'
            ],
            'parent_id' => [
                'bail',
                'nullable',
                'string',
                'exists:modules,id'
            ],
			'status' => 'required|integer'
		]);
		return $validator;
	}
}