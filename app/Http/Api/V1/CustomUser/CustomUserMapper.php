<?php 

declare(strict_types=1);

namespace App\Http\Api\V1\CustomUser;

use App\Contracts\GrantType;

class CustomUserMapper 
{
    public static function mapToUser(CustomUserDto $userDto, ?string $userId = null): array 
    {
        $userData = [
            'first_name' => $userDto->firstName,
            'last_name' => $userDto->lastName,
            'email' => $userDto->email,
            'mobile' => $userDto->mobile,
            'zone_id' => $userDto->zoneId,
            'asi_circle_id' => $userDto->asiCircleId,
            'status' => true
        ];

        if (! $userId) 
        {
            $userData += [
                'id' => uuid(),
                'is_custom' => true,
                'created_by' => AuthId(),
                'created_at' => currentDateTime()
            ];
        }
        else 
        {
            $userData += [
                'updated_at' => currentDateTime(),
                'updated_by' => AuthId()
            ];
        }

        return $userData;
    }

    public static function mapToAddress(CustomUserDto $userDto, ?string $userId = null): array 
    {
        if (! $userDto->stateId) 
        {
            return [];
        }

        $addressData = [
            'address' => '',
            'state_id' => $userDto->stateId
        ];

        if (! $userId) {
            $addressData += [
                'id' => uuid(),
                'created_at' => currentDateTime(),
            ];
        }
        else 
        {
            $addressData += [
                'updated_at' => currentDateTime(),
            ];
        }

        return $addressData;
    }

    public static function mapToUserRoles(CustomUserDto $userDto, string $userId): array
    {
        $userRolesData = [];
        
        if ($userRoles = $userDto->role)
        {
            $userRolesData = array_map(fn ($roleId) => ([
                'user_id' => $userId,
                'role_id' => $roleId,
                'type' => GrantType::THROUGH_ROLE
            ]), $userRoles);
        }
        
        return $userRolesData;
    }
}