<?php 

declare(strict_types=1);

namespace App\Http\Api\V1\CustomUser;

use App\Traits\DataTable;
use App\Traits\Mailer;

class CustomUserService
{
    use Mailer;
    use DataTable;

    public function __construct(private CustomUserRepository $userRepository)
    {
        
    }

    public function createUser(CustomUserDto $userDto)
    {
        $result = $this->userRepository->storeUser($userDto);
        
        if ($result->status()) 
        {
            $this->sendEmailToNewlyRegisteredUser($userDto);
        }

        return $result;
    }

    public function updateUser(CustomUserDto $userDto, string $userId)
    {
        $currentEmail = $this->userRepository->getEmail($userId);
        
        $result = $this->userRepository->storeUser($userDto, $userId);

        if ($result->status() && strcmp($currentEmail, $userDto->email) !== 0)
        {
            $this->sendEmailToNewlyRegisteredUser($userDto);
        }
        
        return $result;
    }

    private function sendEmailToNewlyRegisteredUser(CustomUserDto $userDto)
    {
        $this->sendMail(
            recipients: $userDto->email, 
            subject: 'Welcome to India Cine Hub', 
            templatePath: 'emails.new_custom_user_emailer', 
            templateData: [
                'name' => $userDto->getFullName(),
                'email' => $userDto->email,
            ]
        );
    }

    public function checkRoleIdExistsBySlug(string $slug, array $roles): bool 
    {
        $roleId = $this->userRepository->getRoleIdBySlug($slug);
        return in_array($roleId, $roles);
    }

    public function listUser()
    {
        $result = $this->userRepository->getUsers(dataTableParams: $this->getDataTableParams(columns: [
            1 => 'full_name',
            2 => 'u.email',
            3 => 'u.mobile',
            4 => 'roles',
            5 => 'u.created_at',
            6 => 'u.status',
        ]));

        $resource = CustomUserResource::collection($result->data()['data']);
    
        $result->setData(
            data: $result->data()['isPagination'] 
                ? $this->getDataTableResult($resource) 
                : $resource
        );

        return $result;
    }

    public function getUser(string $userId)
    {
        $result = $this->userRepository->findById($userId);

        if ($result->status()) {
            $result->setData(CustomUserResponseDto::fromModel($result->data()));
        }

        return $result;
    }
}