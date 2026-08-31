<?php 

declare(strict_types=1);

namespace App\Http\Api\V1\CustomUserPermission;

use Illuminate\Support\Facades\Validator;
use Illuminate\Http\Request;
use  App\Http\Api\V1\CustomUser\ValidationResult;

class CustomUserPermissionRequest 
{
    public static function rules(string $id = null): array 
    {
        return [
            'user_id' => [
                'bail', 'required', 'string', 'uuid', 'exists:users,id'
            ],
            'permissions' => [
                'required', 'array'
            ],
            'permissions.*' => [
                'required', 'uuid', 'exists:custom_permissions,id',
            ],
        ];
    }

    public static function messages(): array 
    {
        return [
           
        ];
    }

    public static function validateRequest(Request $request, ?string $userId = null): ValidationResult 
    {
        $data = $request->only([
            'user_id',
            'permissions',
        ]);

        $validator = Validator::make(
            data: $data, 
            rules: static::rules($userId), 
            messages: static::messages()
        );

        if ($validator->fails()) {
            return new ValidationResult(
                status: false, 
                errors: $validator->errors()
            );
        }

        return new ValidationResult(
            status: true, 
            validated: $validator->validated()
        );
    }
}