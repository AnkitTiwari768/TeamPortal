<?php
namespace App\Rules;
use Illuminate\Contracts\Validation\Rule;

class StrongPassword implements Rule
{
    /**
     * Create a new rule instance.
     *
     * @return void
     */
    public function __construct()
    {
        //
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
        if (strlen($value) > 8) { 
            return false;
        } 
        // At least one uppercase letter
        if (!preg_match('/[A-Z]/', $value)) {
            return false;
        }

        // At least one lowercase letter
        if (!preg_match('/[a-z]/', $value)) {
            return false;
        }

        // At least one digit
        if (!preg_match('/[0-9]/', $value)) {
            return false;
        }

        // At least one special character
        if (!preg_match('/[^a-zA-Z0-9]/', $value)) {
            return false;
        }
        return true;
    }

    /**
     * Get the validation error message.
     *
     * @return string
     */
    public function message()
    {
        return 'The :attribute must be a strong password.';
    }
}
