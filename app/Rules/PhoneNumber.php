<?php
namespace App\Rules;
use Illuminate\Contracts\Validation\Rule;

class PhoneNumber implements Rule
{
    /**
     * Create a new rule instance.
     *
     * @return void
     */
    public $type;
    public function __construct($type=null)
    {
       $this->type=$type;
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
        if (preg_match("~^0\d+$~", $value)) {
            return false;
        }
        else {
             return true;
        }
    }

    /**
     * Get the validation error message.
     *
     * @return string
     */
    public function message()
    {
        if($this->type){
        return 'The '.$this->type.' field format is invalid.';
        }else{            
        return 'The :attribute field format is invalid.';
        }
    }
}
