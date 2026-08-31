<?php

namespace App\Domain\DemandGeneration;

use App\Domain\NetworkProvider\NetworkProvider;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class DemandGenerationPhase extends Model
{
    use HasUuids;

    protected $table = 'demand_generation_phases';

    protected $fillable = [
        'id',
        'network_provider_id',
        'aov_category_id',
        'phase_start',
        'phase_end',
        'reset_reason',
        'created_at',
    ];

    protected $casts = [
        'phase_start' => 'date',
        'phase_end' => 'date',
        'created_at' => 'datetime',
    ];

    /**
     * Get the network provider for this phase
     */
    public function networkProvider(): BelongsTo
    {
        return $this->belongsTo(NetworkProvider::class, 'network_provider_id');
    }

    /**
     * Get the AOV category for this phase
     */
    public function aovCategory(): BelongsTo
    {
        return $this->belongsTo(DemandGenerationAovCategory::class, 'aov_category_id');
    }

    /**
     * Get claims for this phase
     */
    public function claims(): HasMany
    {
        return $this->hasMany(DemandGenerationClaim::class, 'phase_id');
    }

    /**
     * Get MSE exclusions for this phase
     */
    public function mseExclusions(): HasMany
    {
        return $this->hasMany(MsePhaseExclusion::class, 'phase_id');
    }

    /**
     * Check if phase is active
     */
    public function isActive(): bool
    {
        return $this->phase_end >= now();
    }

    /**
     * Get days remaining in phase
     */
    public function getDaysRemainingAttribute(): int
    {
        return max(0, now()->diffInDays($this->phase_end, false));
    }
}
