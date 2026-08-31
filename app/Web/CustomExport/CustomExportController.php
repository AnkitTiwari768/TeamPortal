<?php

declare(strict_types=1);

namespace App\Web\CustomExport;

use Illuminate\Http\Request;
use App\Http\Controllers\ClientController;
use Maatwebsite\Excel\Facades\Excel;
use App\Web\SNP\SNPMSMEService;
use App\Web\CustomExport\CatalogueExportService;

class CustomExportController extends ClientController
{
    private SNPMSMEService $service;

    public function __construct(SNPMSMEService $service)
    {
        $this->service = $service;
    }

    public function exportByIds(Request $request)
    {
    
        $ids = $request->input('selectedIds', []);
        if (empty($ids)) {
            return back()->with('error', 'No IDs selected');
        }

        return Excel::download(
            new CustomExportService($ids, $this->service),
            'Open MSME'.'.xlsx'
        );
    }
    public function exportByIdsCatalogue(Request $request)
    {
        $ids = $request->input('selectedIds', []);

        if(empty($ids)){
            return response()->json([
                'message'=>'No rows selected'
            ],400);
        }

        return Excel::download(
            new CatalogueExportService($ids),
            'Catalogue Created List.xlsx'
        );
    }
}
