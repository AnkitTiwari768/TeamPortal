<?php 

declare(strict_types=1);

namespace App\Http\Api\V1\User\Contracts;

use App\Http\Api\V1\User\UserDTO;

interface UserRepositoryInterface
{
    public function save(UserDTO $userDTO, ?string $id = null) : bool;
}