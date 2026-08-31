<?php

namespace App\Domain\DemandGeneration;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class DemandGenerationIncentiveSlab extends Model
{
    use HasUuids;

    protected $table = 'demand_generation_incentive_slabs';

    protected $fillable = [
        'id',
        'aov_category_id',
        'min_transactions',
        'min_unique_mses',
        'incentive_amount',
        'created_at',
    ];

    protected $casts = [
        'min_transactions' => 'integer',
        'min_unique_mses' => 'integer',
        'incentive_amount' => 'decimal:2',
        'created_at' => 'datetime',
    ];

    /**
     * Get the AOV category for this slab
     */
    public function aovCategory(): BelongsTo
    {
        return $this->belongsTo(DemandGenerationAovCategory::class, 'aov_category_id');
    }

    /**
     * Get claims using this slab
     */
    public function claims(): HasMany
    {
        return $this->hasMany(DemandGenerationClaim::class, 'slab_id');
    }

    /**
     * Scope for LOW AOV slabs
     */
    public function scopeLowAov($query)
    {
        return $query->whereHas('aovCategory', function ($q) {
            $q->where('code', 'LOW');
        });
    }

    /**
     * Scope for HIGH AOV slabs
     */
    public function scopeHighAov($query)
    {
        return $query->whereHas('aovCategory', function ($q) {
            $q->where('code', 'HIGH');
        });
    }

    /**
     * Find applicable slab based on transactions and MSEs
     */
    public static function findApplicableSlab(string $aovType, int $transactions, int $uniqueMses): ?self
    {
        return self::whereHas('aovCategory', function ($q) use ($aovType) {
            $q->where('code', $aovType);
        })
            ->where('min_transactions', '<=', $transactions)
            ->where('min_unique_mses', '<=', $uniqueMses)
            ->orderBy('min_transactions', 'desc')
            ->orderBy('min_unique_mses', 'desc')
            ->first();
    }
}
