<?php

declare(strict_types=1);

namespace App\Domain\ComponentUtilizationMapping;

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
        'source_major_component',
        'source_sub_component',
        'allocated_amount',
        'released_amount',
        'remaining_balance',
        'amount_to_be_allocated',
        'target_category',
        'target_sub_component',
        'remarks',
        'status',
        'created_by',
        'updated_by',
        'created_at',
        'updated_at',
    ];

    public function mapping(): BelongsTo
    {
        return $this->belongsTo(ComponentUtilizationMapping::class, 'mapping_id', 'id');
    }
}
