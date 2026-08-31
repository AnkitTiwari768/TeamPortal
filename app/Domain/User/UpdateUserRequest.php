<?php

declare(strict_types=1);

namespace App\Domain\User;

use Illuminate\Validation\Rule;

class UpdateUserRequest extends CreateUserRequest
{
    public function rules(): array
    {
        $rules = parent::rules();

        $rules['email'] = ['required', 'email', 'max:255', Rule::unique('users')->ignore($this->route('id'))];
        $rules['mobile'] = ['required',  'numeric', 'digits:' . config('constant.MOBILE_LENGTH'), Rule::unique('users')->ignore($this->route('id'))];

        return $rules;
    }
}
