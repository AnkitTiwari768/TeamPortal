<?php 

declare(strict_types=1);

namespace App\Http\Api\V1\PMRegistration;

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
use Illuminate\Support\Facades\DB;

class PMRegistrationRequest
{
    public static function getRules($request,?string $id = null): array 
    {
        // $otherId = DB::table('attribute_values as av')
        // ->join('attributes as a', 'a.id', '=', 'av.attribute_id')
        // ->where('a.code', 'type_of_business')
        // ->value('av.id');
        
        return [
		
			'owner_name'=>[
				'bail',
                'required',
                'string',
                'min:1',
                'max:' . config('constant.MAXLENGTH2'),
                new AlphaSpace
			],
			'store_name'=>[
				'bail',
                'nullable',
                'string',
                'min:1',
                'max:' . config('constant.MAXLENGTH2'),
                new AlphaSpace
			],
			'mobile'=>[
				'bail',
				'required',
				'numeric',
				'digits:'.config('constant.MOBILE_LENGTH'), 
				//Rule::unique('team_msme_schemes'),
			],
			'email' => [
                'bail',
                'required',
                'email:rfc,dns',
                'string',
                'max:' . config('constant.EMAIL_LENGTH'),
                Rule::unique('users')->ignore($id),
            ],

			'pan_no' => [
				'bail',
				'nullable',
				'regex:/^[A-Z]{5}[0-9]{4}[A-Z]{1}$/'
			],
			'pin_code' => [
				'bail',
				'numeric',
                'required',
                'digits:'.config('constant.PINCODE_LENGTH')
			],
			'address' => [
				'bail',
				'required',
			],
		
			'type_of_business' => [
                'bail',
                'required',
                'string',
               
            ],

			'cancelled-cheque'      => 'nullable|string|exists:file_uploads,file_system_name',
            'cancelled-cheque-id'   => 'nullable|exists:file_uploads,id',

			'vishwakarma-form-copy'      => 'required|string|exists:file_uploads,file_system_name',
            'vishwakarma-form-copy-id'   => 'nullable|exists:file_uploads,id',
			
		];
		
    }
	

	
	public static function messages(): array
	{
		return [
			   'owner_name.required' => 'Owner name is required.',
        'owner_name.alpha_space' => 'Owner name may only contain letters and spaces.',
        'store_name.required' => 'Store name is required.',
        'store_name.alpha_space' => 'Store name may only contain letters and spaces.',

        // Mobile
        'mobile.required' => 'Mobile number is required.',
        'mobile.numeric' => 'Mobile number must be numeric.',
        'mobile.digits'  => 'Mobile number must be :digits digits.',

        // Email
        'email.required' => 'Email address is required.',
        'email.email'    => 'Please enter a valid email address.',
        'email.unique'   => 'This email address is already registered.',

        // PAN
        'pan_no.required' => 'PAN number is required.',
        'pan_no.regex'    => 'Please enter a valid PAN number.',

        // Pin Code
        'pin_code.required' => 'Pin code is required.',
        'pin_code.numeric'  => 'Pin code must be numeric.',
        'pin_code.digits'   => 'Pin code must be :digits digits.',

        // Address
        'address.required' => 'Address is required.',

        // Other / Remark
        'other.required' => 'Please specify other business details.',

        // Business Type
        'type_of_business.required' => 'Please select type of business.',
        'type_of_business.exists'   => 'Selected business type is invalid.',

        // Documents – Cancelled Cheque
        'hidden_cancelled_cheque.required' => 'Cancelled cheque document is required.',
        'hidden_cancelled_cheque.exists'   => 'Invalid cancelled cheque document.',

        'hidden_cancelled_cheque_id.exists' => 'Invalid cancelled cheque file reference.',

        // Documents – Vishwakarma Form
        'hidden_vishwakarma_form_copy.required' => 'Vishwakarma form copy is required.',
        'hidden_vishwakarma_form_copy.exists'   => 'Invalid Vishwakarma form document.',

        'hidden_vishwakarma_form_copy_id.exists' => 'Invalid Vishwakarma form file reference.',
		];
	}

   
}