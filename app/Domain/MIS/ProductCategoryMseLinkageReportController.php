<?php

declare(strict_types=1);

namespace App\Domain\MIS;

use App\Http\Services\CommonService;
use App\Traits\Respond;
use Maatwebsite\Excel\Facades\Excel;

final class ProductCategoryMseLinkageReportController
{
    use Respond;

    public function index(CommonService $commonService)
    {
        $title = 'Product Category MSE - SNP/BNP/LSP Linkage Report';

        $productCategories = $commonService->getDropdownNewList(
            'sub_domains',
            'status',
            'ASC',
            'name',
            ['id', 'name']
        );

        return view('mis.product-category-mse-linkage-report', compact('title', 'productCategories'));
    }

    public function getProductCategoryMseLinkageReport(ProductCategoryMseLinkageReportAction $action)
    {
        try {
            return $this->success($action->execute());
        } catch (\Exception $e) {
            return $this->error(message: $e->getMessage());
        }
    }

    public function downloadExcel(ProductCategoryMseLinkageReportAction $action)
    {
        return Excel::download(
            new ProductCategoryMseLinkageReportExport($action),
            'Product_Category_MSE_Linkage_Report_' . date('Y_m_d_His') . '.xlsx'
        );
    }
}
