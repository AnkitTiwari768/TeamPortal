<?php

declare(strict_types=1);

namespace App\Web\VerifyOtp;

use Illuminate\Foundation\Http\FormRequest;

class VerifyOtpRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'username' => 'required',
            'otp' => 'required',
            'captcha' => 'required|captcha',
        ];
    }

    public function toDto(): VerifyOtpDto
    {
        return VerifyOtpDto::fromArray($this->validated());
    }
}
