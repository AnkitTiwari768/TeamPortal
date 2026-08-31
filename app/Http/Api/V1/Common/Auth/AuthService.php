<?php

declare(strict_types=1);

namespace App\Http\Api\V1\Auth;

use App\Http\Api\V1\CustomUserPermission\CustomUserPermissionService;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Str;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;
use App\Http\Services\ApiService;
use App\Models\User;
use App\Http\Services\VerificationService;
use Mail;
use DB;
use App\Shared\AccessControl\UserAccessControl;

class AuthService extends ApiService 
{
    use UserAccessControl;

    private const LOCKOUT_SECONDS = 60;
    private const MAXIMUM_ATTEMPTS = 1;

    public function authenticate(array $request)
    {
        $username = $request['username'] ?? null;
        $password = $request['password'] ?? null;

        $user = User::select(
                'id', 
                'password',
                'email',
                'mobile',
                'alternate_email',
                'alternate_mobile', 
                'failed_attempts', 
                'lockout_enabled', 
                'lockout_enabled_at'
            )
            ->where('email', $username)
            ->orWhere('mobile', $username)
            //->orWhere('alternate_email', $username)
            //->orWhere('alternate_mobile', $username)
            ->first();
            
        // if ($user && !$this->ensureIsNotRateLimited($user->id, (bool) $user->lockout_enabled, (string) $user->lockout_enabled_at)) 
        // {
        //     return response()->json([
        //         'status' => false,
        //         'message' => trans('auth.throttle', [
        //             'seconds' => self::LOCKOUT_SECONDS,
        //             'minutes' => '',
        //         ])
        //     ]);    
        // }

        if (!$user)
        {
            return response()->json([
                'status' => false, 
                'message' => __('auth.failed')
            ]);
        }
        
        // if (!Hash::check($password, $user->password)) 
        // {
        //     $this->updateLockout($user->id, (int) $user->failed_attempts);

        //     return response()->json([
        //         'status' => false, 
        //         'message' => __('auth.failed')
        //     ]);
        // }
       
        //$this->clearRateLimits($user->id);

        if (config('settings.enable_two_factor_authentication'))
        {
            if ($this->sendVerificationCode(key: $username, isAuth: true, user: $user)) 
            {
                return response()->json([
                    'status' => true,
                    'message' => 'OTP sent successfully'
                ]);
            }
        }
        
        $accessToken = $this->createAccessToken($user);

        return $this->responseAccessToken($user->id, $accessToken);
    }

    private function updateLockout(string $userId, int $failedAttempts): void
    {
        $data['failed_attempts'] = $failedAttempts + 1;

        if ($failedAttempts > self::MAXIMUM_ATTEMPTS)
        {
            $data['lockout_enabled'] = true;
            $data['lockout_enabled_at'] = currentDateTime();
        }

        User::where('id', $userId)->update($data);
    }

    private function ensureIsNotRateLimited(string $userId, bool $isLockout, string $lockoutAt): bool
    {
        if (!$isLockout) 
        {
            return true;
        }
        
        $seconds = abs(strtotime($lockoutAt) - strtotime(currentDateTime()));
        
        if ($seconds > self::LOCKOUT_SECONDS) 
        {
            $this->clearRateLimits($userId);
            return true;
        }

        return false;
    }

    private function clearRateLimits(string $userId): void
    {
        User::where('id', $userId)->update([
            'failed_attempts' => 0,
            'lockout_enabled' => false,
            'lockout_enabled_at' => null
        ]);
    }

    public function sendVerificationCode(string $key, ?bool $isAuth = false, ?User $user = null)
    {
        $otp = generateOtp();
        
        if ($isAuth && $user)
        {
            if (isEmail($key))
            {
                return VerificationService::sendVerificationCodeByEmail(email: $key, code: $otp);
            }

            if (isPhoneNumber($key))
            {
                return VerificationService::sendVerificationCodeBySms(phone: $key, code: $otp);
            }
            // if (!empty($user->email) && $user->email !== $key) 
            // {
            //     VerificationService::sendVerificationCodeByEmail(email: $user->email, code: $otp);
            // }
            
            // if (!empty($user->mobile) && $user->mobile !== $key) 
            // {
            //     VerificationService::sendVerificationCodeBySms(phone: $user->mobile, code: $otp);
            // }

            // if (!empty($user->alternate_email) && $user->alternate_email !== $key) 
            // {
            //     VerificationService::sendVerificationCodeByEmail(email: $user->alternate_email, code: $otp);
            // }

            // if (!empty($user->alternate_mobile) && $user->alternate_mobile !== $key) 
            // {
            //     VerificationService::sendVerificationCodeBySms(phone: $user->alternate_mobile, code: $otp);
            // }
        }

        // if (isEmail($key))
        // {
        //     return VerificationService::sendVerificationCodeByEmail(email: $key, code: $otp);
        // }

        // if (isPhoneNumber($key))
        // {
        //     return VerificationService::sendVerificationCodeBySms(phone: $key, code: $otp);
        // }

        return false;
    }

    public function verifyOTP(array $request)
    {
        $key = $request['username'];
        $code = (int) $request['otp'];

        $result = VerificationService::verify($key, $code);

        if (
            $result === VerificationService::CODE_INVALID || 
            $result === VerificationService::CODE_EXPIRED
        )
        {
            return response()->json([
                'status' => false,
                'message' => 'Entered OTP is incorrect or expired!'
            ]);
        }

        $user = User::select('id')
            ->where('email', $key)
            ->orWhere('mobile', $key)
            //->orWhere('alternate_email', $key)
            //->orWhere('alternate_mobile', $key)
            ->first();

        
        $accessToken = $this->createAccessToken($user);
        return $this->responseAccessToken($user->id, $accessToken);
    }

    public function createAccessToken(User $user)
    {
        return $user->createToken(['user_id' => $user->id])->accessToken;
    }

    public function responseAccessToken(string $userId, string $accessToken)
    {
        return response()->json([
            'status' => true, 
            'message' => __('message.login_success'),
            'data' => [
                'user_id' => $userId,
                'permissions_assigned' => $this->getUserPermissionsAssigned($userId),
                'token' => $accessToken
            ]
        ]);
    }

    /**
     * Get deduplicated permission slugs for a user.
     *
     * @param  string       $userId
     * @param  string|null  $roleSlug  When provided, returns only that role's permissions.
     *                                 When null, returns all merged permissions (login default).
     */
    public function getUserPermissionsAssigned(string $userId, ?string $roleSlug = null): array
    {
        return array_values(array_unique(
            (new AuthRepository())->getAllUserPermissions($userId, $roleSlug)
        ));
    }

    /**
     * Resolve the default active role from a list of user role slugs based on priority.
     * Priority: snp > lsp > bnp > first available role.
     *
     * @param  array  $roleSlugs
     * @return string|null
     */
    public function resolveDefaultRole(array $roleSlugs): ?string
    {
        if (empty($roleSlugs)) {
            return null;
        }

        // Standardize input slugs to lowercase for safe matching
        $slugsLower = array_map('strtolower', $roleSlugs);

        $priorityOrder = ['snp', 'lsp', 'bnp'];

        foreach ($priorityOrder as $priorityRole) {
            $index = array_search($priorityRole, $slugsLower, true);
            if ($index !== false) {
                return $roleSlugs[$index]; // Return original case slug
            }
        }

        return $roleSlugs[0];
    }


    // public function getUserPermissionsAssigned(string $userId)
    // {
    //     $permissions = DB::table('users')
    //         ->select('permissions.slug')
    //         ->join('user_permissions', 'users.id', '=', 'user_permissions.user_id')
    //         ->join('permissions', 'user_permissions.permission_id', '=', 'permissions.id')
    //         ->where('user_permissions.user_id', $userId)
    //         ->get()
    //         ->toArray();

    //     $userRoles = DB::table('user_roles')->select('role_id')->where('user_id', $userId)->get()->toArray();

    //     if ($userRoles) {
    //         $userRoles = array_column($userRoles, 'role_id');
    //     }


    //     $rolePermissions = DB::table('role_permissions AS rp')
    //                         ->select('p.slug')
    //                         ->join('permissions AS p', 'rp.permission_id', '=', 'p.id')
    //                         ->whereIn('rp.role_id', $userRoles)->get()->toArray();


    //     $rolePermissions = $rolePermissions ? array_column($rolePermissions, 'slug') : [];
    //     $userPermissions = $permissions ? array_column($permissions, 'slug') : [];

    //     $customUserPermissions = 


    //     $userPermissions = [...$rolePermissions, ...$userPermissions];

        
    //     return array_values(array_unique($userPermissions));
    // }
}