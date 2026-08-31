<?php

namespace App\Rules;

use App\Modules\District\District as Model;
use Illuminate\Contracts\Validation\Rule;
use Carbon\Carbon;

class UniqueDistrict implements Rule
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
        $state_id = request()->state_id; 
        $title = request()->title; 
        if($this->id == '')
            $model = Model::where('state_id', $state_id)->where('title',$title)->first();
        else
            $model = Model::where('state_id', $state_id)->where('title',$title)->whereNotIn('id',[$this->id])
            ->first();
        return $model ? false : true;
    }

    /**
     * Get the validation error message.
     *
     * @return string
     */
    public function message()
    {
        return 'The :attribute already created for this State';
    }
}
