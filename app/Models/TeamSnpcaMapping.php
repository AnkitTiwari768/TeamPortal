<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TeamSnpcaMapping extends Model
{
    use HasFactory;

    protected $table = 'team_snpca_mapping';  

    protected $fillable = [
        'snp_user_id',
        'ca_user_id',
    ];

    public function snpUser()
    {
        return $this->belongsTo(User::class, 'snp_user_id');
    }

    public function caUser()
    {
        return $this->belongsTo(User::class, 'ca_user_id');
    }

    // Optional: Scope to get mapping for a specific SNP user (ensures only one)
    // public function scopeForSnpUser($query, $snpUserId)
    // {
    //     return $query->where('snp_user_id', $snpUserId)->first();  
    // }
}
