<?php

declare(strict_types=1);

namespace App\Domain\FundAllocation;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class FundAllocation extends Model
{
    use HasUuids;

    protected $table = 'fund_allocations';

    protected $fillable = [
        'id',
        'financial_year',
        'duration_id',
        'sub_duration_id',
        'sanction_order_number',
        'sanction_order_date',
        'document_path',
        'remarks',
        'total_amount_allocated',
        'total_available_amount',
        'created_by',
        'updated_by',
        'created_at',
        'updated_at',
    ];

    public function componentMappings(): HasMany
    {
        return $this->hasMany(FundAllocationComponentMapping::class, 'fund_allocation_id', 'id');
    }
}
