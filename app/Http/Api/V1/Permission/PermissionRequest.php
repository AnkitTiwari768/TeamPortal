<?php 

declare (strict_types = 1);

namespace App\Http\Api\V1\Permission;

use Illuminate\Validation\Rule;
use App\Rules\AlphaSpace;

class PermissionRequest 
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
                Rule::unique('permissions')->ignore($id),
                //new AlphaSpace
            ], 
			'module_id' => [
				'required',
				'string'
			],
			'roles' => 'required',
			'roles.*' => 'string',
			'description' => 'nullable'
		];
	}
}