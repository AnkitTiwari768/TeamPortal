<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
#use Laravel\Sanctum\HasApiTokens;
use Laravel\Passport\HasApiTokens;
use Illuminate\Support\Facades\Hash;
use App\Http\Api\V1\Role\Role;


class User extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable;

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
		'category_id',
        'role_id',
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
		'updated_at',
        'is_sub_user',
        'parent_user_id'
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
        'email_verified_at' => 'datetime',
        'password' => 'hashed',
		'status' => 'boolean',
        'lockout_enabled' => 'boolean'
    ];


    public function scopeUserPermissions($query, $userId)
    {
        return $query->select('permissions.slug')
                ->join('user_permissions', 'users.id', '=', 'user_permissions.user_id')
                ->join('permissions', 'user_permissions.permission_id', '=', 'permissions.id')
                ->where('user_permissions.user_id', $userId)
                ->get();
    }


    public function passwordHistory()
    {
        return $this->hasMany(PasswordHistory::class);
    }


 // Relationship: A SNP user has ONE mapped CA user (one-to-one through pivot)
      public function mappedCaUser ()
      {
          return $this->hasOneThrough(
              User::class,  
              TeamSnpcaMapping::class,  
              'snp_user_id', 
              'id', 
              'id',  
              'ca_user_id' 
          );
      }

       // Optional: Reverse relationship if needed (CA user belongs to one SNP user)
      public function snpUser ()
      {
          return $this->hasOneThrough(
              User::class,
              TeamSnpcaMapping::class,
              'ca_user_id',  
              'id',  
              'id',  
              'snp_user_id'  
          );
      }

}
