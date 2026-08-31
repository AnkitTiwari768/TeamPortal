<?php

namespace App\Http\Requests\Auth;

use Illuminate\Auth\Events\Lockout;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
use App\Models\User;
use Illuminate\Support\Facades\Hash;

class LoginRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        $rules = [
            'username' => ['required', 'string'],
            'password' => ['required', 'string'],
        ];

        if (config('settings.enable_captcha')) {
            $rules['captcha'] = 'required|captcha';
        }

        return $rules;
    }

    public function messages()
    {
        return [
            'g-recaptcha-response.required' => 'Please check you are robot or not?',
            'captcha.required' => 'Captcha is required.',
            'captcha.captcha' => 'Invalid captcha entered.',
        ];
    }

    /**
     * Attempt to authenticate the request's credentials.
     *
     * @return void
     *
     * @throws \Illuminate\Validation\ValidationException
     */
    public function authenticate()
    {
        $this->ensureIsNotRateLimited();
        $credentials = $this->only('username', 'password');
        //$credentials = $this->only('username');
        $username = $credentials['username'];
        $password = crypto_decrypt($credentials['password']);
        $user = $this->getAuthUser($username);

        if (!$user || !Hash::check($password, $user->password)) {
            //if (!$user ) {
            //RateLimiter::hit($this->throttleKey());

            throw ValidationException::withMessages([
                'login' => __('auth.failed'),
            ]);
        }

        RateLimiter::clear($this->throttleKey());

        return true;
        //Auth::login($user, $this->boolean('remember'));

    }

    public function getAuthUser($username)
    {
        $authType = request()->type ?? null;

        $query = User::where('status', config('constant.ACTIVE'));

        if ($authType === 'email') {
            $query->where('email', $username);
        }

        if ($authType === 'mobile') {
            $query->where('mobile', $username);
        }

        if (! $authType) {
            $query
                ->where('email', $username)
                ->orWhere('username', $username);
        }

        return $query->first();
    }

    /**
     * Ensure the login request is not rate limited.
     *
     * @return void
     *
     * @throws \Illuminate\Validation\ValidationException
     */
    public function ensureIsNotRateLimited()
    {
        if (! RateLimiter::tooManyAttempts($this->throttleKey(), 2)) {
            return;
        }

        event(new Lockout($this));

        $seconds = RateLimiter::availableIn($this->throttleKey());

        throw ValidationException::withMessages([
            'username' => trans('auth.throttle', [
                'seconds' => 30,
                'minutes' => ceil($seconds / 60),
            ]),
        ]);
    }

    /**
     * Get the rate limiting throttle key for the request.
     *
     * @return string
     */
    public function throttleKey()
    {
        return Str::lower($this->input('username')) . '|' . $this->ip();
    }
}
