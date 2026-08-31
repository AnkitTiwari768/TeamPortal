<?php

namespace App\Domain\DemandGeneration;

use App\Domain\NetworkProvider\NetworkProvider;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class DemandGenerationClaim extends Model
{
    use HasUuids;

    protected $table = 'demand_generation_claims';

    protected $fillable = [
        'id',
        'network_provider_id',
        'phase_id',
        'aov_category_id',
        'unique_mse_count',
        'cumulative_transactions',
        'slab_id',
        'incentive_amount',
        'status',
        'declaration_accepted',
        'low_aov_excel_document_id',
        'low_aov_excel_document_name',
        'high_aov_excel_document_id',
        'high_aov_excel_document_name',
        'submitted_at',
        'approved_at',
    ];

    protected $casts = [
        'declaration_accepted' => 'boolean',
        'submitted_at' => 'datetime',
        'approved_at' => 'datetime',
        'incentive_amount' => 'decimal:2',
    ];

    public function networkProvider(): BelongsTo
    {
        return $this->belongsTo(NetworkProvider::class, 'network_provider_id');
    }

    public function phase(): BelongsTo
    {
        return $this->belongsTo(DemandGenerationPhase::class, 'phase_id');
    }

    public function aovCategory(): BelongsTo
    {
        return $this->belongsTo(DemandGenerationAovCategory::class, 'aov_category_id');
    }

    public function slab(): BelongsTo
    {
        return $this->belongsTo(DemandGenerationIncentiveSlab::class, 'slab_id');
    }

    public function transactions(): HasMany
    {
        return $this->hasMany(DemandGenerationClaimTransaction::class, 'claim_id');
    }
}
