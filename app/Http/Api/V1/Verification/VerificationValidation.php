<?php 
declare(strict_types=1);
namespace App\Http\Api\V1\Verification;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule; 
use App\Rules\PhoneNumber;
use App\Rules\EmailValidation;

class VerificationValidation
{
	public static function getGenerateOtpRules($request, ?string $id = null)
    {	

	$attributes_name=str_replace("_"," ",$request['type']);
	//dd($attributes_name);
    	if($request['type']=='mobile' || $request['type']=='alternate_mobile'){
    		
    		 $validator = Validator::make($request, [
				 	'keyValue' => [
		                'bail',
		                'required',
		                'numeric',
		                'digits:10',
		                new PhoneNumber($attributes_name),
						Rule::unique('users', 'mobile')
					
		            ]
        	],
			[
				'keyValue.required' => 'The '.$attributes_name.' field is required',
				'keyValue.unique' => 'The '.$attributes_name.' field is already taken',
				'keyValue.digits' => 'The '.$attributes_name.' should be number'
			]
			);
    	}  

    	 if($request['type']=='email' || $request['type']=='alternate_email'){
    		 $validator = Validator::make($request, [
				 	'keyValue' => [
		                'bail',
		                'required', 
		                'email', 
						'max:'.config('constant.EMAIL_LENGTH'),
						Rule::unique('users', 'email')
						//new EmailValidation
		            ]
        	],
			[
				'keyValue.required' => 'The '.$attributes_name.' field is required',
				'keyValue.email' => 'The '.$attributes_name.' field must be valid email address',
				'keyValue.unique' => 'The '.$attributes_name.' field is already taken',
				'keyValue.max' => 'The '.$attributes_name.' field must not be greater than 35 characters'
			]);
    	}

		return $validator;
	}

	public static function getVerifyOtpRules($request, ?string $id = null)
    {
		 $validator = Validator::make($request, [
		 	'otp' => [
                'bail',
                'required',
                'numeric',
                'digits:6'
            ],
            
		]);
		return $validator;
	}
}