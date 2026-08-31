<?php
namespace App\Http\Api\V1\AuditTrail;   
use App\Models\User; 
use Illuminate\Database\Eloquent\Model;

class AuditTrail extends Model 
{ 
    protected $table = 'audit_trail'; 
    public $timestamps = false;
    protected $fillable = [
        'module_name',
        'activity_type',
        'activity_data',
        'user_id',  
        'last_login', 
        'ip_address',
        'created_at',
    ];
  
    public function getUser()
    {
        return $this->belongsTo(User::class, 'user_id', 'id');
    }  
    
}