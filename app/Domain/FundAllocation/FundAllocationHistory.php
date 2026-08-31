<?php

declare(strict_types=1);

namespace App\Domain\FundAllocation;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class FundAllocationHistory extends Model
{
    use HasUuids;

    protected $table = 'fund_allocation_histories';

    protected $fillable = [
        'id',
        'fund_allocation_id',
        'financial_year',
        'duration_id',
        'sub_duration_id',
        'type',
        'fresh_allocation_amount',
        'carried_forward_amount',
        'component_lines',
        'total_amount_allocated_after',
        'total_available_amount_after',
        'sanction_order_number',
        'sanction_order_date',
        'document_path',
        'remarks',
        'created_by',
    ];

    protected $casts = [
        'component_lines' => 'array',
        'sanction_order_date' => 'date',
    ];

    public function fundAllocation(): BelongsTo
    {
        return $this->belongsTo(FundAllocation::class, 'fund_allocation_id', 'id');
    }
}
