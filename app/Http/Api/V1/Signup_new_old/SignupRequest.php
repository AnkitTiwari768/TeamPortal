<?php 

declare(strict_types=1);

namespace App\Http\Api\V1\Signup;

use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;
use App\Rules\{
    AlphaSpace,
    PhoneNumber,
    EmailValidation,
    IsPhoneSame,
    IsEmailSame
};
use App\Enums\ProductionType;
use App\Enums\RepresentativeType;

class SignupRequest
{
    public static function getRules($request,?string $id = null): array 
    { 
        return [

			'entrepreneur_name' => [
				'bail',
                'required', 
                'string',
                'min:3',
                'max:100',
			],

			'enterprise_name' => [
				'bail',
                'required', 
                'string',
                'min:3',
                'max:100',
			],
		
		    'udyam_no' => [
                'bail',
                'nullable', 
                'string',
                'min:19',
                'max:19',
				//Rule::unique('team_msme_schemes'),
		    ],
		
			'mobile'=>[
				'bail',
				'required',
				'numeric',
				'digits:'.config('constant.MOBILE_LENGTH'), 
				Rule::unique('team_msme_schemes'),
			],

			'email'=>[
				'bail',
				'required',
				'string',
				'min:3',
                'max:100',
				Rule::unique('team_msme_schemes'),
			],

			'product_category_id' => [
				'required',
				'array', 
				'min:1',
			],
			
			'product_category_id.*' => [
				'string',
			],
			
			'product_details'=>[
				'bail',
				'required',
				'string',
				'min:3',
                'max:200'
			],
			
			'state_id'=>[
				'bail',
                'required',
                'string',
        	],

			'ondc_transaction_type_id'=>[
				'bail',
                'required',
                'string',
        	],
			
			
			'select_snp'=>[
				'bail',
                'required',
                'integer',
        	],
			
			'snp_id' => [
				'bail',
				'required_if:select_snp,1',
			],
			
			
			
		];
		
    }
	
	public static function messages(): array
	{
		return [
			'terms_condition.in' => 'Please select terms and condition',
		];
	}

	public static function getSecondStepRules($request,?string $id = null): array 
    { 
        return [
		
		    'udyam_no' => [
                'bail',
                'required', 
                'string',
                'min:19',
                'max:19',
				//Rule::unique('team_msme_schemes'),
		    ],
		
			'mobile'=>[
				'bail',
				'required',
				'numeric',
				'digits:'.config('constant.MOBILE_LENGTH'), 
				//Rule::unique('team_msme_schemes'),
			],

			'gstin_no' => [
				'bail',
				'required',
				function ($attribute, $value, $fail) use ($request) {

					if (!preg_match('/^[0-9]{2}[A-Z]{5}[0-9]{4}[A-Z]{1}[1-9A-Z]{1}Z[0-9A-Z]{1}$/', $value)) {
						$fail($attribute.' format is invalid.');
					}
				},
				//'regex:/^[0-9]{2}[A-Z]{5}[0-9]{4}[A-Z]{1}[1-9A-Z]{1}Z[0-9A-Z]{1}$/',
			],

			'pan_no' => [
				'bail',
				'required',
				function ($attribute, $value, $fail) use ($request) {
					if (!preg_match('/^[A-Z]{5}[0-9]{4}[A-Z]{1}$/', $value)) {
						$fail($attribute.' format is invalid.');
					}
				},
				//'regex:/^[A-Z]{5}[0-9]{4}[A-Z]{1}$/'
			],
			
		];
		
    }
}