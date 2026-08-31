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
		
		    'udyam_no' => [
                'bail',
                'required', 
                'string',
                'min:19',
                'max:19',
				Rule::unique('team_msme_schemes'),
		    ],
		
			'mobile'=>[
				'bail',
				'required',
				'numeric',
				'digits:'.config('constant.MOBILE_LENGTH'), 
				Rule::unique('team_msme_schemes'),
			],
			
			/*'gstin'=>[
				'bail',
                'required',
                'integer',
        	],*/
			
			'gstin_no' => [
				'bail',
				'nullable',
				function ($attribute, $value, $fail) use ($request) {

					if (!preg_match('/^[0-9]{2}[A-Z]{5}[0-9]{4}[A-Z]{1}[1-9A-Z]{1}Z[0-9A-Z]{1}$/', $value)) {
						$fail($attribute.' format is invalid.');
					}
				},
				//'required_if:gstin,1',
				/*function ($attribute, $value, $fail) use ($request) {
					if ($request->gstin == 1) {
						if (!preg_match('/^[0-9]{2}[A-Z]{5}[0-9]{4}[A-Z]{1}[1-9A-Z]{1}Z[0-9A-Z]{1}$/', $value)) {
							$fail($attribute.' format is invalid.');
						}
					}
				},*/
				//'regex:/^[0-9]{2}[A-Z]{5}[0-9]{4}[A-Z]{1}[1-9A-Z]{1}Z[0-9A-Z]{1}$/',
			],
			
			/*'pan'=>[
				'bail',
                'required',
                'integer',
        	],*/
			
			'pan_no' => [
				'bail',
				'required',
				function ($attribute, $value, $fail) use ($request) {
					if (!preg_match('/^[A-Z]{5}[0-9]{4}[A-Z]{1}$/', $value)) {
						$fail($attribute.' format is invalid.');
					}
				},
				//'required_if:pan,1',
				/*function ($attribute, $value, $fail) use ($request) {
					if ($request->pan == 1) {
						if (!preg_match('/^[A-Z]{5}[0-9]{4}[A-Z]{1}$/', $value)) {
							$fail($attribute.' format is invalid.');
						}
					}
				},*/
				//'regex:/^[A-Z]{5}[0-9]{4}[A-Z]{1}$/'
			],
			
			/*'specially_abled'=>[
				'bail',
                'required',
                'integer',
        	],
			
			'net_investment_plant_machinery'=>[
				'bail',
                'required',
                'numeric',
				'regex:/^\d{1,15}(\.\d{1,2})?$/',
        	],*/
			
			'turnover'=>[
				'bail',
                'nullable',
                'numeric',
				'regex:/^(?!0+(\.0+)?$)\d{1,15}(\.\d{1,2})?$/',
				//'regex:/^\d{1,15}(\.\d{1,2})?$/',
        	],
			
			'current_state_business_id'=>[
				'bail',
                'required',
                'string',
        	],

			'ondc_transaction_type_id'=>[
				'bail',
                'required',
                'string',
        	],
			
			'product_category_id' => [
				'required',
				'array', 
				'min:1',
			],
			
			'product_category_id.*' => [
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
			
			/*'physical_device_business_transactions'=>[
				'bail',
                'required',
                'integer',
        	],
			
			'printer'=>[
				'bail',
                'required',
                'integer',
        	],
			
			'catalogue_prodcut_details'=>[
				'bail',
                'required',
                'integer',
        	],*/
			
			'attending_ondc_awareness_workshop'=>[
				'bail',
                'required',
                'integer',
        	],
			
			
			/*'nic_code'=>[
				'bail',
                'required',
                'integer',
				'digits_between:2,5',
        	],
			
			'dic_attached'=>[
				'bail',
                'required',
                'string',
                'max:100',
        	],

			'products_geography'=>[
				'bail',
                'required',
                'string',
                'max:200',
        	]*/
			
			/*
			'captcha'=> [
				'required',
				 self::verifyCaptcha()
			]*/
			
		];
		
    }
	
	
	public static function verifyCaptcha(){
		if (config('settings.enable_captcha')) 
		{
			 return ['required','captcha'];
		}	
	}
	
	public static function messages(): array
	{
		return [
			'terms_condition.in' => 'Please select terms and condition',
		];
	}
}