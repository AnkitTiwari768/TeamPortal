<?php

namespace App\Web\Common;

use Illuminate\Http\Request;
use App\Http\Controllers\ClientController;
use App\Web\Common\StateWiseCountService;

class StateWiseCountController extends ClientController
{
    protected $service;

    public function __construct(StateWiseCountService $service)
    {
        $this->service = $service;
    }

    /**
     * Display SNP MSME list page
     */
    public function stateMsmeList()
    {
        return view('common.state_wise_list', [
            'title' => 'State Wise MSME Count List'
        ]);
    }

    /**
     * Get SNP MSME data for DataTable
     */
    public function getStateWiseMsmeData(Request $request)
    {
        try {
            $data = $this->service->getStateWiseMsmeData();
            
            $rows = [];
            $sn = 1;
            
            foreach ($data as $row) {
                $rows[] = [
                    'sn' => $sn++,
                    'state_name' => $row->state_name ?? 'N/A',
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