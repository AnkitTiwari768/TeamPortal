<?php

namespace App\Web\Language;

use App\Core\BaseModel;

class Language extends BaseModel
{
    protected $table = 'language';

    protected $fillable = [
        'name',
        'slug',
        'status',
    ];

    protected $casts = [
        'status' => 'integer'
    ];
}