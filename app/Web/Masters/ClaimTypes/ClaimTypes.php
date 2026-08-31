<?php

namespace App\Web\Masters\ClaimTypes;

use App\Core\BaseModel;

class ClaimTypes extends BaseModel
{
    protected $table = 'claim_types';

    protected $fillable = [
        'name',
        'slug',
        'short_name',
        'status',
    ];

    protected $casts = [
        'status' => 'integer'
    ];
}