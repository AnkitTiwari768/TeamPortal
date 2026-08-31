<?php

declare(strict_types=1);

namespace App\Web\VerifyOtp;

use Illuminate\Foundation\Http\FormRequest;

class GenericVerifyOtpRequest extends FormRequest
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
        ];
    }

    public function toDto(): VerifyOtpDto
    {
        return VerifyOtpDto::fromArray($this->validated());
    }
}
