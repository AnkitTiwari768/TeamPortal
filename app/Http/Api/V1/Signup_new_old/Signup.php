<?php

namespace App\Http\Api\V1\Signup;
use App\Core\BaseModel;
use Illuminate\Notifications\Notifiable;
use Illuminate\Database\Eloquent\Builder;

class Signup extends BaseModel 
{
    protected $table = 'applicants';
    public $module = 'Signup';
    use Notifiable;

    protected $fillable = [
        'id',
        'user_id', 
        'production_type',
        'representative_type',
		'production_name',
		'country_id',
		'state_id',
		'city_id',
		'address_first',
		'address_second',
		'postal_code',
		'status',
		'terms_condition',
		'created_at',
		'updated_at',
        'created_by',
		'updated_by'
    ];

    protected $casts = [
        'status' => 'int'
    ];
	
    public function scopeByUserId(Builder $query, string $userId) : void 
    {
        $query->where('user_id', $userId);
    }
}