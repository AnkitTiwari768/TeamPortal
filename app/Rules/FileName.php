<?php

namespace App\Rules;

use Illuminate\Contracts\Validation\Rule;
use Symfony\Component\HttpFoundation\File\UploadedFile;

class FileName implements Rule
{
    /**
     * Create a new rule instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->regex = '/^[a-zA-Z0-9-_ ]+$/';
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
        if (!($value instanceof UploadedFile) || !$value->isValid()) {
            return false;
        }

        $filename = pathinfo($value->getClientOriginalName(), PATHINFO_FILENAME);
        
        return preg_match($this->regex, $filename) > 0;
    }

    /**
     * Get the validation error message.
     *
     * @return string
     */
    public function message()
    {
        return 'The :attribute name is invalid, special characters are not allowed in file name';
    }
}
