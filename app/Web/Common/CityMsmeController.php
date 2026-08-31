<?php

namespace App\Web\Common;

use Illuminate\Http\Request;
use App\Http\Controllers\ClientController;
use App\Web\Common\CityMsmeService;

class CityMsmeController extends ClientController
{
    protected $service;

    public function __construct(CityMsmeService $service)
    {
        $this->service = $service;
    }

    public function cityMmseFemaleList()
    {
        return view('common.city_msme_list', [
            'title' => 'City MSME Registration List'
        ]);
    }

    public function getCityMsmeFemaleList(Request $request)
    {
        $data = $this->service->getCityMsmeFemaleData();
        return $this->formatResponse($data, 'female_mse_registrations');
    }

    public function getTotalMsmeList(Request $request)
    {
        $data = $this->service->getTotalMsmeData();
        return $this->formatResponse($data, 'total_mse_registrations');
    }

    /**
     * Format response for DataTables
     */
    private function formatResponse($data, $countField)
    {
        $rows = [];
        
        foreach ($data as $key => $row) {
            $rows[] = [
                'DT_RowIndex' => $key + 1,
                'tier_1_city' => $row->tier_1_city,
                $countField => $row->$countField,
            ];
        }

        return response()->json(['data' => $rows]);
    }
}