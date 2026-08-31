<?php

namespace App\Models;
 
use Illuminate\Foundation\Auth\User as Authenticatable; 
use Illuminate\Support\Facades\Hash;

class PasswordHistory extends Authenticatable
{ 
	protected $table = 'password_history';
	public $module = 'Password History';
    public $incrementing = false;
    public $timestamps = false;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
	    'user_id',
        'password'
    ];
 
}
