<?php 

declare(strict_types=1);

namespace App\Http\Api\V1\CustomUser;

use Illuminate\Support\Facades\DB;
use Throwable;

class CustomUserRepository
{
    public function storeUser(CustomUserDto $userDto, ?string $userId = null): Result
    {
        try 
        {
            return DB::transaction(function () use ($userDto, $userId) {
                $userData = CustomUserMapper::mapToUser($userDto, $userId);
                $addressData = CustomUserMapper::mapToAddress($userDto, $userId);
                $userRolesData = CustomUserMapper::mapToUserRoles($userDto, $userId ?? $userData['id']);
                
                if (! $userId) 
                {
                    $userData['address_id'] = $addressData['id'] ?? null;

                    $this->createUser($userData);            
                    
                    if ($addressData) 
                    {
                        $this->createAddress($addressData);
                    }

                    if ($userRolesData) 
                    {
                        $this->createUserRoles($userRolesData);
                    }
                }
                else 
                {
                    $this->updateUser($userData, $userId);

                    if ($addressData) 
                    {
                        $this->updateAddress($addressData, addressId: $this->getAddressId($userId));
                    }

                    if ($userRolesData) 
                    {
                        $this->deleteUserRoles($userId);
                        $this->createUserRoles($userRolesData);
                    }
                }

                return new Result(status: true);
            });
        }
        catch (Throwable $exception) 
        {
            return new Result(
                status: false, 
                message: $exception->getMessage()
            );
        }
    }

    public function createUser(array $userData)
    {
        DB::table('users')->insert($userData);
    }

    public function updateUser(array $userData, string $userId)
    {
        DB::table('users')
            ->where('id', $userId)
            ->update($userData); 
    }

    public function createAddress(array $addressData)
    {
        DB::table('addresses')->insert($addressData);
    }

    public function updateAddress(array $addressData, string $addressId)
    {
        DB::table('addresses')
            ->where('id', $addressId)
            ->update($addressData);
    }

    public function createUserRoles(array $userRolesData) 
    {
        DB::table('user_roles')->insert($userRolesData);
    }

    public function deleteUserRoles(string $userId)
    {
        DB::table('user_roles')
            ->where('user_id', $userId)
            ->delete();
    }

    public function getAddressId(string $userId)
    {
        return DB::table('users')->where('id', $userId)->value('address_id');
    }

    public function getEmail(string $userId): string
    {
        return DB::table('users')->where('id', $userId)->value('email');
    }

    public function getRoleIdBySlug(string $slug): ?string
    {
        return DB::table('roles')->where('slug', 'nodal-officer')->value('id');
    }

    public function getUsers(array $dataTableParams)
    {
        [$limit, $order, $dir, $search, $page, $filters] = $dataTableParams;

        $query = DB::table('users AS u')
            ->select(
                'u.id',
                DB::raw('CONCAT_WS(" ", u.first_name, u.last_name) AS full_name'),
                'u.email',
                'u.mobile',
                'roles.roles_json AS roles',
                'u.status',
                'u.created_at'
            )
            ->leftJoinSub(
                DB::table('user_roles as ur')
                    ->join('roles as r', 'ur.role_id', '=', 'r.id')
                    ->select('ur.user_id', DB::raw('JSON_ARRAYAGG(r.name) as roles_json'))
                    ->groupBy('ur.user_id'),
                'roles',
                'roles.user_id',
                '=',
                'u.id'
            );

            if (isset($search) && !empty($search)) {
                $query  
                    ->where(function($subQuery) use ($search) {
                        return $subQuery
                                ->having('full_name', 'LIKE', "%{$search}%")
                                ->orWhere('u.email', 'LIKE', "{$search}%")
                                ->orWhere('u.mobile', 'LIKE', "{$search}%")
                                ->orWhereRaw('roles.roles_json LIKE ?', ["%{$search}%"])
                                ->orWhereRaw('DATE_FORMAT(u.created_at, '. config('constant.mysql_app_date') .') LIKE ?')
                                ->orWhere(function ($subQuery) use ($search) {
                                    $subQuery
                                        ->where('u.status', '=', 1)
                                        ->whereRaw('"Active" LIKE ?', ["%{$search}%"]);
                                })
                                ->orWhere(function ($subQuery) use ($search) {
                                    $subQuery
                                        ->where('u.status', '!=', 1)
                                        ->whereRaw('"In-active" LIKE ?', ["%{$search}%"]);
                                });

                    });
            }
        
            if (isset($filters['role_id']) && !empty($filters['role_id'])) {
                $query->whereRaw('roles.roles_json like ?', ["%". $filters['role_id'] ."%"]);
            }
    
            if (isset($filters['status']) && !empty($filter['status'])) {
                $query->where('u.status', $filters['status']);
            }
    
            if (isset($filters['status']) && $filters['status'] === '0') {
                $query->where('u.status', $filters['status']);
            }

            $query
                ->where('u.is_custom', true)
                ->whereIn('u.created_by', \authIdsWithSubUsers());

            if ($order === 'id') {
                $query->orderBy('u.created_at', 'desc');
            } else {
                $query->orderBy($order, $dir); 
            }
                
            $isPagination = isset($page) && !empty($page);
            
            $resultData = [
                'isPagination' => $isPagination,
                'data' => $isPagination ? $query->paginate($limit) : $query->limit($limit)->get()
            ];

            return new Result(status: true, data: $resultData);
    }

    public function findById(string $userId): Result
    {
        $userData = DB::table('users AS u')
            ->select(
                'u.id', 'u.first_name', 'u.last_name', 
                'u.email', 'u.mobile', 'u.status',
                'roles.roles_json AS roles',
                'a.state_id',
                'u.zone_id',
                'u.asi_circle_id',
            )
            ->leftJoin('addresses as a', 'a.id', '=', 'u.address_id')
            ->leftJoinSub(
                DB::table('user_roles as ur')
                    ->join('roles as r', 'ur.role_id', '=', 'r.id')
                    ->select(
                        'ur.user_id', 
                        DB::raw('JSON_ARRAYAGG(JSON_OBJECT("id", r.id, "name", r.name)) as roles_json')
                    )
                    ->groupBy('ur.user_id'),
                'roles',
                'roles.user_id',
                '=',
                'u.id'
            )
            ->where('u.id', $userId)
            ->first();

        if (! empty($userData)) {
            return new Result(status: true, data: $userData);
        }

        return new Result(status: false, message: 'Could not find user');
    }
}