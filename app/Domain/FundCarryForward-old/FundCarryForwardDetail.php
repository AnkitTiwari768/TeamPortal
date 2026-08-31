<?php

declare(strict_types=1);

namespace App\Domain\FundCarryForward;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class FundCarryForwardDetail extends Model
{
    use HasUuids;

    protected $table = 'fund_carry_forward_details';

    protected $fillable = [
        'id',
        'carry_forward_id',
        'major_component_id',
        'sub_component_id',
        'opening_balance',
        'carried_forward_amount',
        'remarks',
        'created_at',
        'updated_at',
    ];

    public function carryForward(): BelongsTo
    {
        return $this->belongsTo(FundCarryForward::class, 'carry_forward_id', 'id');
    }
}
