<?php
namespace App\Rules;
use Illuminate\Contracts\Validation\Rule;

class IsPhoneSame implements Rule
{
    /**
     * Create a new rule instance.
     *
     * @return void
     */
     protected $alternateNumber;

    public function __construct($alternateNumber)
    {
        $this->alternateNumber = $alternateNumber;
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
        return $value !== $this->alternateNumber;
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
