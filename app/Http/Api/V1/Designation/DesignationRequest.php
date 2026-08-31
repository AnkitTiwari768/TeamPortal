<?php 
declare(strict_types=1);
namespace App\Http\Api\V1\Designation;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule; 

class DesignationRequest
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
                Rule::unique('services')->ignore($id),
                
            ],
            'department_id' => [
                'required',
                'max:300'
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