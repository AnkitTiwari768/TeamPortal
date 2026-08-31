<?php

declare(strict_types=1);

namespace App\Domain\AICataloguingClaim;

use App\Http\Controllers\Controller;
use App\Web\Claim\ClaimReviewStatus;
use App\Web\Claim\ClaimService;
use App\Web\ClaimForm\ClaimFormController;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;
use Illuminate\View\View;
use PhpOffice\PhpSpreadsheet\IOFactory;

class AICataloguingClaimController extends Controller
{
    public function __construct(
        private AICataloguingClaimService $claimService,
        private ClaimService $generalClaimService
    ) {}

    public function index(): View
    {
        $claimSlug = 'claim-for-ai-cataloguing';
        $claimFormController = app(ClaimFormController::class);
        $claim_types = $claimFormController->getClaimTypes($claimSlug);
        $claimTypeIdValue = $claimFormController->getClaimTypeId($claimSlug);
        $documentCategory = $claimFormController->getDocumentCategoryId();
        $documentDeclarationCategory = $claimFormController->getDocumentDeclarationCategoryId();
        $redirectUrl = 'ai-cataloguing-claim';

        $tabs = $this->generalClaimService->getClaimsDataTableDetails($claimSlug);
        $tabs = collect($tabs)->firstWhere('claim_type_slug', $claimSlug);
        $declaration = $this->generalClaimService->getLowestClaimWorkflowDeclaration($claimSlug);
        $showRejectLabel = false;

        return view('claim-form.index', compact(
            'claimSlug',
            'showRejectLabel',
            'redirectUrl',
            'claim_types',
            'claimTypeIdValue',
            'tabs',
            'declaration',
            'documentCategory',
            'documentDeclarationCategory'
        ))->with('title', 'Claim for AI Cataloguing');
    }

    public function create(): View
    {
        $title = __('AI Cataloguing Claim');
        $networkProvider = $this->claimService->getNetworkProviderDetailsByUserID(authId());
        $declarationContent = $this->claimService->getDeclarationContent('claim-for-ai-cataloguing');

        return view('ai-cataloguing.form', compact(
            'title',
            'networkProvider',
            'declarationContent'
        ));
    }

    public function import(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'excel_file' => 'required|file|mimes:xlsx,xls,csv|max:5120',
            'gst_type' => 'required|in:1,2',
            'gst_percentage' => 'exclude_unless:gst_type,1|required|numeric|min:0|max:100',
            'cgst_percentage' => 'exclude_unless:gst_type,2|required|numeric|min:0|max:100',
            'sgst_percentage' => 'exclude_unless:gst_type,2|required|numeric|min:0|max:100',
            'declaration' => 'required|accepted'
        ], [
            'declaration.accepted' => 'You must accept the declaration to proceed.'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => false,
                'message' => $validator->errors()->first()
            ], 422);
        }

        try {
            $import = new AICataloguingImport();
            \Maatwebsite\Excel\Facades\Excel::import($import, $request->file('excel_file'));

            $errorToken = Str::uuid()->toString();
            Cache::put('claim_import_errors_' . $errorToken, $import->invalidRows, now()->addMinutes(30));

            return response()->json([
                'status' => true,
                'message' => count($import->invalidRows) > 0 ? 'Claims processed with some errors.' : 'Excel file processed successfully.',
                'summary' => [
                    'total_rows' => $import->totalRows,
                    'inserted' => $import->insertedCount,
                    'failed' => $import->invalidCount
                ],
                'error_token' => $errorToken,
                'failed_rows' => array_slice($import->invalidRows, 0, 10)
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'status' => false,
                'message' => $e->getMessage()
            ], 422);
        }
    }

    public function store(Request $request, CreateAICataloguingClaimAction $action): JsonResponse
    {
        $request->validate([
            'declaration' => 'required|accepted',
            'gst_type' => 'required|in:1,2',
            'gst_percentage' => 'exclude_unless:gst_type,1|required|numeric|min:0|max:100',
            'cgst_percentage' => 'exclude_unless:gst_type,2|required|numeric|min:0|max:100',
            'sgst_percentage' => 'exclude_unless:gst_type,2|required|numeric|min:0|max:100'
        ]);

        $result = $action->execute(
            authId(),
            $request->input('gst_type'),
            (float)$request->input('gst_percentage', 0),
            (float)$request->input('cgst_percentage', 0),
            (float)$request->input('sgst_percentage', 0)
        );

        if ($result['status']) {
            return response()->json([
                'status' => true,
                'message' => 'Claim submitted successfully!'
            ], 201);
        }

        return response()->json([
            'status' => false,
            'message' => $result['message']
        ], 422);
    }

    public function downloadErrorReport(string $token)
    {
        $errors = Cache::get('claim_import_errors_' . $token);

        if (!$errors) {
            return response()->json([
                'status' => false,
                'message' => 'Error report expired or not found.'
            ], 404);
        }

        $fileName = 'ai_cataloguing_error_report_' . now()->timestamp . '.csv';

        $headers = [
            'Row Number',
            'Field',
            'Error Message',
            'Value'
        ];

        return response()->streamDownload(function () use ($errors, $headers) {
            $file = fopen('php://output', 'w');
            fputcsv($file, $headers);

            foreach ($errors as $row) {
                foreach ($row['errors'] as $field => $messages) {
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
        }, $fileName, [
            'Content-Type' => 'text/csv'
        ]);
    }

    public function downloadTemplate()
    {
        return \Maatwebsite\Excel\Facades\Excel::download(
            new AICataloguingClaimTemplateExport(),
            'ai_cataloguing_claim_template.xlsx'
        );
    }
}
