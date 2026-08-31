<?php 

declare(strict_types=1);

namespace App\Web\CA;

use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rules\Password as PasswordRule;
use App\Rules\{
    AlphaSpace,
    PhoneNumber,
    EmailValidation,
    IsPhoneSame,
    IsEmailSame
};

class CAValidation 
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
          
            'email' => [
                'bail',
                'email',
                'required',
                'string', 
                'max:'.config('constant.EMAIL_LENGTH'),
                Rule::unique('users')->ignore($id),
                //new EmailValidation
            ],
			
			'mobile' => [
                'bail',
                'required',
                'numeric',
                'digits:'.config('constant.MOBILE_LENGTH'), 
                Rule::unique('users')->ignore($id),
                new PhoneNumber
            ],

        ];
        
        if (! $id) 
        {
            $rules['password'] = [
                'bail',
                'required',
                PasswordRule::min(8)->mixedCase()->numbers()->symbols(),
            ];

            $rules['password_confirmation'] = 'required';
        }

        return $rules;
    }

    public static function validate(array $payload, ?string $id = null) 
    {
        return Validator::make($payload, self::getRules($id));
    }
}