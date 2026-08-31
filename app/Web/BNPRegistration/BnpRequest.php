<?php 

declare(strict_types=1);

namespace App\Web\BNPRegistration;

use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;
use App\Rules\{
    AlphaSpace,
    PhoneNumber,
    EmailValidation,
    IsPhoneSame,
    IsEmailSame
};

class BnpRequest
{
    public static function getRules(?string $id = null): array 
    {
        return [
		
		    'organization_id' => [
                'bail',
                'required', 
                'numeric',
		    ],
			
			'organization_name' => [
                'bail',
                'required',
                'string',
                'min:1',
                'max:80',
				//'regex:/^[a-zA-Z\s]*$/'
		    ],
			
			'authorized_person_name'=>[
				'bail',
                'required',
                'string',
                'min:1',
                'max:80',
				//'regex:/^[a-zA-Z]*$/'
				new AlphaSpace
        	],
			
			'email'=>[
				'bail',
				'required',
                'email',
                'string', 
                'max:'.config('constant.EMAIL_LENGTH'),
                 Rule::unique('users')->ignore($id),
                //new EmailValidation
				
			],
			
			'mobile'=>[
				'bail',
				'required',
				'numeric',
				'digits:'.config('constant.MOBILE_LENGTH'), 
				Rule::unique('users')->ignore($id),
			],
			
			'bank_name'=>[
				'bail',
                'required',
                'string',
                'min:3',
                'max:80'
        	],
			
			'ifsc_code'=>[
				'bail',
                'required',
                'string',
        	],
			
			'account_no'=>[
				'bail',
                'required',
                'numeric',
        	],
			
			'pan' => [
				'bail',
				'required',
				'string',
				'size:10',
				'regex:/^[A-Z]{5}[0-9]{4}[A-Z]{1}$/',
			],
			
			'gst_number' => [
				'bail',
				'required',
				'string',
				'size:15',
				'regex:/^[0-9]{2}[A-Z]{5}[0-9]{4}[A-Z]{1}[A-Z0-9]{1}Z[A-Z0-9]{1}$/',
			],

			'agreecheck'=>[
				'bail',
                'required',
                'integer',
                'max:10',
				'regex:/^[0-9]*$/'
        	],
			
		];
		
    }
	
	
	
	public static function messages(): array
	{
		return [
			'agreecheck.required'=>'The declaration field is required',		
		];
	}
}