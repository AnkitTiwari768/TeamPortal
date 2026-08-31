<?php
namespace App\Web\RootManager\Secret;

use App\Web\RootManager\Secret\SecretCode;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Hash;

class SecretCodeService
{
    /**
     * Verify secret code against stored hash
     */
    public function verifyCode($enteredCode)
    {
        try {
            // Get all active codes
            $allCodes = SecretCode::where('status', 'active')->get();
            
            $validCode = null;
            foreach ($allCodes as $code) {
                // Verify entered code against stored hash
                if ($code->verifyCode($enteredCode)) {
                    $validCode = $code;
                    break;
                }
            }

            if (!$validCode) {
                return [
                    'success' => false,
                    'message' => 'Invalid secret code. Please try again.'
                ];
            }

            // Store in session for authentication
            session([
                'secret_authenticated' => true,
                'code_name' => $validCode->code_name,
                'secret_code_id' => $validCode->id,
                'secret_authenticated_at' => now()
            ]);

            return [
                'success' => true,
                'message' => 'Login successful! Redirecting...',
                'data' => [
                    'redirect_url' => route('secret.dashboard')
                ]
            ];

        } catch (\Exception $e) {
            Log::error('Secret code verification error: ' . $e->getMessage());
            return [
                'success' => false,
                'message' => 'An error occurred. Please try again.'
            ];
        }
    }

    /**
     * Check if user is authenticated for secret page
     */
    public function isAuthenticated()
    {
        return session()->has('secret_authenticated') && 
               session('secret_authenticated') === true;
    }

    /**
     * Logout from secret page
     */
    public function logout()
    {
        session()->forget(['secret_authenticated', 'secret_code_id','code_name', 'secret_authenticated_at']);
        return ['success' => true, 'message' => 'Logged out successfully.'];
    }
}