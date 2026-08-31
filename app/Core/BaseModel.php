<?php

namespace App\Core;;

use App\Traits\UUID;
use Illuminate\Database\Eloquent\Model;

abstract class BaseModel extends Model 
{
    use UUID;

    public $incrementing = false;
    public $timestamps = false;
}