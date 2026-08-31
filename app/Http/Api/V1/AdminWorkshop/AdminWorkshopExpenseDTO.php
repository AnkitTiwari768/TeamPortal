<?php

namespace App\Http\Api\V1\AdminWorkshop;

class AdminWorkshopExpenseDTO
{
    public function __construct(
        public readonly string  $componentId,
        public readonly string  $subcomponentId,
        public readonly float   $expenseAmount,
        public readonly int     $tdsApplicable     = 0,    // 1=Yes, 0=No
        public readonly float   $tdsPercentage     = 0.00,
        public readonly ?string $sanctionOrderNo   = null,
        public readonly ?string $sanctionOrderDate = null,
    ) {}

    public static function fromRequest(AdminWorkshopExpenseRequest $request): self
    {
        return new self(
            componentId:      $request->validated('component_id'),
            subcomponentId:   $request->validated('subcomponent_id'),
            expenseAmount:    (float) $request->validated('expense_amount'),
            tdsApplicable:    (int)   $request->validated('tds_applicable', 0),
            tdsPercentage:    (float) $request->validated('tds_percentage', 0),
            sanctionOrderNo:  $request->validated('sanction_order_no'),
            sanctionOrderDate: $request->validated('sanction_order_date'),
        );
    }
}
