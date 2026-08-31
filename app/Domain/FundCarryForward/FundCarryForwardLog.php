<?php

declare(strict_types=1);

namespace App\Domain\FundCarryForward;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class FundCarryForwardLog extends Model
{
    use HasUuids;

    protected $table = 'fund_carry_forward_logs';

    const UPDATED_AT = null;

    protected $fillable = [
        'id',
        'carry_forward_id',
        'financial_year',
        'from_duration_id',
        'to_duration_id',
        'major_component_id',
        'sub_component_id',
        'previous_balance',
        'carried_amount',
        'new_opening_balance',
        'action',
        'remarks',
        'created_by',
        'created_at',
    ];

    protected $casts = [
        'previous_balance' => 'float',
        'carried_amount' => 'float',
        'new_opening_balance' => 'float',
    ];

    public function carryForward(): BelongsTo
    {
        return $this->belongsTo(FundCarryForward::class, 'carry_forward_id', 'id');
    }
}
