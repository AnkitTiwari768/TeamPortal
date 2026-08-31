<?php 
declare(strict_types=1);
namespace App\Web\Category;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule; 

class CategoryRequest
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
				'regex:/^[a-zA-Z0-9\s]*$/'
                
            ],
            'ondc_type_id' => [
                'bail',
                'required',
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