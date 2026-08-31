<?php

declare(strict_types=1);

namespace App\Web\MseBulkRegistration;

use Illuminate\View\View;
use Illuminate\Http\Request;
use App\Traits\DataTable;
use Illuminate\Support\Facades\Validator;
use App\Http\Controllers\ClientController;
use Maatwebsite\Excel\Facades\Excel;
use App\Web\MseBulkRegistration\MseDraftBulkUploadService;
use App\Web\MseBulkRegistration\MseFailedExportAll;
use App\Web\MseBulkRegistration\Logging\MseBulkUploadLogService;
use App\Web\MseBulkRegistration\Logging\MseBulkUploadRunType;
use DB;
use Carbon\Carbon;
use Yajra\DataTables\Facades\DataTables;
use Illuminate\Support\Facades\Auth;

use Illuminate\Support\Collection;

class MseDraftBulkUploadController extends ClientController
{
     use DataTable; 
     
    public function draftBulkUpload(): View
    {
        return view('msme.import_draft_udyam')->with('title', 'MSE Bulk Registration');
    }

    public function msme_bulk_draft_import(Request $request)
    {
        $request->validate([
          	'file' => 'required|mimes:xlsm,xlsx,xls|mimetypes:application/vnd.openxmlformats-officedocument.spreadsheetml.sheet'
        ]);

        $logService = app(MseBulkUploadLogService::class);

        /*
         * $logContext starts null and only gets a value once startRun() returns. It is
         * declared outside the try so the catch below can tell "never started" apart from
         * "started, then something broke" — only the latter has a run row to mark Failed.
         * Everything that can fail — including reading the uploaded file's own metadata —
         * now runs inside the try, and the catch is \Throwable rather than \Exception, so a
         * run can no longer be abandoned mid-flight (stuck on Pending with blank fields):
         * it always ends Migrated or Failed.
         */
        $logContext = null;

        try {
            $uploadedFile = $request->file('file');
            $logContext = $logService->startRun(
                MseBulkUploadRunType::ManualUpload,
                roleType: hasRole('snp') ? 1 : 2,
                createdBy: AuthId(),
                fileDetails: [
                    'file_name'      => $uploadedFile->getClientOriginalName(),
                    'file_size'      => $uploadedFile->getSize(),
                    'file_extension' => $uploadedFile->getClientOriginalExtension(),
                ]
            );

            $import = new MseDraftBulkUploadService(
                new MseDraftBulkUpload()
            );
            $import->setLogContext($logContext);

            Excel::import($import, $request->file('file'));
            $summary = $import->getSummary();
           // 🔥 Get only failed mobile + udyam pairs
            $failedPairs = DB::table('team_msme_scheme_temps')
                        ->select('mobile', 'udyam_no')
                        ->distinct()
                        ->get();

            $logService->completeRun($logContext, $import->getLogSummary(), $import->getBatchId());

            // completeRun() records this run's own totals/duration; the run's *status*, per
            // requirement, must instead track the real state of the drafts it just created —
            // right after upload every one of them is still Pending, so this brings the run
            // back down from completeRun()'s default "Migrated" to the correct "Pending".
            $logService->syncBatchStatus($import->getBatchId(), AuthId());

            return response()->json([
                'status'        => 'success',
                'message'       => 'MSE Bulk Registration successfully.',
                'draft_count'   => $summary['draft_count'],
                'temp_count'    => $summary['temp_count'],
                'failed_count'  => $summary['failed_count'],
                'failed_pairs'  => $failedPairs
            ]);

        } catch (\Throwable $e) {

            if ($logContext) {
                $logService->failRun($logContext, $e->getMessage());
            }

            return response()->json([
                'status'  => 'error',
                'message' => $e->getMessage()
            ], 500);
        }
    }

    public function downloadFailedAll()
    {
        $count = DB::table('team_msme_scheme_temps')->count();
        if ($count == 0) {
            return back()->with('error', 'No failed records found.');
        }

        return Excel::download(
            new MseFailedExportAll(),
            'team_msme_temps_failed_records.xlsx'
        );
    }

    public function msmeDraftList()
    {
        return view('msme.msme_draft_list')
        ->with('title', 'MSE Bulk Registration List');
    }

    // public function getMsmeDraftList(Request $request)
    // {

    //     $columns = [
    //         0 => 'id', 
    //         1 => 'udyam_no',
    //         2 => 'mobile',
    //         3 => 'product_category_id',
    //         4 => 'current_state_business_id',
    //         5 => 'ondc_transaction_type_id',
    //         6 => 'status',
    //         7 => 'created_at',
    //     ];

    //     $limit  = $request->input('length');
    //     $start  = $request->input('start');

    //     $orderColumnIndex = $request->input('order.0.column');
    //     $orderColumn = $columns[$orderColumnIndex] ?? 'created_at';
    //     $orderDir = $request->input('order.0.dir') ?? 'desc';
    //     $search = $request->input('search.value');
    //    $query = DB::table('team_msme_scheme_drafts')
    //     ->where('created_by', Auth::id()); // ✅ Added condition

    //     // GLOBAL SEARCH
    //     if (!empty($search)) {
    //         $query->where(function ($q) use ($search) {
    //             $q->where('udyam_no', 'like', "%{$search}%")
    //             ->orWhere('mobile', 'like', "%{$search}%")
    //             ->orWhere('product_category_id', 'like', "%{$search}%")
    //             ->orWhere('current_state_business_id', 'like', "%{$search}%")
    //             ->orWhere('ondc_transaction_type_id', 'like', "%{$search}%")
    //             ->orWhere('status', 'like', "%{$search}%");
    //         });
    //     }
    //     // STATUS FILTER
    //     if ($request->filled('status')) {
    //         $query->where('status', $request->status);
    //     }

    //     // DATE RANGE FILTER (FIXED)
    //     if ($request->filled('from_date') && $request->filled('to_date')) {
    //         $from = Carbon::parse($request->from_date)->startOfDay();
    //         $to   = Carbon::parse($request->to_date)->endOfDay();
    //         $query->whereBetween('created_at', [$from, $to]);
    //     } elseif ($request->filled('from_date')) {
    //         $query->where('created_at', '>=', Carbon::parse($request->from_date)->startOfDay());
    //     } elseif ($request->filled('to_date')) {
    //         $query->where('created_at', '<=', Carbon::parse($request->to_date)->endOfDay());
    //     }

    //     // TOTAL RECORDS
    //     $totalData = DB::table('team_msme_scheme_drafts')->count();
    //     // FILTERED RECORDS
    //     $totalFiltered = $query->count();

    //     // DATA FETCH
    //     $data = $query->offset($start)
    //         ->limit($limit)
    //         ->orderBy($orderColumn, $orderDir)
    //         ->get()
    //         ->map(function ($item) {

    //             if ($item->created_at) {
    //                 $item->created_at = Carbon::parse($item->created_at)
    //                     ->format('d-m-Y h:i A');
    //             }
    //             return $item;
    //         });

    //     // RESPONSE
    //     return response()->json([
    //         "draw" => intval($request->input('draw')),
    //         "recordsTotal" => $totalData,
    //         "recordsFiltered" => $totalFiltered,
    //         "data" => $data
    //     ]);
    // }

    // public function getMsmeDraftList(Request $request)
    // {
    //     $columns = [
    //         0 => 'id', 
    //         1 => 'udyam_no',
    //         2 => 'mobile',
    //         3 => 'product_category_id',
    //         4 => 'current_state_business_id',
    //         5 => 'ondc_transaction_type_id',
    //         6 => 'status',
    //         7 => 'created_at',
    //     ];

    //     $limit  = $request->input('length');
    //     $start  = $request->input('start');

    //     $orderColumnIndex = $request->input('order.0.column');
    //     $orderColumn = $columns[$orderColumnIndex] ?? 'created_at';
    //     $orderDir = $request->input('order.0.dir') ?? 'desc';
    //     $search = $request->input('search.value');

    //     // BASE QUERY
    //     $query = DB::table('team_msme_scheme_drafts');

    //     // ✅ ROLE-BASED FILTER
    //     if (hasRole('snp') || hasRole('ia-registration')) {
    //         $query->where('created_by', Auth::id());
    //     }

    //     // ✅ GLOBAL SEARCH
    //     if (!empty($search)) {
    //         $query->where(function ($q) use ($search) {
    //             $q->where('udyam_no', 'like', "%{$search}%")
    //             ->orWhere('mobile', 'like', "%{$search}%")
    //             ->orWhere('product_category_id', 'like', "%{$search}%")
    //             ->orWhere('current_state_business_id', 'like', "%{$search}%")
    //             ->orWhere('ondc_transaction_type_id', 'like', "%{$search}%")
    //             ->orWhere('status', 'like', "%{$search}%");
    //         });
    //     }

    //     // ✅ STATUS FILTER
    //     if ($request->filled('status')) {
    //         $query->where('status', $request->status);
    //     }

    //     // ✅ DATE RANGE FILTER
    //     if ($request->filled('from_date') && $request->filled('to_date')) {
    //         $from = Carbon::parse($request->from_date)->startOfDay();
    //         $to   = Carbon::parse($request->to_date)->endOfDay();
    //         $query->whereBetween('created_at', [$from, $to]);
    //     } elseif ($request->filled('from_date')) {
    //         $query->where('created_at', '>=', Carbon::parse($request->from_date)->startOfDay());
    //     } elseif ($request->filled('to_date')) {
    //         $query->where('created_at', '<=', Carbon::parse($request->to_date)->endOfDay());
    //     }

    //     // ✅ TOTAL RECORDS (WITH ROLE FILTER)
    //     $totalDataQuery = DB::table('team_msme_scheme_drafts');

    //     if (hasRole('snp') || hasRole('ia-registration')) {
    //         $totalDataQuery->where('created_by', Auth::id());
    //     }

    //     $totalData = $totalDataQuery->count();

    //     // ✅ FILTERED RECORDS
    //     $totalFiltered = $query->count();

    //     // ✅ FETCH DATA
    //     $data = $query->offset($start)
    //         ->limit($limit)
    //         ->orderBy($orderColumn, $orderDir)
    //         ->get()
    //         ->map(function ($item) {
    //             if ($item->created_at) {
    //                 $item->created_at = Carbon::parse($item->created_at)
    //                     ->format('d-m-Y h:i A');
    //             }
    //             return $item;
    //         });

    //     // ✅ RESPONSE
    //     return response()->json([
    //         "draw" => intval($request->input('draw')),
    //         "recordsTotal" => $totalData,
    //         "recordsFiltered" => $totalFiltered,
    //         "data" => $data
    //     ]);
    // }
    // public function msmeProcessDrafts(MseDraftBulkUploadService $processor)
    // {
    //     try {
    //         $result = $processor->msmePendingDrafts();

    //         if (!empty($result['success']) && $result['success'] > 0) {
    //             return response()->json([
    //                 'status'  => true,
    //                 'message' => $result['message'] ?? 'MSME Registration Udyam Process Completed.',
    //                 'success' => $result['success'],
    //                 'failed'  => $result['failed'] ?? 0,
    //             ], 200);
    //         }
    //         return response()->json([
    //             'status'  => false,
    //             'message' => $result['message'] ?? 'No drafts processed.',
    //             'success' => 0,
    //             'failed'  => $result['failed'] ?? 0,
    //         ], 400);

    //     } catch (\Exception $e) {
    //         \Log::error('MSME Draft Process Error: '.$e->getMessage());
    //         return response()->json([
    //             'status'  => false,
    //             'message' => 'Something went wrong while processing drafts.',
    //             'error'   => $e->getMessage(),
    //         ], 500);
    //     }
    // }

//     public function getMsmeDraftList(Request $request)
// {
//     $columns = [
//         0 => 'id', 
//         1 => 'udyam_no',
//         2 => 'mobile',
//         3 => 'product_category_id',
//         4 => 'current_state_business_id',
//         5 => 'ondc_transaction_type_id',
//         6 => 'status',
//         7 => 'created_at',
//     ];

//     $limit  = $request->input('length');
//     $start  = $request->input('start');

//     $orderColumnIndex = $request->input('order.0.column');
//     $orderColumn = $columns[$orderColumnIndex] ?? 'created_at';
//     $orderDir = $request->input('order.0.dir') ?? 'desc';
//     $search = $request->input('search.value');

//     // ✅ BASE QUERY WITH GROUP BY
//     $query = DB::table('team_msme_scheme_drafts')
//         ->select(
//             'udyam_no',
//             DB::raw('id as id'),
//             DB::raw('mobile as mobile'),
//             DB::raw('product_category_id as product_category_id'),
//             DB::raw('current_state_business_id as current_state_business_id'),
//             DB::raw('ondc_transaction_type_id as ondc_transaction_type_id'),
//             DB::raw('status as status'),
//             DB::raw('created_at as created_at')
//         );

//     // ✅ ROLE-BASED FILTER
//     if (hasRole('snp') || hasRole('ia-registration')) {
//         $query->where('created_by', Auth::id());
//     }

//     // ✅ GLOBAL SEARCH
//     if (!empty($search)) {
//         $query->where(function ($q) use ($search) {
//             $q->where('udyam_no', 'like', "%{$search}%")
//               ->orWhere('mobile', 'like', "%{$search}%")
//               ->orWhere('product_category_id', 'like', "%{$search}%")
//               ->orWhere('current_state_business_id', 'like', "%{$search}%")
//               ->orWhere('ondc_transaction_type_id', 'like', "%{$search}%")
//               ->orWhere('status', 'like', "%{$search}%");
//         });
//     }

//     // ✅ STATUS FILTER
//     if ($request->filled('status')) {
//         $query->where('status', $request->status);
//     }

//     // ✅ DATE FILTER
//     if ($request->filled('from_date') && $request->filled('to_date')) {
//         $from = Carbon::parse($request->from_date)->startOfDay();
//         $to   = Carbon::parse($request->to_date)->endOfDay();
//         $query->whereBetween('created_at', [$from, $to]);
//     } elseif ($request->filled('from_date')) {
//         $query->where('created_at', '>=', Carbon::parse($request->from_date)->startOfDay());
//     } elseif ($request->filled('to_date')) {
//         $query->where('created_at', '<=', Carbon::parse($request->to_date)->endOfDay());
//     }

//     // ✅ APPLY GROUP BY
//     $query->groupBy('udyam_no');

//     // ✅ TOTAL RECORDS (WITHOUT FILTER, BUT GROUPED)
//     $totalDataQuery = DB::table('team_msme_scheme_drafts')
//         ->select('udyam_no');

//     if (hasRole('snp') || hasRole('ia-registration')) {
//         $totalDataQuery->where('created_by', Auth::id());
//     }

//     $totalData = $totalDataQuery->groupBy('udyam_no')->get()->count();

//     // ✅ FILTERED COUNT (IMPORTANT FIX)
//     $totalFiltered = DB::table(DB::raw("({$query->toSql()}) as sub"))
//         ->mergeBindings($query)
//         ->count();

//     // ✅ FETCH DATA
//     $data = DB::table(DB::raw("({$query->toSql()}) as sub"))
//         ->mergeBindings($query)
//         ->offset($start)
//         ->limit($limit)
//         ->orderBy($orderColumn, $orderDir)
//         ->get()
//         ->map(function ($item) {
//             if ($item->created_at) {
//                 $item->created_at = Carbon::parse($item->created_at)
//                     ->format('d-m-Y h:i A');
//             }
//             return $item;
//         });

//     // ✅ RESPONSE
//     return response()->json([
//         "draw" => intval($request->input('draw')),
//         "recordsTotal" => $totalData,
//         "recordsFiltered" => $totalFiltered,
//         "data" => $data
//     ]);
// }

    public function getMsmeDraftList(Request $request)
    {
        /*
        |--------------------------------------------------------------------------
        | COLUMN MAP
        |--------------------------------------------------------------------------
        | Indexes match the rendered table: 0 = checkbox, 1 = SN, then the data
        | columns. Both are unorderable, so they are absent from this map.
        */
        $columns = [
            2 => 'udyam_no',
            3 => 'mobile',
            4 => 'product_category_id',
            5 => 'current_state_business_id',
            6 => 'ondc_transaction_type_id',
            7 => 'status',
            8 => 'role_type',
            9 => 'created_at',
        ];

        $limit  = (int) ($request->input('length') ?: 10);
        $start  = (int) $request->input('start', 0);

        $orderColumnIndex = $request->input('order.0.column');
        $orderColumn = $columns[$orderColumnIndex] ?? 'created_at';
        $orderDir = strtolower((string) $request->input('order.0.dir')) === 'asc' ? 'asc' : 'desc';
        $search = $request->input('search.value');

        /*
        | The shared dataTableInit() helper nests filter values under filters[],
        | keyed by the input id. Top-level values are still honoured so any other
        | caller keeps working.
        */
        $filters = (array) $request->input('filters', []);

        $filterValue = function (string $key) use ($filters, $request) {
            $value = $filters[$key] ?? $request->input($key);

            return is_string($value) ? trim($value) : $value;
        };

        $statusFilter = $filterValue('status');
        $fromDate     = $filterValue('from_date');
        $toDate       = $filterValue('to_date');

        /*
        |--------------------------------------------------------------------------
        | BASE QUERY
        |--------------------------------------------------------------------------
        */

        $query = DB::table('team_msme_scheme_drafts')
            ->select(
                'id',
                'udyam_no',
                'mobile',
                'product_category_id',
                'current_state_business_id',
                'ondc_transaction_type_id',
                'status',
                'role_type',
                'created_at'
            );

        /*
        |--------------------------------------------------------------------------
        | ROLE BASED FILTER
        |--------------------------------------------------------------------------
        */
        // if (hasRole('snp') || hasRole('ia-registration')) {
        //     $query->where('created_by', Auth::id());
        // }

        if (hasRole('snp') || hasRole('ia-registration')) {

            $query->where(function ($q) {

                $q->where('created_by', Auth::id());

                if (auth()->user()->parent_user_id) {
                    $q->orWhere('created_by', auth()->user()->parent_user_id);
                }
            });
        }
        /*
        |--------------------------------------------------------------------------
        | GLOBAL SEARCH
        |--------------------------------------------------------------------------
        */
        if (!empty($search)) {
            $query->where(function ($q) use ($search) {
                $q->where('udyam_no', 'like', "%{$search}%")
                ->orWhere('mobile', 'like', "%{$search}%")
                ->orWhere('product_category_id', 'like', "%{$search}%")
                ->orWhere('current_state_business_id', 'like', "%{$search}%")
                ->orWhere('ondc_transaction_type_id', 'like', "%{$search}%")
                ->orWhere('status', 'like', "%{$search}%");
            });
        }

        /*
        |--------------------------------------------------------------------------
        | STATUS FILTER
        |--------------------------------------------------------------------------
        */
        if (!empty($statusFilter)) {
            $query->where('status', $statusFilter);
        }

        /*
        |--------------------------------------------------------------------------
        | DATE FILTER
        |--------------------------------------------------------------------------
        */
        $from = $this->parseFilterDate($fromDate);
        $to   = $this->parseFilterDate($toDate);

        if ($from && $to) {
            $query->whereBetween('created_at', [$from->startOfDay(), $to->endOfDay()]);
        } elseif ($from) {
            $query->where('created_at', '>=', $from->startOfDay());
        } elseif ($to) {
            $query->where('created_at', '<=', $to->endOfDay());
        }

        /*
        |--------------------------------------------------------------------------
        | TOTAL RECORDS
        |--------------------------------------------------------------------------
        */
        // $totalData = DB::table('team_msme_scheme_drafts')
        //     ->when(hasRole('snp') || hasRole('ia-registration'), function ($q) {
        //         $q->where('created_by', Auth::id());
        //     })
        //     ->count();

        $totalData = DB::table('team_msme_scheme_drafts')
            ->when(hasRole('snp') || hasRole('ia-registration'), function ($q) {

                $q->where(function ($subQuery) {

                    $subQuery->where('created_by', Auth::id());

                    if (auth()->user()->parent_user_id) {
                        $subQuery->orWhere('created_by', auth()->user()->parent_user_id);
                    }
                });
            })
            ->count();
        /*
        |--------------------------------------------------------------------------
        | FILTERED RECORDS
        |--------------------------------------------------------------------------
        */
        $totalFiltered = (clone $query)->count();

        /*
        |--------------------------------------------------------------------------
        | FETCH DATA
        |--------------------------------------------------------------------------
        */
        $data = $query->orderBy($orderColumn, $orderDir)
            ->offset($start)
            ->limit($limit)
            ->get()
            ->map(function ($item) {
                if ($item->created_at) {
                    $item->created_at = Carbon::parse($item->created_at)
                        ->format('d-m-Y h:i A');
                }
                $item->uploaded_by = $item->role_type == 1 ? 'SNP' : 'IA';
                return $item;
            });

        /*
        |--------------------------------------------------------------------------
        | RESPONSE
        |--------------------------------------------------------------------------
        | Wrapped by Respond::success() because the shared dataTableInit() helper
        | reads json.data.draw / json.data.data.
        */
        return $this->success([
            "draw" => intval($request->input('draw')),
            "recordsTotal" => $totalData,
            "recordsFiltered" => $totalFiltered,
            "data" => $data
        ], 'MSE bulk registration list fetched successfully.');
    }

    /**
     * Filter dates arrive as dd-mm-yyyy from the shared datepicker. Carbon::parse
     * handles that, but createFromFormat is explicit and other formats still fall
     * back rather than throwing.
     */
    private function parseFilterDate($value): ?Carbon
    {
        if (empty($value)) {
            return null;
        }

        try {
            return Carbon::createFromFormat('d-m-Y', (string) $value)->startOfDay();
        } catch (\Throwable $e) {
            try {
                return Carbon::parse((string) $value);
            } catch (\Throwable $e) {
                return null;
            }
        }
    }

   public function msmeProcessDrafts(MseDraftBulkUploadService $processor)
    {
        try {
            $result = $processor->msmePendingDrafts();
            
            if (!empty($result['success']) && $result['success'] > 0) {
                return response()->json([
                    'status'  => true,
                    'message' => $result['message'] ?? 'MSME Registration Udyam Process Completed.',
                    'success' => $result['success'],
                    'failed'  => $result['failed'] ?? 0,
                ], 200);
            }
            return response()->json([
                'status'  => false,
                'message' => $result['message'] ?? 'No drafts processed.',
                'success' => 0,
                'failed'  => $result['failed'] ?? 0,
            ], 400);

        } catch (\Exception $e) {
            \Log::error('MSME Draft Process Error: '.$e->getMessage());
            return response()->json([
                'status'  => false,
                'message' => 'Something went wrong while processing drafts.',
                'error'   => $e->getMessage(),
            ], 500);
        }
    }
    public function msmeProcessDraftsIa(MseDraftBulkUploadService $processor)
    {
        try {
            $result = $processor->msmePendingDraftsIa();

            if (!empty($result['success']) && $result['success'] > 0) {
                return response()->json([
                    'status'  => true,
                    'message' => $result['message'] ?? 'MSME Registration Udyam Process Completed.',
                    'success' => $result['success'],
                    'failed'  => $result['failed'] ?? 0,
                ], 200);
            }
            return response()->json([
                'status'  => false,
                'message' => $result['message'] ?? 'No drafts processed.',
                'success' => 0,
                'failed'  => $result['failed'] ?? 0,
            ], 400);

        } catch (\Exception $e) {
            \Log::error('MSME Draft Process Error: '.$e->getMessage());
            return response()->json([
                'status'  => false,
                'message' => 'Something went wrong while processing drafts.',
                'error'   => $e->getMessage(),
            ], 500);
        }
    }
    // public function downloadErrorExcel()
    // {
    //    $fileName = 'msme_failed_records_'.date('d-m-Y - H:i').'.xlsm';
       
    //     return Excel::download(new MseFailedExportAll, $fileName);
    // }

  

public function downloadErrorExcel(Request $request)
{
    try {
        // ✅ 1. Get all Failed drafts with role-based filtering
        $drafts = DB::table('team_msme_scheme_drafts')
            ->where('status', 'Failed')
            ->when(hasRole('snp') || hasRole('ia-registration'), function($q) {
                return $q->where('created_by', AuthId());
            })
            ->get();

        // ✅ 2. Get all temps with error messages with role-based filtering
        $temps = DB::table('team_msme_scheme_temps')
            ->whereNotNull('error_message')
            ->when(hasRole('snp') || hasRole('ia-registration'), function($q) {
                return $q->where('created_by', AuthId());
            })
            ->get();

        // ✅ 3. Merge both collections
        $mergedData = $drafts->map(function($item) {
            $item->source = 'Draft';
            return $item;
        })->merge($temps->map(function($item) {
            $item->source = 'Temp';
            return $item;
        }));

        // ✅ 4. Unique based on udyam_no + mobile
        $uniqueData = $mergedData->unique(function ($item) {
            return ($item->udyam_no ?? '') . '_' . ($item->mobile ?? '');
        });

        if ($uniqueData->isEmpty()) {
            if ($request->ajax() || $request->wantsJson()) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'No failed records found.'
                ], 404);
            }
            return back()->with('error', 'No failed records found.');
        }

        // ✅ 5. Excel data
        $finalData = [];
        $finalData[] = [
            'Source',
            'Udyam No',
            'Mobile',
            'Current State Business',
            'Workshop',
            'Turnover',
            'PAN No',
            'GSTIN No',
            'Product Category',
            'ONDC Transaction Type',
            'Error Message',
            'Created Date'
        ];

        foreach ($uniqueData as $row) {
            $finalData[] = [
                $row->source ?? '-',
                $row->udyam_no ?? '-',
                $row->mobile ?? '-',
                $row->current_state_business_id ?? '-',
                $row->attending_ondc_awareness_workshop ?? '-',
                $row->turnover ?? '-',
                $row->pan_no ?? '-',
                $row->gstin_no ?? '-',
                $row->product_category_id ?? '-',
                $row->ondc_transaction_type_id ?? '-',
                $row->error_message ?? 'The provided details do not match the records available on the Udyam Portal.',
                $row->created_at ? Carbon::parse($row->created_at)->format('d-m-Y H:i:s') : '-'
            ];
        }

        $fileName = 'msme_failed_records_' . date('d-m-Y_H-i-s') . '.xlsx';

        return Excel::download(
            new class($finalData) implements \Maatwebsite\Excel\Concerns\FromArray {
                protected $data;
                public function __construct($data) { $this->data = $data; }
                public function array(): array { return $this->data; }
            },
            $fileName
        );

    } catch (\Exception $e) {
        \Log::error('Download Error: ' . $e->getMessage());
        return response()->json([
            'status' => 'error',
            'message' => 'Error: ' . $e->getMessage()
        ], 500);
    }
}
}