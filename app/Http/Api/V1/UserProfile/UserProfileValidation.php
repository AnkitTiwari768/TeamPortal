<?php

declare(strict_types=1);

namespace App\Http\Api\V1\UserProfile;

use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\Validator;

use App\Rules\{
    AlphaSpace,
    PhoneNumber,
    EmailValidation,
    IsPhoneSame,
    IsEmailSame
};

class UserProfileValidation
{
    public static function getRules(?string $id = null): array
    {
        $rules = [
            'first_name' =>  [
                'bail',
                'required',
                'string',
                'min:1',
                'max:' . config('constant.MAXLENGTH2'),
                new AlphaSpace
            ],
            'middle_name' =>  [
                'bail',
                'nullable',
                'string',
                'min:1',
                'max:' . config('constant.MAXLENGTH2'),
                new AlphaSpace
            ],
            'last_name' =>  [
                'bail',
                'nullable',
                'string',
                'min:1',
                'max:' . config('constant.MAXLENGTH2'),
                new AlphaSpace
            ],

            'state' => [
                'bail',
                //'required',
                'string'
            ],



            'email' => [
                'bail',
                'email',
                'required',
                'string',
                'max:' . config('constant.EMAIL_LENGTH'),
                Rule::unique('users')->ignore($id)
            ],
            'mobile' => [
                'bail',
                'required',
                'numeric',
                'digits:' . config('constant.MOBILE_LENGTH'),
                Rule::unique('users')->ignore($id),
                new PhoneNumber
            ],
            'phone_number' => [
                'bail',
                'nullable',
                'numeric',
                'digits:' . config('constant.MOBILE_LENGTH'),
                Rule::unique('users')->ignore($id),
                new PhoneNumber
            ],
            'landline_number' => [
                'bail',
                'nullable',
                'numeric',
                'digits:' . config('constant.MOBILE_LENGTH'),
                new PhoneNumber
            ],
        ];

        return $rules;
    }

    public static function getSnprules(?string $id = null): array
    {
        $rules = [

            'email' => [
                'bail',
                'required',
                'email',
                'string',
                'max:' . config('constant.EMAIL_LENGTH'),
                Rule::unique('users')->ignore($id),
                //new EmailValidation

            ],
            'alternate_email' => [
                'bail',
                'required',
                'email',
                'string',
                'max:' . config('constant.EMAIL_LENGTH'),
                //new EmailValidation

            ],
            'mobile' => [
                'bail',
                'required',
                'numeric',
                'digits:' . config('constant.MOBILE_LENGTH'),
                Rule::unique('users')->ignore($id),
            ],
            'contanct_no' => [
                'bail',
                'nullable',
                'numeric',
                //'digits:'.config('constant.MOBILE_LENGTH')
            ],
            'domain' => [
                'bail',
                'required',
                'array'
            ],
            'sub_domain' => [
                'bail',
                'required',
                'array'
            ],
            'transaction_type' => [
                'bail',
                'required',
                'array'
            ],
            'state_id' => [
                'bail',
                'required',
                'array'
            ],
            'bank_name' => [
                'bail',
                'required',
                'string',
                'min:3',
                'max:80'
            ],

            'ifsc_code' => [
                'bail',
                'required',
                'string',
            ],

            'account_no' => [
                'bail',
                'required',
                'integer',
            ],

            'pan' => [
                'bail',
                'required',
                'string',
                'min:10',
                'max:10',
            ],

            'gst_number' => [
                'bail',
                'required',
                'string',
                'min:5',
                'max:20'
            ],
            /*'cancelled_cheque_document'=>[
				'bail',
                'required'
        	],*/
            'live_seller' => [
                'bail',
                'required',
                'numeric'
            ],
            'no_of_transactions_done' => [
                'bail',
                'required',
                'numeric'
            ],
        ];

        return $rules;
    }

    public static function getBnprules(?string $id = null): array
    {
        $rules = [

            'email' => [
                'bail',
                'required',
                'email',
                'string',
                'max:' . config('constant.EMAIL_LENGTH'),
                Rule::unique('users')->ignore($id),
                //new EmailValidation

            ],

            'mobile' => [
                'bail',
                'required',
                'numeric',
                'digits:' . config('constant.MOBILE_LENGTH'),
                Rule::unique('users')->ignore($id),
            ],


            'bank_name' => [
                'bail',
                'required',
                'string',
                'min:3',
                'max:80'
            ],

            'ifsc_code' => [
                'bail',
                'required',
                'string',
            ],

            'account_no' => [
                'bail',
                'required',
                'integer',
            ],

            'pan' => [
                'bail',
                'required',
                'string',
                'min:10',
                'max:10',
            ],

            'gst_number' => [
                'bail',
                'required',
                'string',
                'min:5',
                'max:20'
            ],
        ];

        return $rules;
    }

    public static function validate(array $payload, ?string $id = null)
    {
        return Validator::make($payload, self::getRules($id));
    }
}
