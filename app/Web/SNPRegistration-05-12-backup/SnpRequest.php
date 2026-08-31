<?php 

declare(strict_types=1);

namespace App\Web\SNPRegistration;

use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;
use App\Rules\{
    AlphaSpace,
    PhoneNumber,
    EmailValidation,
    IsPhoneSame,
    IsEmailSame
};

class SnpRequest
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
			'brand_name' => [
                'bail',
                'nullable',
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
			'designation'=>[
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
				//'digits:'.config('constant.MOBILE_LENGTH')
			],
			'authorized_certificate_document'=>[
				'bail',
				'required'
			],
			'domain'=>[
				'bail',
                'required',
				'array'
        	],
			'sub_domain'=>[
				'bail',
                'required',
				'array'
        	],
			'transaction_type'=>[
				'bail',
                'required',
				'array'
        	],
			'state_id'=>[
				'bail',
                'required',
				'array'
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
			
			'cancelled_cheque_document'=>[
				'bail',
                'required'
        	],

			'commercial'=>[
				'bail',
                'nullable'
        	],
			
			'commercial_model_document'=>[
				'bail',
                'required'
        	],
			
			'live_seller'=>[
				'bail',
                'required',
                'numeric'
        	],
			
			'date_of_going_live_on_ondc'=>[
				'bail',
                'required',
				'date_format:Y-m-d',
                'string',
				'min:10',
                'max:10'
        	],

			'no_of_transactions_done'=>[
				'bail',
                'required',
                'numeric'
        	],
			
			'agreecheck'=>[
				'bail',
                'required',
                'integer'
        	],
			'short_description' => [
				'nullable',
				'string',
				'max:1000',
				'required_without:description_document'
			],
			
			'description_document' => [
				'nullable',
				'string', 
				'required_without:short_description'
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