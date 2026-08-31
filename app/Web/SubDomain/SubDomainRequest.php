<?php 
declare(strict_types=1);
namespace App\Web\SubDomain;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule; 

class SubDomainRequest
{
    public static function getRules(?string $id = null): array 
    {
        return [
			'name' => [
                'bail',
                'required',
                'string',
                'min:1',
                'max:100'
                
            ],
            'domain_id' => [
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