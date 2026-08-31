<?php

namespace App\Rules;

use App\Http\Api\V1\ScriptEvaluator\ScriptEvaluatorAssignment as Model;
use Illuminate\Contracts\Validation\Rule;
use Carbon\Carbon;

class LimitValidation implements Rule
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
        $count = \DB::table('national_permission_applications')->select('script_evaluator_counter')->where('id', $app_id)->first();
        $counter = $count->script_evaluator_counter;
        if($this->id == '')   
            return $counter>=3 ? false : true;
        else
            return true;
    }

    /**
     * Get the validation error message.
     *
     * @return string
     */
    public function message()
    {
        return 'This application has been already assigned 3 times';
    }
}
