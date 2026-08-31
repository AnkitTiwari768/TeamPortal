<?php

declare(strict_types=1);

namespace App\Web\CA;

use App\Traits\DataTable;
use App\Core\BaseService;
use App\Http\Services\CommonService;
use App\Models\User;
use App\Contracts\GrantType;
use DB;

class CAService extends BaseService
{
    use DataTable;

    public function generateStrongPassword(int $length = 8): string
    {
        $upper = 'ABCDEFGHIJKLMNOPQRSTUVWXYZ';
        $lower = 'abcdefghijklmnopqrstuvwxyz';
        $numbers = '0123456789';
        $symbols = '!@#$%^&*()-_=+<>?';

        $all = $upper . $lower . $numbers . $symbols;

        $password = $upper[random_int(0, strlen($upper) - 1)] .
                    $lower[random_int(0, strlen($lower) - 1)] .
                    $numbers[random_int(0, strlen($numbers) - 1)] .
                    $symbols[random_int(0, strlen($symbols) - 1)];

        for ($i = 4; $i < $length; $i++) {
            $password .= $all[random_int(0, strlen($all) - 1)];
        }

        return str_shuffle($password);
    }

    public function store(array $payload, ?string $id = null): bool
    {
        return DB::transaction(function () use ($payload, $id) {
            $commonService = new CommonService();

            if (!empty($payload['password'])) {
                $password = bcrypt($payload['password']);
            } else {
                $password = !$id ? bcrypt($this->generateStrongPassword(8)) : null;
            }

            $userData = [
                'first_name' => $payload['first_name'],
                'middle_name' => $payload['middle_name'] ?? null,
                'last_name' => $payload['last_name'],
                'mobile' => $payload['mobile'],
                'email' => $payload['email'],
                'username' => $payload['email'],
                'status' => 1,
                'is_role_mapped' => 1,
            ];

            if ($password !== null) {
                $userData['password'] = $password;
            }

            if (!$id) {
                $userData['id'] = uuid();
                $userId = User::create($userData)->id;
            } else {
                User::where('id', $id)->update($userData);
                $userId = $id;
            }

            $snpCaData = [
                'id' => uuid(),
                'ca_user_id' => $userId,
                'snp_user_id' => AuthId(),
                'created_at' => currentDateTime(),
                'updated_at' => currentDateTime(),
            ];

            DB::table('team_snpca_mapping')->where('snp_user_id', AuthId())->delete();
            DB::table('team_snpca_mapping')->insert($snpCaData);

            $roleId = $commonService->getRoleId_by_slug('ca');

            $updatedUserRoles = [
                'user_id' => $userId,
                'role_id' => $roleId->id,
                'type' => GrantType::THROUGH_ROLE,
            ];

            DB::table('user_roles')->where('user_id', $userId)->delete();
            DB::table('user_roles')->insert($updatedUserRoles);

            $rolePermissions = DB::table('role_permissions')
                ->where('role_id', $roleId->id)
                ->pluck('permission_id')
                ->toArray();

            $userPermissions = DB::table('user_permissions')
                ->where('user_id', $userId)
                ->pluck('permission_id')
                ->toArray();

            $mergedUserPermissions = array_unique(array_merge($rolePermissions, $userPermissions));

            $updatedUserPermissions = array_map(fn($permissionId) => [
                'user_id' => $userId,
                'permission_id' => $permissionId,
                'type' => GrantType::THROUGH_ROLE,
            ], $mergedUserPermissions);

            DB::table('user_permissions')->where('user_id', $userId)->delete();
            DB::table('user_permissions')->insert($updatedUserPermissions);

            /*
            $email = $payload['email'];
            $templateData['name'] = $payload['authorized_person_name'];

            if (!$id) {
                $subject = "Registration Successful";
                $templateData['status'] = 'new';
            } else {
                $subject = "Registration Update Successful";
                $templateData['status'] = 'update';
            }

            $body = view('emails.snp_registration_mail', $templateData)->render();
            app(PHPMailerService::class)->sendEmail($email, $subject, $body);
            */

            return true;
        });
    }
}
