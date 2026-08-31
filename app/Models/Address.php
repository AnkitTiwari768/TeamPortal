<?php 

namespace App\Models;

use App\Traits\Mutators;
use Illuminate\Database\Eloquent\Model;

class Address extends Model 
{
    use Mutators;

    protected $table = 'addresses';
    public $module = 'Address';
    public $incrementing = false;
    public $timestamps = false;

    protected $fillable = [
        'id',
		'address',
		'country_id',
        'state_id', 
        'district_id',
		'city_id',
        'postal_code',
        'created_at',
        'updated_at'
    ];

}