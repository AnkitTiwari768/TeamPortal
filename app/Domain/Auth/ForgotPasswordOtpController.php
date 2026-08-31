<?php

namespace App\Domain\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Illuminate\Validation\Rules;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Session;
use App\Services\PHPMailerService;
use App\Services\OtpGenerator;

class ForgotPasswordOtpController extends Controller
{
    public function index()
    {
        return view('auth.forgot-password-otp.index')->with('title', __('message.forgot_password'));
    }

    public function sendOtp(Request $request)
    {
        $rules = [
            'email' => 'required|email',
        ];

        // If it's a resend request and session has the email, we bypass captcha
        if (!($request->has('resend') && Session::get('forgot_password_email') === $request->email)) {
            $rules['captcha'] = 'required|captcha';
        }

        $validator = Validator::make($request->all(), $rules);

        if ($validator->fails()) {
            return response()->json([
                'status' => false,
                'message' => 'Validation failed.',
                'errors' => $validator->errors()
            ]);
        }

        $user = User::where('email', $request->email)
            ->orWhere('alternate_email', $request->email)
            ->first();

        if (!$user) {
            return response()->json([
                'status' => false,
                'message' => 'Please enter a valid registered email ID'
            ]);
        }

        $existingOtp = DB::table('password_reset_otps')->where('email', $request->email)->first();

        if ($existingOtp) {
            if ($existingOtp->locked_until && Carbon::now()->isBefore(Carbon::parse($existingOtp->locked_until))) {
                $remaining = Carbon::now()->diffInSeconds(Carbon::parse($existingOtp->locked_until));
                return response()->json([
                    'status' => false,
                    'locked' => true,
                    'remaining_seconds' => $remaining,
                    'message' => 'Your account is temporarily locked. ' . $this->getLockMessage($remaining) . ' Minutes:Seconds'
                ]);
            }

            if ($existingOtp->resend_count >= 3) {
                DB::table('password_reset_otps')->where('email', $request->email)->update([
                    'locked_until' => Carbon::now()->addMinutes(30),
                    'resend_count' => 0
                ]);
                $remaining = 1800;
                return response()->json([
                    'status' => false,
                    'locked' => true,
                    'remaining_seconds' => $remaining,
                    'message' => 'Your account is temporarily locked. ' . $this->getLockMessage($remaining) . ' Minutes:Seconds'
                ]);
            }

            $newResendCount = $existingOtp->resend_count + 1;
            $otp = sprintf("%06d", OtpGenerator::generateOtp());
            DB::table('password_reset_otps')->where('email', $request->email)->update([
                'otp' => $otp,
                'resend_count' => $newResendCount,
                'expires_at' => Carbon::now()->addMinutes(5),
                'attempts' => 0,
                'locked_until' => null
            ]);
            $attemptsLeft = 3 - $newResendCount;
        } else {
            $otp = sprintf("%06d", OtpGenerator::generateOtp());
            DB::table('password_reset_otps')->insert([
                'email' => $request->email,
                'otp' => $otp,
                'attempts' => 0,
                'resend_count' => 1,
                'expires_at' => Carbon::now()->addMinutes(5),
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now()
            ]);
            $attemptsLeft = 2;
        }

        $body = view('emails.forgot_password_otp', ['otp' => $otp])->render();
        $to = $request->email;
        $subject = "Change Password OTP";

        app(PHPMailerService::class)->sendEmail($to, $subject, $body);

        Session::put('forgot_password_email', $request->email);

        $message = 'OTP sent to your registered email address.';
        if (isset($attemptsLeft)) {
            if ($attemptsLeft > 0) {
                $message = "OTP sent to your registered email address. {$attemptsLeft} attempt" . ($attemptsLeft > 1 ? 's' : '') . " left";
            } else {
                $message = "OTP sent to your registered email address."; // 0 attempts left
            }
        }

        return response()->json([
            'status' => true,
            'message' => $message
        ]);
    }

    public function showVerifyForm()
    {
        $email = Session::get('forgot_password_email');
        if (!$email) {
            return redirect('/forgot-password-otp');
        }
        return view('auth.forgot-password-otp.verify')->with('title', 'Verify OTP')->with('email', $email);
    }

    public function verifyOtp(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'otp' => 'required',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => false,
                'message' => 'Please provide a valid OTP.',
                'errors' => $validator->errors()
            ]);
        }

        $email = Session::get('forgot_password_email');
        if (!$email) {
            return response()->json([
                'status' => false,
                'message' => 'Session expired. Please request OTP again.'
            ]);
        }

        $record = DB::table('password_reset_otps')->where('email', $email)->first();

        if (!$record) {
            return response()->json([
                'status' => false,
                'message' => 'No OTP request found for this email.'
            ]);
        }

        if ($record->locked_until && Carbon::now()->isBefore(Carbon::parse($record->locked_until))) {
            $remaining = Carbon::now()->diffInSeconds(Carbon::parse($record->locked_until));
            return response()->json([
                'status' => false,
                'locked' => true,
                'remaining_seconds' => $remaining,
                'message' => 'Your account is temporarily locked. ' . $this->getLockMessage($remaining) . ' Minutes:Seconds'
            ]);
        }

        if (Carbon::now()->isAfter(Carbon::parse($record->expires_at))) {
            return response()->json([
                'status' => false,
                'message' => 'OTP expired. Please request a new one.'
            ]);
        }

        // if ($request->otp) // request otp decryption? The mobile forgot password doesn't use crypto_decrypt for verify, only for reset. Let's assume standard POST for simplicity or use decrypt if needed, we'll implement without frontend crypto.

        $user_otp = $request->otp; // or crypto_decrypt if applied in front end

        if ($record->otp !== $user_otp) {
            DB::table('password_reset_otps')->where('email', $email)->increment('attempts');
            $newAttempts = $record->attempts + 1;

            if ($newAttempts >= 3) {
                DB::table('password_reset_otps')->where('email', $email)->update([
                    'locked_until' => Carbon::now()->addMinutes(30)
                ]);
                $remaining = 1800;
                return response()->json([
                    'status' => false,
                    'locked' => true,
                    'remaining_seconds' => $remaining,
                    'message' => 'Your account is temporarily locked. ' . $this->getLockMessage($remaining) . ' Minutes:Seconds'
                ]);
            }

            return response()->json([
                'status' => false,
                'message' => 'Invalid OTP.'
            ]);
        }

        // OTP Valid - Clean up OTP
        DB::table('password_reset_otps')->where('email', $email)->delete();

        // Give them a token to proceed to reset password safely in session
        $resetToken = Str::random(64);
        Session::put('forgot_password_reset_token', $resetToken);

        return response()->json([
            'status' => true,
            'message' => 'OTP verified successfully.',
            'url' => url('/forgot-password-otp-reset')
        ]);
    }

    public function showResetForm()
    {
        if (!Session::has('forgot_password_email') || !Session::has('forgot_password_reset_token')) {
            return redirect('/forgot-password-otp');
        }

        return view('auth.forgot-password-otp.reset')->with('title', 'Reset Password')
            ->with('crypto_salt', session('crypto_salt'))
            ->with('crypto_iv', session('crypto_iv'))
            ->with('crypto_key', session('crypto_key'))
            ->with('crypto_key_size', session('crypto_key_size'))
            ->with('crypto_iterations', session('crypto_iterations'));
    }

    public function resetPassword(Request $request)
    {
        if (!Session::has('forgot_password_email') || !Session::has('forgot_password_reset_token')) {
            return response()->json([
                'status' => false,
                'message' => 'Session expired. Please start over.'
            ]);
        }

        $email = Session::get('forgot_password_email');

        // Decrypt password sent from JS (as per existing login/auth logic)
        $password = $request->get('password');
        $password_confirmation = $request->get('password_confirmation');

        if (function_exists('crypto_decrypt')) {
            try {
                $password = crypto_decrypt($password);
                $password_confirmation = crypto_decrypt($password_confirmation);
            } catch (\Exception $e) {
                // fallback
            }
        }

        $request->merge([
            'password' => $password,
            'password_confirmation' => $password_confirmation,
        ]);

        $validator = Validator::make($request->all(), [
            'password' => [
                'required',
                'confirmed',
                Rules\Password::min(8)
                    ->mixedCase()
                    ->numbers()
                    ->symbols()
                    ->uncompromised(2)
            ],
            'password_confirmation' => 'required',
        ], [
            'password.required' => 'Password is required.',
            'password.confirmed' => 'Passwords do not match.',
            'password.min' => 'Password does not meet required criteria.',
            'password.mixed' => 'Password does not meet required criteria.',
            'password.numbers' => 'Password does not meet required criteria.',
            'password.symbols' => 'Password does not meet required criteria.',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => false,
                'message' => $validator->errors()->first(),
                'errors' => $validator->errors()
            ]);
        }

        $user = User::where('email', $email)
            ->orWhere('alternate_email', $email)
            ->first();

        if ($user) {
            $user->password = $password; // Hash::make is in mutator setPasswordAttribute in User model
            $user->save();
        }

        // Clean up session
        Session::forget('forgot_password_email');
        Session::forget('forgot_password_reset_token');

        // Optional: Send success email (not requested but standard)
        return response()->json([
            'status' => true,
            'message' => 'Your password has been changed successfully. You may Login Now.',
            'url' => url('/forgot-password-otp-success')
        ]);
    }

    public function showSuccess()
    {
        return view('auth.forgot-password-otp.success')->with('title', 'Success');
    }

    public function checkLockStatus(Request $request)
    {
        $email = Session::get('forgot_password_email');
        if (!$email) {
            return response()->json(['locked' => false]);
        }

        $record = DB::table('password_reset_otps')->where('email', $email)->first();

        if ($record && $record->locked_until && Carbon::now()->isBefore(Carbon::parse($record->locked_until))) {
            $remaining = Carbon::now()->diffInSeconds(Carbon::parse($record->locked_until));
            return response()->json([
                'status' => false,
                'locked' => true,
                'remaining_seconds' => $remaining,
                'message' => $this->getLockMessage($remaining)
            ]);
        }

        return response()->json(['locked' => false]);
    }

    protected function getLockMessage($seconds)
    {
        $minutes = (int) floor($seconds / 60);
        $secs = $seconds % 60;

        $timeString = "";
        if ($minutes >= 60) {
            $hours = (int) floor($minutes / 60);
            $minutes = $minutes % 60;
            $timeString .= sprintf("%02d:", $hours);
        }
        $timeString .= sprintf("%02d:%02d", $minutes, $secs);

        return "Try again in {$timeString}";
    }
}
