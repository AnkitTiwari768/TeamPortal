<?php
namespace App\Rules;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Contracts\Validation\Rule;

class NotInPreviousPasswords implements Rule
{
    /**
     * Create a new rule instance.
     *
     * @return void
     */
    public function __construct($user)
    {
        $this->user = $user;
    }

    /**
     * Determine if the validation rule passes.
     *
     * @param  string  $attribute
     * @param  mixed  $value
     * @return bool
     */
    public function passes($attribute, $value)
    {   
        //dd($this->user->password);
        if (Hash::check($value, $this->user->password)) {
            //dd($value);
            return false;
        }

        // Check password history
        $passwordHistory = $this->user->passwordHistory()->latest()->take(3)->get();

        foreach ($passwordHistory as $history) {
            //dd($history->password);
            if (Hash::check($value, $history->password)) {
                return false;
            }
        }
        return  true;
    }

    /**
     * Get the validation error message.
     *
     * @return string
     */
    public function message()
    {
        return 'The provided password cannot be one of the last three passwords used.';
    }
}
