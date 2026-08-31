<?php

namespace App\Rules;

use Illuminate\Contracts\Validation\Rule;
use Carbon\Carbon;

class IsAssignableToNextEvaluator implements Rule
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
        $query = \DB::table('national_permission_script_evaluator_assignment As npsea')->select('npsea.status')->where('npsea.national_permission_application_id', $app_id)->orderBy('created_at', 'desc')->limit(1)->get();

        if(count($query)>0)
        {
            $status = $query[0]->status;
            if($this->id == '')   
            {
                
                if($status==null)
                    return false;
                else if($status == 2 || $status == 4 || $status == 6)
                    return false;
                else
                    return true;
            }            
            else{
                return true;
            }
        }else{
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
        return 'This application is under process by a script evaluator';
    }
}
