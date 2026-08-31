<?php

namespace App\Web\Common;

use Illuminate\Http\Request;
use App\Http\Controllers\ClientController;
use App\Web\Common\SnpMsmeWiseService;

class SnpMsmeCountController extends ClientController
{
    protected $service;

    public function __construct(SnpMsmeWiseService $service)
    {
        $this->service = $service;
    }

    /**
     * Display SNP MSME list page
     */
    public function snpMsmeList()
    {
        return view('common.snp_msme_wise_list', [
            'title' => 'SNP Wise MSME Count List'
        ]);
    }

    /**
     * Get SNP MSME data for DataTable
     */
    public function getSnpMsmeData(Request $request)
    {
        try {
            $data = $this->service->getSnpMsmeWiseData();
            
            $rows = [];
            $sn = 1;
            
            foreach ($data as $row) {
                $rows[] = [
                    'sn' => $sn++,
                    'snp_id' => $row->snp_id ?? 'N/A',
                    'organization_id' => $row->organization_id ?? 'N/A',
                    'organization_name' => $row->organization_name ?? 'N/A',
                    'open_msme' => (int)($row->open_msme ?? 0),
                    'direct_selection_by_mse' => (int)($row->direct_selection_by_mse ?? 0),
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