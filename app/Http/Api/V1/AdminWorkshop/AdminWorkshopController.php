<?php

namespace App\Http\Api\V1\AdminWorkshop;

use App\Http\Controllers\Controller;
use Exception;

class AdminWorkshopController extends Controller
{
    public function __construct(protected AdminWorkshopService $service) {}


    public function addExpense(AdminWorkshopExpenseRequest $request, string $adminWorkshopId)
    {
        try {
            $dto     = AdminWorkshopExpenseDTO::fromRequest($request);
            $expense = $this->service->addAdminWorkshopExpense($adminWorkshopId, $dto);

            return response()->json([
                'success' => true,
                'message' => 'Expense added successfully.',
                'data'    => $expense,
            ], 201);

        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to add expense.',
                'error'   => $e->getMessage(),
            ], 500);
        }
    }
}
