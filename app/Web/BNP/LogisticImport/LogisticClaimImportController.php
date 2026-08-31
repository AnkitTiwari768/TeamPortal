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
        $request->validate([
            'file' => 'required|mimes:xlsx,xls'
        ]);

        $import = new LogisticClaimImport;

        try {
            Excel::import($import, $request->file('file'));
        } catch (\Maatwebsite\Excel\Validators\ValidationException $e) {
            // Handle excel specific validation exceptions if they occur
            return response()->json([
                'status' => false,
                'message' => 'Validation Failed',
                'errors' => $e->failures()
            ], 422);
        }

        $errorToken = Str::uuid()->toString();

        cache()->put(
            'logistic_claim_import_errors_' . $errorToken,
            $import->invalidRows,
            now()->addMinutes(30)
        );

        return response()->json([
            'status' => true,
            'message' => 'Excel processed',
            'summary' => [
                'total_rows' => $import->totalRows,
                'inserted' => count($import->validRows),
                'failed' => count($import->invalidRows),
            ],
            'error_token' => $errorToken,
            'failed_rows' => array_slice($import->invalidRows, 0, 10),
        ]);
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
