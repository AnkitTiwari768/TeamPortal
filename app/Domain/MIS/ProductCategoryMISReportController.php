<?php

declare(strict_types=1);

namespace App\Domain\MIS;

use Illuminate\Http\Request;

final class ProductCategoryMISReportController
{
    public function index()
    {
        $title = 'Product Category MIS Report';
        return view('mis.product-category-mis-report', compact('title'));
    }

    public function getProductCategoryMISReport(ProductCategoryMISReportAction $action)
    {
        try {           
            $data = $action->execute();
            return response()->json([
                'data' => $data
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'status' => false,
                'message' => $e->getMessage()
            ], 500);
        }
    }
}