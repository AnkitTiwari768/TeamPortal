<?php 

declare (strict_types = 1);

namespace App\Http\Api\V1\UserPermission\Contracts;

use App\Http\Api\V1\UserPermission\UserPermissionDTO;

interface UserPermissionRepositoryInterface 
{
    public function save(UserPermissionDTO $userPermissionDTO) : bool;
}