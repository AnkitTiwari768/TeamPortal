<?php

declare(strict_types=1);

namespace App\Domain\User;

use Illuminate\Foundation\Http\FormRequest;

class ChangePasswordRequest extends FormRequest
{
    public function rules(): array
    {
        return [
            'user_id' => [
                'required',
                'string',
                'exists:users,id',
            ],

            'password' => [
                'required',
                'string',
                'confirmed',
            ],

            'password_confirmation' => [
                'required',
                'string',
            ],
        ];
    }

    public function messages(): array
    {
        return [
            'user_id.required'               => 'User ID is required.',
            'user_id.exists'                 => 'The selected user does not exist.',

            'password.required'              => 'Password is required.',
            'password.min'                   => 'Password must be at least 8 characters.',
            'password.max'                   => 'Password must not exceed :max characters.',
            'password.confirmed'             => 'Password confirmation does not match.',

            'password_confirmation.required' => 'Please confirm your password.',
        ];
    }
}