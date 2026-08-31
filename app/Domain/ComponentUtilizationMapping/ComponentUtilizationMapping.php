<?php

declare(strict_types=1);

namespace App\Domain\ComponentUtilizationMapping;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class ComponentUtilizationMapping extends Model
{
    use HasUuids;

    protected $table = 'component_utilization_mappings';

    protected $fillable = [
        'id',
        'financial_year',
        'duration',
        'sub_duration',
        'status',
        'created_by',
        'updated_by',
        'created_at',
        'updated_at',
    ];

    public function details(): HasMany
    {
        return $this->hasMany(ComponentUtilizationMappingDetail::class, 'mapping_id', 'id');
    }
}
