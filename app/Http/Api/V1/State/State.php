<?php

namespace App\Http\Api\V1\State;

use App\Traits\UUID;
use Illuminate\Database\Eloquent\Model;

class State extends Model 
{
    use UUID;

    protected $table = 'states';
    public $module = 'State';
    public $incrementing = false;
    public $timestamps = false;

    protected $fillable = [
        'id',
		'country_id',
        'name', 
        'slug',
		'code',
        'status',
        'created_by',
        'updated_by',
        'created_at',
        'updated_at'
    ];

    protected $casts = [
        'status' => 'boolean'
    ];
}