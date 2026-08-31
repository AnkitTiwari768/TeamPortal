<?php

declare(strict_types=1);

namespace App\Domain\DemandGeneration;

use Maatwebsite\Excel\Facades\Excel;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\DB;

class DemandGenerationClaimImportController
{
    public function __construct(private DemandGenerationClaimImportService $demandGenerationService) {}

    public function create()
    {
        $title = __('Demand Generation Incentive Claim');
        $networkProvider = $this->demandGenerationService->getNetworkProviderDetailsByUserID(authId());
        $declarationContent = $this->demandGenerationService->getDeclarationContent();

        return view('demand-generation.form', compact(
            'title',
            'networkProvider',
            'declarationContent'
        ));
    }

    public function import(Request $request)
    {

        $validator = Validator::make($request->all(), [
            'low_aov_excel' => 'nullable|file|mimes:csv,txt',
            'low_aov_claim_start_date' => 'nullable|date',
            'low_aov_claim_end_date' => 'nullable|date|after_or_equal:low_aov_claim_start_date',
            'high_aov_excel' => 'nullable|file|mimes:csv,txt',
            'high_aov_claim_start_date' => 'nullable|date',
            'high_aov_claim_end_date' => 'nullable|date|after_or_equal:high_aov_claim_start_date',
            'declaration' => 'nullable|accepted',
        ], [
            'declaration.accepted' => 'You must accept the declaration to proceed.'
        ]);



        // Custom conditional validation
        $validator->after(function ($validator) use ($request) {
            $lowAovFilled = $request->filled('low_aov_excel') &&
                $request->filled('low_aov_claim_start_date') &&
                $request->filled('low_aov_claim_end_date');

            $highAovFilled = $request->filled('high_aov_excel') &&
                $request->filled('high_aov_claim_start_date') &&
                $request->filled('high_aov_claim_end_date');

            if (!$lowAovFilled && !$highAovFilled) {
                $validator->errors()->add('claim', 'Please enter either Low AOV or High AOV CSV and claim period.');
            }
        });

        // If validation fails, return JSON
        if ($validator->fails()) {
            return response()->json([
                'status' => false,
                'message' => $validator->errors()->first() // returns first validation error
            ], 422);
        }


        try {

            $userId = auth()->id();

            // ✅ DELETE OLD DATA (ADD HERE)
            $tempClaimIds = DB::table('temporary_claims')
                ->where('created_by', $userId)
                ->pluck('id')
                ->toArray();

            if (!empty($tempClaimIds)) {
                DB::table('temporary_claim_orders')
                    ->whereIn('claim_id', $tempClaimIds)
                    ->delete();
            }

            DB::table('temporary_claims')
                ->where('created_by', $userId)
                ->delete();

            $import = new DemandGenerationClaimImport;

            Excel::import($import, $request->file('low_aov_excel'), \Maatwebsite\Excel\Excel::CSV);

            if ($import->claimId) {
                DB::table('temporary_claims')
                    ->where('id', $import->claimId)
                    ->update([
                        'total_uploaded_records' => $import->totalRows,
                        'total_valid_records'    => $import->insertedCount,
                        'total_invalid_records'  => $import->invalidCount,
                        'updated_at'             => now(),
                    ]);
            }

            $errorToken = Str::uuid()->toString();

            cache()->put(
                'claim_import_errors_' . $errorToken,
                $import->invalidRows,
                now()->addMinutes(30)
            );


            $validRowsCount = $import->insertedCount;
            $invalidRowsCount = $import->invalidCount;

            if ($import->totalRows === 0) {
                return response()->json([
                    'status' => false,
                    'message' => 'Error: could not process empty excel file!'
                ], 422);
            }

            return response()->json([
                'status'  => true,
                'message' => $invalidRowsCount > 0 ? 'Claim processing completed with some validation errors' : 'Claim processing completed successfully',
                'summary' => [
                    'total_rows' => $import->totalRows,
                    'inserted'   => $import->insertedCount,
                    'failed'     => $import->invalidCount,
                ],
                'error_token' => $errorToken,
                'failed_rows' => array_slice($import->invalidRows, 0, 10), // only first 10 for UI
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'status' => false,
                'message' => $e->getMessage()
            ], 422);
        }
    }

    public function store(Request $request)
    {
        $request->validate([
            'declaration' => 'nullable'
        ]);

        $import = new DemandGenerationClaimImport;

        $response = $import->store(authId());

        DB::table('temporary_claims')
            ->where('id', $import->claimId)
            ->update([
                'total_uploaded_records' => $import->totalRows,
                'total_valid_records'    => $import->insertedCount,
                'total_invalid_records'  => $import->invalidCount,
                'updated_at'             => now(),
            ]);

        $errorToken = Str::uuid()->toString();

        cache()->put(
            'claim_import_errors_' . $errorToken,
            $import->invalidRows,
            now()->addMinutes(30)
        );

        return response()->json([
            'status'  => true,
            'message' => 'Claim processing completed',
            'summary' => [
                'total_rows' => max(0, $import->totalRows - 1),
                'inserted'   => count($import->validRows),
                'failed'     => count($import->invalidRows),
            ],
            'error_token' => $errorToken,
            'failed_rows' => $import->invalidRows,
            'success_rows' => $import->validRows,
        ]);
    }

    public function downloadErrorReport($token)
    {
        $errors = cache()->get('claim_import_errors_' . $token);

        if (!$errors) {
            return response()->json([
                'status' => false,
                'message' => 'Error report expired or not found'
            ]);
        }

        $fileName = 'error_report_' . now()->timestamp . '.csv';

        $headers = [
            'Row Number',
            'Field',
            'Error Message',
            'Value'
        ];

        $callback = function () use ($errors, $headers) {
            $file = fopen('php://output', 'w');

            fputcsv($file, $headers);

            foreach ($errors as $row) {

                // ✅ FIX HERE
                $errorArray = $row['errors'] instanceof \Illuminate\Support\MessageBag
                    ? $row['errors']->toArray()
                    : $row['errors'];

                foreach ($errorArray as $field => $messages) {
                    foreach ($messages as $message) {
                        fputcsv($file, [
                            $row['row_number'],
                            $field,
                            $message,
                            $row['data'][$field] ?? ''
                        ]);
                    }
                }
            }

            fclose($file);
        };

        return response()->streamDownload($callback, $fileName, [
            'Content-Type' => 'text/csv',
        ]);
    }

    public function downloadPdfErrorReport($token)
    {
        $errors = Cache::get('claim_import_errors_' . $token);

        if (!$errors) {
            abort(404, 'Error report expired or not found.');
        }

        $pdf = Pdf::loadView('pdf.claim_error_report', [
            'errors' => $errors,
            'generated_at' => now()->format('d-m-Y H:i:s')
        ])->setPaper('A4', 'landscape');

        return $pdf->download('claim-error-report.pdf');
    }

    public function reset()
    {
        DB::beginTransaction();

        try {
            $userId = auth()->id();

            $claimIds = DB::table('temporary_claims')
                ->where('created_by', $userId)
                ->pluck('id');

            DB::table('temporary_claim_orders')
                ->whereIn('claim_id', $claimIds)
                ->delete();

            DB::table('temporary_claims')
                ->where('created_by', $userId)
                ->delete();

            DB::commit();

            return response()->json([
                'status' => true,
                'message' => 'Temporary data cleared successfully'
            ]);
        } catch (\Throwable $e) {
            DB::rollBack();

            return response()->json([
                'status' => false,
                'message' => 'Reset failed'
            ]);
        }
    }
}
