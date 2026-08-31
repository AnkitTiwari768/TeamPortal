<?php 
declare(strict_types=1);
namespace App\Http\Api\V1\Department;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule; 

class DepartmentRequest
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
                Rule::unique('departments')->ignore($id),
                
            ],
            'description' => [
                'nullable',
                'max:300'
            ],
			'status' => [
                'required',
                'integer'
            ]
		];
    }
}