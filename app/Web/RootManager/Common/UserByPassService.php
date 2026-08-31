<?php

namespace App\Web\RootManager\Common;

use Illuminate\Support\Facades\DB;

class UserByPassService
{
    public function updateByPass(string $email,int $byPass): array 
    {

        $user = DB::table('users')
            ->where('email', $email)
            ->first();

        if (!$user) {
            return [
                'success' => false,
                'message' => 'User not found.'
            ];
        }

        DB::table('users')
            ->where('email', $email)
            ->update([
                'is_bypass'   => $byPass,
                'updated_at' => now()
            ]);

        return [
            'success' => true,
            'message' => 'User bypass updated successfully.'
        ];
    }
}