<?php

declare(strict_types=1);

namespace App\Domain\User;

use App\Rules\AlphaSpace;
use App\Rules\PhoneNumber;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class CreateUserRequest extends FormRequest
{
    public function rules(): array
    {
        $userId = $this->route('id') ?? $this->route('user');

        $rules = [
            'first_name' => [
                'bail',
                'required',
                'string',
                'min:1',
                'max:' . config('constant.MAXLENGTH2'),
                new AlphaSpace
            ],

            'middle_name' => [
                'bail',
                'nullable',
                'string',
                'min:1',
                'max:' . config('constant.MAXLENGTH2'),
                new AlphaSpace
            ],

            'last_name' => [
                'bail',
                'required',
                'string',
                'min:1',
                'max:' . config('constant.MAXLENGTH2'),
                new AlphaSpace
            ],

            'email' => [
                'bail',
                'required',
                'email',
                'string',
                'max:' . config('constant.EMAIL_LENGTH'),
                Rule::unique('users', 'email')->ignore($userId),
            ],

            'mobile' => [
                'bail',
                'required',
                'numeric',
                'digits:' . config('constant.MOBILE_LENGTH'),
                Rule::unique('users', 'mobile')->ignore($userId),
                new PhoneNumber()
            ],
        ];

        if (!$userId) {
            // Create
            $rules['roles'] = [
                'bail',
                Rule::requiredIf(fn () => hasRole('administrator')),
                'string',
                'exists:roles,id',
            ];

            $rules['status'] = [
                'bail',
                'required',
                'integer',
            ];
        } else {
            // Update
            $rules['roles'] = [
                'bail',
                'sometimes',
                'nullable',
                'string',
                'exists:roles,id',
            ];

            $rules['status'] = [
                'bail',
                'sometimes',
                'nullable',
                'integer',
            ];
        }

        return $rules;
    }
}
