<?php

namespace App\Domain\DemandGeneration;

use App\Domain\NetworkProvider\NetworkProvider;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class MsePhaseExclusion extends Model
{
    use HasUuids;

    protected $table = 'mse_phase_exclusions';

    protected $fillable = [
        'id',
        'network_provider_id',
        'mse_id',
        'phase_id',
        'created_at',
    ];

    protected $casts = [
        'created_at' => 'datetime',
    ];

    /**
     * Get the network provider for this exclusion
     */
    public function networkProvider(): BelongsTo
    {
        return $this->belongsTo(NetworkProvider::class, 'network_provider_id');
    }

    /**
     * Get the MSE for this exclusion
     */
    public function mse(): BelongsTo
    {
        return $this->belongsTo(Mse::class, 'mse_id');
    }

    /**
     * Get the phase for this exclusion
     */
    public function phase(): BelongsTo
    {
        return $this->belongsTo(DemandGenerationPhase::class, 'phase_id');
    }

    /**
     * Check if MSE is excluded for a network provider in current phase
     */
    public static function isExcluded(string $mseId, string $networkProviderId, string $phaseId): bool
    {
        return self::where('mse_id', $mseId)
            ->where('network_provider_id', $networkProviderId)
            ->where('phase_id', $phaseId)
            ->exists();
    }
}
