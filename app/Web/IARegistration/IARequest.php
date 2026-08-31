<?php 

declare(strict_types=1);

namespace App\Web\IARegistration;

use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;
use App\Rules\{
    AlphaSpace,
    PhoneNumber,
    EmailValidation,
    IsPhoneSame,
    IsEmailSame
};

class IARequest
{
    public static function getRules(?string $id = null): array 
    {
        return [
			
			'name' => [
                'bail',
                'required',
                'string',
                'min:1',
                'max:80',
		    ],
			'entity_type' => [
                'bail',
                'required',
                'string',
                'min:1',
                'max:80',
		    ],
			'email_of_entity'=>[
				'bail',
                'required',
                'string',
                'min:1',
                'max:80',
				new AlphaSpace
        	],
			'required'=>[
				'bail',
                'nullable',
                'string',
                'min:1',
                'max:80',
				new AlphaSpace
        	],
			'registration_no'=>[
				'bail',
                'nullable',
                'string',
                'min:1',
                'max:80',
				new AlphaSpace
        	],
			'website'=>[
				'bail',
                'nullable',
                'string',
                'min:1',
                'max:80',
				new AlphaSpace
        	],
			'contanct_no'=>[
				'bail',
                'required',
                'string',
                'min:1',
                'max:80',
				new AlphaSpace
        	],
			'email_of_entity'=>[
				'bail',
				'required',
                'email',
                'string', 
                'max:'.config('constant.EMAIL_LENGTH'),
                 Rule::unique('users')->ignore($id),
                //new EmailValidation
				
			],
			'alternate_email'=>[
				'bail',
				'nullable',
                'email',
                'string', 
                'max:'.config('constant.EMAIL_LENGTH'),
                //new EmailValidation
				
			],
			'mobile'=>[
				'bail',
				'required',
				'numeric',
				'digits:'.config('constant.MOBILE_LENGTH'), 
				Rule::unique('users')->ignore($id),
			],
			'contanct_no'=>[
				'bail',
				'nullable',
				'numeric',
			],
			'authorized_certificate_document'=>[
				'bail',
				'required'
			],
			'district_id'=>[
				'bail',
                'required',
				'array'
        	],

			'state_id'=>[
				'bail',
                'required',
				'array'
        	],
		];
		
    }
	
	
	
	public static function messages(): array
	{
		return [
			'short_description.required_without' => 'Either short description or description document is required.',
			'description_document.required_without' => 'Either description document or short description is required.',
			'agreecheck.required'=>'The declaration field is required',
			//'last_name.regex'=>'Last name must be only characters',
			//'confirm_password.same'=>'Confirm password must be same as password',
			//'terms_condition.in' => 'Please select terms and condition',
			
		];
	}
}