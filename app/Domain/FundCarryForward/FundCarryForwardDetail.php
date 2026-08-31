<?php

declare(strict_types=1);

namespace App\Domain\FundCarryForward;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class FundCarryForwardDetail extends Model
{
    use HasUuids;

    /**
     * major_component_id is NOT NULL, but an "unallocated header remainder" carry-forward
     * line (see StoreFundAllocationAction::recordCarryForwardHistory()) isn't tied to any
     * one component. Use this sentinel instead of altering the schema.
     */
    public const UNALLOCATED_COMPONENT = '00000000-0000-0000-0000-000000000001';

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
