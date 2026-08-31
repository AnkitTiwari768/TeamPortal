<?php

declare(strict_types=1);

namespace App\Domain\FundAllocation;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class FundAllocationComponentMapping extends Model
{
    use HasUuids;

    protected $table = 'fund_allocation_component_mappings';

    protected $fillable = [
        'id',
        'fund_allocation_id',
        'major_component_id',
        'sub_component_id',
        'amount',
        'created_at',
        'updated_at',
    ];

    public function fundAllocation(): BelongsTo
    {
        return $this->belongsTo(FundAllocation::class, 'fund_allocation_id', 'id');
    }
}
