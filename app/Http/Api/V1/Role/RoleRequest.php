<?php 

declare(strict_types=1);

namespace App\Http\Api\V1\Role;

use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;
use App\Rules\AlphaSpace;

class RoleRequest
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
                Rule::unique('roles')->ignore($id),
                
            ],
            'description' => [
                'nullable',
                'max:300'
            ],
            'department' => [
                'bail',
                'nullable',
                'exists:departments,id'
            ],
            // Optional: roles created before Role Types exist must still be saveable.
            'role_type' => [
                'bail',
                'nullable',
                'string',
                'exists:role_types,id'
            ],
            'permissions' => [
                'nullable',
                'array'
            ],
            'permissions.*' => [
                'bail',
                'required',
                'string',
                'distinct',
                'exists:permissions,id'
            ],
			'status' => [
                'required',
                'integer'
            ]
		];
    }
}