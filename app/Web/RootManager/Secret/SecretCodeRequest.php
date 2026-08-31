<?php
// app/Web/RootManager/Secret/SecretCodeRequest.php

namespace App\Web\RootManager\Secret;

use Illuminate\Foundation\Http\FormRequest;

class SecretCodeRequest extends FormRequest
{
    public function authorize()
    {
        return true;
    }

    public function rules()
    {
        return [
            'code_value' => 'required|string|min:3|max:50'
        ];
    }

    public function messages()
    {
        return [
            'code_value.required' => 'Secret code is required.',
            'code_value.min' => 'Secret code must be at least 3 characters.'
        ];
    }
}