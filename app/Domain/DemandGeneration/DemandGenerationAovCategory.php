<?php

namespace App\Domain\DemandGeneration;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class DemandGenerationAovCategory extends Model
{
    use HasUuids;

    protected $table = 'demand_generation_aov_categories';

    protected $fillable = [
        'id',
        'code',
        'incentive_per_txn',
    ];

    protected $casts = [
        'incentive_per_txn' => 'decimal:2',
    ];

    /**
     * Get incentive slabs for this category
     */
    public function incentiveSlabs(): HasMany
    {
        return $this->hasMany(DemandGenerationIncentiveSlab::class, 'aov_category_id');
    }

    /**
     * Get phases for this category
     */
    public function phases(): HasMany
    {
        return $this->hasMany(DemandGenerationPhase::class, 'aov_category_id');
    }

    /**
     * Get claims for this category
     */
    public function claims(): HasMany
    {
        return $this->hasMany(DemandGenerationClaim::class, 'aov_category_id');
    }
}
