<?php

declare(strict_types=1);

namespace App\Domain\User;

use App\Domain\EmailTemplate\EmailTemplateService;
use App\Domain\UserLog\UserLogService;
use App\Models\User;
use App\Services\PasswordGenerator;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\auth;

class CreateUserAction
{
    protected static bool $isSendEmail = true;

    public function __construct(
        protected UserService $userService,
        protected UserLogService $userLogService
    ) {}

    public function execute(CreateUserDTO $dto): bool
    {

        return DB::transaction(function () use ($dto) {

            $userId = uuid();

           // $status = $this->userService->processStatus($dto->status);
           $password = app(PasswordGenerator::class)->generate();
            $userData = [
                'id' => $userId,
                'first_name' => $dto->firstName,
                'middle_name' => $dto->middleName,
                'last_name' => $dto->lastName,
                'email' => $dto->email,
                'username' => $dto->email,
                'mobile' => $dto->mobile,
                'status' =>  $dto->status,
                'parent_user_id' => authId(),
                'created_at' => now(),
                'created_by' => authId(),
                'password'       => $password,
                 // Administrator creates user => 0, otherwise => 1
                'is_sub_user' => hasRole('administrator') ? 0 : 1,
            ];

          
          //  $userData['password'] = Hash::make($password);

            // Save user
            User::create($userData);

            // Map roles if provided
            $roleId = $this->userService->processUserRole($dto->roleId);
            if ($roleId) {
                DB::table('user_roles')->insert([
                    'user_id' => $userId,
                    'role_id' => $roleId,
                    'type' => 1,
                ]);
            }

            $this->userLogService->logCreated(
                $userId,
                [...$userData, 'role' => $roleId ? DB::table('roles')->where('id', $roleId)->value('name') : null],
                authId()
            );

            // Send email notification with credentials
            if (self::$isSendEmail) {
                app(EmailTemplateService::class)->send(
                    templateKey: 'user-registration',
                    toEmail: $dto->email,
                    data: [
                        'name' => $dto->firstName,
                        'username' => $dto->email,
                        'password' => $password,
                    ]
                );
            }

            return true;
        });
    }
}
