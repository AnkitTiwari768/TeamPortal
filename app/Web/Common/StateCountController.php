<?php

namespace App\Web\Common;

use Illuminate\Http\Request;
use App\Http\Controllers\ClientController;
use App\Web\Common\StateCountService;

class StateCountController extends ClientController
{
    protected $service;

    public function __construct(StateCountService $service)
    {
        $this->service = $service;
    }

    /**
     * Display SNP MSME list page
     */
    public function stateCountList()
    {
        return view('common.state_count_list', [
            'title' => 'State Count List'
        ]);
    }

    /**
     * Get SNP MSME data for DataTable
     */
    public function getStateMsmeData(Request $request)
    {
        try {
            $data = $this->service->getStateMsmeData();
            
            $rows = [];
            $sn = 1;
            
            foreach ($data as $row) {
                $rows[] = [
                    'sn' => $sn++,
                    'state_name' => $row->state_name ?? 'N/A',
                    'count' => (int)($row->count ?? 0),
                    
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