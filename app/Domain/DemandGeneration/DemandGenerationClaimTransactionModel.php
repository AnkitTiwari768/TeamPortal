<?php

namespace App\Domain\DemandGeneration;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class DemandGenerationClaimTransaction extends Model
{
    use HasUuids;

    protected $table = 'demand_generation_claim_transactions';

    protected $fillable = [
        'id',
        'claim_id',
        'network_transaction_id',
        'network_transaction_date',
        'seller_id',
        'transaction_completed',
        'order_invoice_number',
        'has_msme_team_cred',
        'total_product_cost',
        'total_tax_on_product',
        'total_discount',
        'offers',
        'logistics_packaging',
        'tax_on_delivery_packaging',
        'misc_charges',
        'created_at',
    ];

    protected $casts = [
        'network_transaction_date' => 'date',
        'transaction_completed' => 'boolean',
        'has_msme_team_cred' => 'boolean',
        'total_product_cost' => 'decimal:2',
        'total_tax_on_product' => 'decimal:2',
        'total_discount' => 'decimal:2',
        'offers' => 'decimal:2',
        'logistics_packaging' => 'decimal:2',
        'tax_on_delivery_packaging' => 'decimal:2',
        'misc_charges' => 'decimal:2',
        'created_at' => 'datetime',
    ];

    /**
     * Get the claim for this transaction
     */
    public function claim(): BelongsTo
    {
        return $this->belongsTo(DemandGenerationClaim::class, 'claim_id');
    }

    /**
     * Get the MSE for this transaction
     */
    public function mse(): BelongsTo
    {
        return $this->belongsTo(Mse::class, 'seller_id', 'bppid_providerid');
    }

    /**
     * Calculate order value (excluding offers/discounts)
     */
    public function getOrderValueAttribute(): float
    {
        return $this->total_product_cost
            + $this->total_tax_on_product
            + $this->logistics_packaging
            + $this->tax_on_delivery_packaging
            + $this->misc_charges;
    }

    /**
     * Calculate total amount (including discounts)
     */
    public function getTotalAmountAttribute(): float
    {
        return $this->getOrderValueAttribute()
            - $this->total_discount
            - $this->offers;
    }

    /**
     * Check if transaction is eligible for incentive
     */
    public function isEligible(float $minOrderValue): bool
    {
        return $this->transaction_completed
            && $this->has_msme_team_cred
            && $this->getOrderValueAttribute() >= $minOrderValue;
    }
}
