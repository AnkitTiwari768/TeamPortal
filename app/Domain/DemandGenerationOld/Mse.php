<?php

namespace App\Domain\DemandGeneration;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Mse extends Model
{
    protected $table = 'mses';

    protected $primaryKey = 'id';
    public $incrementing = false;
    protected $keyType = 'string';

    protected $fillable = [
        'id',
        'bppid_providerid',
        'has_msme_team_cred',
        'status',
        'created_at',
    ];

    protected $casts = [
        'has_msme_team_cred' => 'boolean',
        'status' => 'boolean',
        'created_at' => 'datetime',
    ];

    /**
     * Get claims for this MSE
     */
    public function claimTransactions(): HasMany
    {
        return $this->hasMany(DemandGenerationClaimTransaction::class, 'seller_id', 'bppid_providerid');
    }

    /**
     * Get phase exclusions for this MSE
     */
    public function phaseExclusions(): HasMany
    {
        return $this->hasMany(MsePhaseExclusion::class, 'mse_id');
    }

    /**
     * Check if MSE has MSME TEAM credential
     */
    public function hasMsmeTeamCredential(): bool
    {
        return $this->has_msme_team_cred;
    }

    /**
     * Check if MSE is active
     */
    public function isActive(): bool
    {
        return $this->status;
    }

    /**
     * Find MSE by seller ID
     */
    public static function findBySellerId(string $sellerId): ?self
    {
        return self::where('bppid_providerid', $sellerId)->first();
    }

    /**
     * Get all active MSEs with MSME TEAM credential
     */
    public static function getActiveWithCredential()
    {
        return self::where('has_msme_team_cred', true)
            ->where('status', true)
            ->get();
    }
}
