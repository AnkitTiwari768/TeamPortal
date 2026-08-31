<?php

declare(strict_types=1);

namespace App\Domain\FundCarryForward;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class FundCarryForward extends Model
{
    use HasUuids;

    protected $table = 'fund_carry_forwards';

    protected $fillable = [
        'id',
        'financial_year',
        'from_duration_id',
        'from_sub_duration_id',
        'to_duration_id',
        'to_sub_duration_id',
        'carry_forward_date',
        'total_amount',
        'remarks',
        'status',
        'created_by',
        'updated_by',
        'created_at',
        'updated_at',
    ];

    public function details(): HasMany
    {
        return $this->hasMany(FundCarryForwardDetail::class, 'carry_forward_id', 'id');
    }
}
