<?php 

declare(strict_types=1);

namespace App\Http\Api\V1\User;

use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\Validator;

use App\Rules\{
    AlphaSpace,
    PhoneNumber,
    EmailValidation,
    IsPhoneSame,
    IsEmailSame
};

class UserValidation 
{
    public static function getRules(?string $id = null) : array 
    {
        $rules = [
            'first_name' =>  [
                'bail',
                'required',
                'string',
                'min:1',
                'max:'.config('constant.MAXLENGTH2'),
                new AlphaSpace
            ],
			'middle_name' =>  [
                'bail',
				'nullable',
                'string',
                'min:1',
                'max:'.config('constant.MAXLENGTH2'),
                new AlphaSpace
            ],
			'last_name' =>  [
                'bail',
                'required',
                'string',
                'min:1',
                'max:'.config('constant.MAXLENGTH2'),
                new AlphaSpace
            ],
            /*'username' => [
                'bail',
                'required',
                'alpha_num', 
                'max:'.config('constant.MAXLENGTH2'),
                Rule::unique('users')->ignore($id)
            ],*/
            'email' => [
                'bail',
                'email',
                'required',
                'string', 
                'max:'.config('constant.EMAIL_LENGTH'),
                Rule::unique('users')->ignore($id),
                //new EmailValidation
            ],
			/*'alternate_email' => [
                'bail',
                'nullable',
                'email',
                'string', 
                'max:'.config('constant.EMAIL_LENGTH'),
                Rule::unique('users')->ignore($id),
                //new EmailValidation,
				new IsEmailSame(request()->email)
            ],*/
			'mobile' => [
                'bail',
                'required',
                'numeric',
                'digits:'.config('constant.MOBILE_LENGTH'), 
                Rule::unique('users')->ignore($id),
                new PhoneNumber
            ],
			/*'alternate_mobile' => [
                'bail',
                'nullable',
                'numeric',
                'digits:'.config('constant.MOBILE_LENGTH'), 
                Rule::unique('users')->ignore($id),
                new PhoneNumber,
				//new IsPhoneSame(request()->mobile)
            ],*/
            'roles' => [
                'bail',
                'required',
                'array',
            ],
            'roles.*' => [
                'bail',
                'required',
                'string',
                'exists:roles,id',
            ],
			'department_id' => [
                'bail',
                'required',
                'string',
                'exists:departments,id',
            ],
            'zone_id' => [
                'bail',
                'nullable',
                'required_if:selected_role,Railway Zonal',
                'exists:railway_zones,id',
                'unique:users,zone_id,'.$id
            ],
			'designation_id' => [
                'bail',
                'required',
                'string',
                'exists:designations,id',
            ],
			'address' => [
                'bail',
                'string',
                'required',
                'max:'.config('constant.MAXLENGTH4')
            ],
			'postal_code' => [
                'bail',
                'numeric',
                'required',
                'digits:'.config('constant.PINCODE_LENGTH')
            ],
			'country_id' => [
                'bail',
                'required',
                'string',
                'exists:countries,id',
            ],
			'state_id' => [
                'bail',
                'required',
                'string',
                'exists:states,id',
            ],
			'district_id' => [
                'bail',
                'required',
                'string',
                'exists:locations,id',
            ],
			'status' => [
                'bail',
                'required', 
                'integer'
            ],
            'landline_number' => [
                'bail',
                'nullable',
                'numeric',
                'digits:'.config('constant.MOBILE_LENGTH'),
                new PhoneNumber
            ],
            'is_role_mapped' => [
                'bail',
                'nullable',
                'integer',
            ],
        ];
        
       /* if (! $id) 
        {
            $rules['password'] = [
                'bail',
                'required',
                'min:8',
                'regex:/[a-z]/',
                'regex:/[A-Z]/',
                'regex:/[0-9]/',
                'regex:/[@$!%*#?&]/',
            ];

            $rules['password_confirmation'] = 'required';
        }*/

        return $rules;
    }

    public static function validate(array $payload, ?string $id = null) 
    {
        return Validator::make($payload, self::getRules($id));
    }
}