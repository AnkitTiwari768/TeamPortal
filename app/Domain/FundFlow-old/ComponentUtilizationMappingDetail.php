<?php

declare(strict_types=1);

namespace App\Domain\FundFlow;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ComponentUtilizationMappingDetail extends Model
{
    use HasUuids;

    protected $table = 'component_utilization_mapping_details';

    protected $fillable = [
        'id',
        'mapping_id',
        'eligible_major_component_id',
        'eligible_sub_component_id',
        'total_allocated_amount',
        'total_distributed_amount',
        'max_utilization_amount',
        'remarks',
        'created_at',
        'updated_at',
    ];

    public function mapping(): BelongsTo
    {
        return $this->belongsTo(ComponentUtilizationMapping::class, 'mapping_id', 'id');
    }
}
