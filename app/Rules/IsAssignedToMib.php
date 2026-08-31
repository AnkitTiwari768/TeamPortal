<?php

namespace App\Rules;

use Illuminate\Contracts\Validation\Rule;
use Carbon\Carbon;

class IsAssignedToMib implements Rule
{
    /**
     * Create a new rule instance.
     *
     * @return void
     */
    public function __construct($id)
    {
        $this->id = $id;
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
        $app_id = request()->national_permission_application_id; 
        $isMib = \DB::table('national_permission_applications As np')->select('np.is_sent_to_mib')->where('np.id', $app_id)->first();

        if($this->id == '')   
        {
            
            if($isMib->is_sent_to_mib == 1)
                return false;
            else
                return true;
        }            
        else{
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
        return 'This application is already assigned to Mib';
    }
}
