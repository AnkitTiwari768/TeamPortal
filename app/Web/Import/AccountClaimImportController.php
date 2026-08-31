<?php

declare(strict_types=1);

namespace App\Web\Import;

use Maatwebsite\Excel\Facades\Excel;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Cache;

class AccountClaimImportController
{
    public function import(Request $request)
    {
        try {
            // $request->validate([
            //     'claim_type_id' => 'required',
            //     'gst_percentage' => 'required|numeric|min:0|max:100',
            //     'file' => 'required|mimes:xlsx,xls'
            // ]);

            // session()->put('gst_percentage_' . authId(), floatval($request->input('gst_percentage', 0)));

            $rules = [
                'claim_type_id' => 'required|string',
                // 'gst_type' => 'required|in:1,2',
                'file' => 'required|mimes:xlsx,xls'
            ];
            // if ($request->gst_type == '1') { // GST
            //     $rules['gst_percentage'] = 'required|numeric|min:0|max:100';
            //     $rules['cgst_percentage'] = 'nullable';
            //     $rules['sgst_percentage'] = 'nullable';
            // } elseif ($request->gst_type == '2') { // CGST/SGST
            //     $rules['gst_percentage'] = 'nullable';
            //     $rules['cgst_percentage'] = 'required|numeric|min:0|max:100';
            //     $rules['sgst_percentage'] = 'required|numeric|min:0|max:100';
            // }

            // Validate the request
            $validated = $request->validate($rules);
            //   session()->put('gst_type_' . authId(), $request->gst_type);    
            // if ($request->gst_type == '1') {
            //     session()->put('gst_percentage_' . authId(), floatval($request->input('gst_percentage', 0)));
            //     session()->forget('cgst_percentage_' . authId());
            //     session()->forget('sgst_percentage_' . authId());
            // } elseif ($request->gst_type == '2') {
            //     session()->put('cgst_percentage_' . authId(), floatval($request->input('cgst_percentage', 0)));
            //     session()->put('sgst_percentage_' . authId(), floatval($request->input('sgst_percentage', 0)));
            //     session()->forget('gst_percentage_' . authId());
            // }

            $import = new AccountClaimImport;

            Excel::import($import, $request->file('file'));

            $errorToken = Str::uuid()->toString();

            cache()->put(
                'claim_import_errors_' . $errorToken,
                $import->invalidRows,
                now()->addMinutes(30)
            );


            if (count($import->validRows) === 0 && count($import->invalidRows) === 0) {
                return response()->json([
                    'status' => false,
                    'message' => 'Error: could not process empty excel file!'
                ], 422);
            }

            return response()->json([
                'status'  => true,
                'message' => 'Excel processed',
                'summary' => [
                    'total_rows' => $import->totalRows,
                    'inserted'   => count($import->validRows),
                    'failed'     => count($import->invalidRows),
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

        $import = new AccountClaimImport;

        $response = $import->store(authId());

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

    public function downloadErrorReport(string $token)
    {
        $errors = cache()->get('claim_import_errors_' . $token);

        if (!$errors) {
            abort(404, 'Error report expired or not found.');
        }

        return Excel::download(
            new AccountImportErrorExport($errors),
            'account_import_errors.xlsx'
        );
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
}
