<?php 

namespace App\Http\Api\V1\User;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Database\Eloquent\Builder;
use Laravel\Passport\HasApiTokens;
use App\Traits\{
	UUID,
	Mutators\Hashable
};


class User extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable, Hashable, UUID;

    protected $table = 'users';
	public $module = 'User';
    public $incrementing = false;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
	    'id',
		'first_name',
		'middle_name',
		'last_name',
		'username',
		'password',
		'email',
		'email_verified',
		'email_verified_at',
		'alternate_email',
		'alternate_email_verified',
		'alternate_email_verified_at',
		'mobile',
		'mobile_verified',
		'mobile_verified_at',
        'alternate_mobile',
		'alternate_mobile_verified',
		'alternate_mobile_verified_at',
		'landline_number',
        'is_role_mapped',
		'department_id',
		'designation_id',
        'status',
		'photo_file_upload_id',
        'profile_picture',
		'address_id',
		'last_login_at',
        'failed_attempts',
        'lockout_enabled',
        'lockout_enabled_at',
        'created_by',
        'updated_by',
		'created_at',
		'updated_at'
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<int, string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'lockout_enabled' => 'boolean'
    ];

    public function getUserById(Builder $query, string $id)
    {
        return $query->selectRaw('email, mobile, 
            TRIM(
                CONCAT_WS(" ", first_name, middle_name, last_name)
            ) AS company_representative_name
        ')
        ->where('id', $id)
        ->first();
    }
}