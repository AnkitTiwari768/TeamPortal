<?php

namespace App\Http\Api\V1\PMRegistration;
use App\Core\BaseModel;
use Illuminate\Notifications\Notifiable;
use Illuminate\Database\Eloquent\Builder;

class PMRegistration extends BaseModel 
{
    protected $table = 'pm_registrations';
    public $module = 'pm_registration';
    use Notifiable;

    protected $fillable = [
        'id',
        'owner_name',
        'store_name',
        'mobile',
        'email',
        'pan_no',
        'pin_code',
        'address',
        'type_of_business',
        'other',
        'remark',

        'cancelled_cheque_file',
        'cancelled_cheque_file_id',
        'vishwakarma_form_copy_file',
        'vishwakarma_form_copy_file_id',

        'status',
        'created_at',
        'updated_at'
    ];

    protected $casts = [
        'status' => 'int'
    ];
	
    public function scopeByUserId(Builder $query, string $userId) : void 
    {
        $query->where('user_id', $userId);
    }
}