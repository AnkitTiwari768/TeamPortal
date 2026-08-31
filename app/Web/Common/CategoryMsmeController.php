<?php

namespace App\Web\Common;

use Illuminate\Http\Request;
use App\Http\Controllers\ClientController;
use App\Web\Common\CategoryMsmeService;

class CategoryMsmeController extends ClientController
{
    protected $service;

    public function __construct(CategoryMsmeService $service)
    {
        $this->service = $service;
    }

    /**
     * Display SNP MSME list page
     */
    public function stateMsmeList()
    {
        return view('common.category_wise_list', [
            'title' => 'Category Wise MSME Count List'
        ]);
    }

    /**
     * Get SNP MSME data for DataTable
     */
    public function getCategoryWiseMsmeData(Request $request)
    {
        try {
            $data = $this->service->getCategoryWiseMsmeData();
            
            $rows = [];
            $sn = 1;
            
            foreach ($data as $row) {
                $rows[] = [
                    'sn' => $sn++,
                    'product_category_name' => $row->product_category_name ?? 'N/A',
                    'open_msme' => (int)($row->open_msme ?? 0),
                    'selected_msme' => (int)($row->selected_msme ?? 0),
                    'onboarded_msme' => (int)($row->onboarded_msme ?? 0),
                ];
            }
            
      
            return response()->json([
                'data' => $rows
            ]);
            
        } catch (\Exception $e) {
            return response()->json([
                'data' => [],
                'error' => $e->getMessage()
            ], 500);
        }
    }
}