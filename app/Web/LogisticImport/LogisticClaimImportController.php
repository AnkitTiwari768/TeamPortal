<?php

declare(strict_types=1);

namespace App\Web\LogisticImport;

use Maatwebsite\Excel\Facades\Excel;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class LogisticClaimImportController
{
    public function import(Request $request)
    {
        try {
            // Base validation rules
            $rules = [
                'claim_type_id' => 'required|string',
                // 'gst_type' => 'required|in:1,2',
                'file' => 'required|mimes:xlsx,xls'
            ];

            // Add dynamic validation rules based on GST type
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

            //   session()->put('gst_type_' . authId(), $request->gst_type); // ADD THIS LINE
            // Store GST percentage in session
            // if ($request->gst_type == '1') {
            //     session()->put('gst_percentage_' . authId(), floatval($request->input('gst_percentage', 0)));
            //     // Clear CGST/SGST from session
            //     session()->forget('cgst_percentage_' . authId());
            //     session()->forget('sgst_percentage_' . authId());
            // } elseif ($request->gst_type == '2') {
            //     session()->put('cgst_percentage_' . authId(), floatval($request->input('cgst_percentage', 0)));
            //     session()->put('sgst_percentage_' . authId(), floatval($request->input('sgst_percentage', 0)));
            //     // Clear GST percentage from session
            //     session()->forget('gst_percentage_' . authId());
            // }
            $import = new LogisticClaimImport;

            try {
                Excel::import($import, $request->file('file'));
                $errorToken = Str::uuid()->toString();

                if (count($import->validRows) === 0 && count($import->invalidRows) === 0) {
                    return response()->json([
                        'status' => false,
                        'message' => 'Error: could not process empty excel file!'
                    ], 422);
                }

                cache()->put(
                    'logistic_claim_import_errors_' . $errorToken,
                    $import->invalidRows,
                    now()->addMinutes(30)
                );

                return response()->json([
                    'status' => true,
                    'message' => 'Excel processed',
                    'summary' => [
                        'total_rows' => max(0, $import->totalRows - 1),
                        'inserted' => count($import->validRows),
                        'failed' => count($import->invalidRows),
                    ],
                    'error_token' => $errorToken,
                    'failed_rows' => array_slice($import->invalidRows, 0, 10), // only first 10 for UI
                ]);
            } catch (\Maatwebsite\Excel\Validators\ValidationException $e) {
                // Handle excel specific validation exceptions if they occur
                return response()->json([
                    'status' => false,
                    'message' => 'Validation Failed',
                    'errors' => $e->failures()
                ], 422);
            }
        } catch (\Exception $e) {
            return response()->json([
                'status' => false,
                'message' => $e->getMessage()
            ], 422);
        }
    }

    public function store(Request $request)
    {
        app(LogisticClaimImport::class)->store(auth()->id());

        return response()->json([
            'status' => true,
            'message' => 'Saved successfully'
        ]);
    }

    public function downloadErrorReport(string $token)
    {
        $errors = cache()->get('logistic_claim_import_errors_' . $token);

        if (!$errors) {
            abort(404, 'Error report expired or not found.');
        }

        return Excel::download(
            new LogisticClaimImportErrorExport($errors),
            'logistic_claim_import_errors.xlsx'
        );
    }
}
