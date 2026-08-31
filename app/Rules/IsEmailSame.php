<?php
namespace App\Rules;
use Illuminate\Contracts\Validation\Rule;

class IsEmailSame implements Rule
{
    /**
     * Create a new rule instance.
     *
     * @return void
     */
     protected $email;

    public function __construct($email)
    {
        $this->email = $email;
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
        // Check if the phone number and alternate number are different.
        return $value !== $this->email;
    }

    /**
     * Get the validation error message.
     *
     * @return string
     */
    public function message()
    {
    	 return 'The :attribute cannot be the same.'; 
    }
}
