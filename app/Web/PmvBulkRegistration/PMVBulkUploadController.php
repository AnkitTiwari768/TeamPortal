<?php

declare(strict_types=1);

namespace App\Web\PmvBulkRegistration;

use Illuminate\View\View;
use Illuminate\Http\Request;
use App\Traits\DataTable;
use Illuminate\Support\Facades\Validator;
use App\Http\Controllers\ClientController;
use App\Web\PmvBulkRegistration\PMVBulkUploadService;
use Maatwebsite\Excel\Facades\Excel;
use App\Web\PmvBulkRegistration\FailedRowsExport;
use App\Web\PmvBulkRegistration\FailedErrorsExport;
use Illuminate\Support\Facades\DB;
use Yajra\DataTables\DataTables;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use App\Traits\SubUserTrait; // ✅ Import Trait

class PMVBulkUploadController extends ClientController
{
    use SubUserTrait; // ✅ Use Trait

    protected $service;
    
    public function __construct(PMVBulkUploadService $service)
    {
        $this->service = $service;
    }

    public function index()
    {
        return view('pmv_bulk_registration.pmv_bulk_registration')
            ->with('title','PMV Bulk Registration');
    }

    public function import(Request $request)
    {
        $request->validate([
            'file'=>'required|mimes:xls,xlsx,xlsm'
        ]);

        try{
            $result = $this->service->processExcel($request->file('file'));
            return response()->json([
                'status' => 'success',
                'success_count' => $result['success'],
                'failed_count'  => $result['failed'],
                'total_count'   => $result['total']
            ]);
        } catch(\Exception $e){
            return response()->json([
                'message'=>$e->getMessage()
            ],422);
        }
    }

    public function downloadFailed()
    {
        return Excel::download(new FailedRowsExport, 'PMV_failed_records.xlsx');
    }

    public function list()
    {
        return view('pmv_bulk_registration.pmv_bulk_registration_list')
            ->with('title','PMV Bulk Registration List');
    }

    public function pmvDataList(Request $request)
    {
        $result = $this->service->getPmvDataList($request);
        return response()->json($result);
    }

    // Get error count for badge - ✅ UPDATED
    public function getErrorCount()
    {
        // Get all user IDs including sub-users
        $userIds = $this->getUserIdsWithSubUsers();
        
        $count = DB::table('pm_vishwakarma_bulk_registration_errors')
            ->whereIn('created_by', $userIds) // ✅ Use whereIn
            ->count();
            
        return response()->json(['count' => $count]);
    }

    // Download errors from error table - ✅ UPDATED
    public function downloadErrorsExcel()
    {
        try {
            // Get all user IDs including sub-users
            $userIds = $this->getUserIdsWithSubUsers();
            
            $count = DB::table('pm_vishwakarma_bulk_registration_errors')
                ->whereIn('created_by', $userIds) // ✅ Use whereIn
                ->count();
            
            if ($count == 0) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'No failed records found to download.'
                ], 404);
            }
            
            // Pass userIds to export
            $export = new FailedErrorsExport($userIds);
            return Excel::download($export, 'PMV_Error_Records_' . date('d-m-Y') . '.xlsx');
            
        } catch (\Exception $e) {
            \Log::error('Error downloading errors: ' . $e->getMessage());
            return response()->json([
                'status' => 'error',
                'message' => 'Failed to download errors: ' . $e->getMessage()
            ], 500);
        }
    }
}