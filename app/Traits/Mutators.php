<?php 

namespace App\Traits;

use Illuminate\Support\Str;

trait Mutators 
{
    public function setSlugAttribute($value)
    {
        $this->attributes['slug'] = Str::of($value)->slug('-');
    }
	
	public function setFullNameAttribute($value)
    {
        $this->attributes['full_name'] = ucwords(strtolower($value));
    }
} 