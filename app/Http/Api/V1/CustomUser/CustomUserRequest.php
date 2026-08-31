<?php 

declare(strict_types=1);

namespace App\Http\Api\V1\CustomUser;

use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;
use Illuminate\Http\Request;
use App\Rules\AlphaSpace;
use App\Rules\PhoneNumber;

class CustomUserRequest 
{
    private static function unique(string $id = null)
    {
        return Rule::unique('users')->ignore($id);
    }

    public static function rules(string $id = null): array 
    {
        return [
            'first_name' => [
                'bail', 'required', 'string', 'min:3', 'max:64', new AlphaSpace
            ],
            'last_name' => [
                'bail', 'nullable', 'string', 'min:3', 'max:64', new AlphaSpace
            ],
            'email' => [
                'bail', 'required', 'email', 'max:255', static::unique($id)
            ],
            'mobile' => [
                'bail', 'required', 'min:10', 'max:20', new PhoneNumber, static::unique($id)
            ],
            'role' => [
                'nullable', 'array'
            ],
            'role.*' => [
                'required', 'uuid', 'exists:roles,id',
            ],
            'state_id' => [
                static::isRoleContainsNodalOfficer() ? 'required' : 'nullable',
                'exists:states,id',
            ],
            'zone_id' => [
                'nullable'
            ],
            'asi_circle_id' => [
                'nullable'
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
            'first_name',
            'last_name',
            'email',
            'mobile',
            'role',
            'state_id',
            'zone_id',
            'asi_circle_id'
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

    private static function isRoleContainsNodalOfficer(): bool 
    {
        $roles = request()?->role ?? [];
        return app(CustomUserService::class)->checkRoleIdExistsBySlug('nodal-officer', $roles); 
    }
}