<?php

namespace App\Web\Masters\Attributes;

use App\Core\BaseModel;

class Attributes extends BaseModel
{
    protected $table = 'attributes';

    protected $fillable = [
        'id', // ✅ MUST ADD
        'name',
        'code',
        'status',
        'created_at',
        'updated_at',
    ];

    protected $casts = [
        'status' => 'integer'
    ];

    public $timestamps = true;
    // ✅ UUID support
    public $incrementing = false;
    protected $keyType = 'string';
}