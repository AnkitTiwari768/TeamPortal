<?php 

declare (strict_types = 1);

namespace App\Http\Api\V1\UserPermission;

use Illuminate\Support\Facades\Validator;

class UserPermissionValidation 
{
    public static function getRules(?string $id = null): array 
    {
        return [
            'user_id' => 'required|string|string|exists:users,id',
            'permissions' => 'nullable|array',
            'permissions.*' => 'required|string|exists:permissions,id',
        ];
    }  

    public static function validate(array $payload, ?string $id = null) 
    {
        return Validator::make($payload, self::getRules($id));
    }
}