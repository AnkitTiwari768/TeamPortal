<?php

declare(strict_types=1);

namespace App\Web\Allocation;

use Illuminate\View\View;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Validator;
use App\Http\Controllers\ClientController;
use App\Traits\HasFileUpload;
use App\Core\BaseRequest;
use App\Traits\HasAttribute;

/**
 * AllocationController
 *
 * Handles web routes for the Allocation Module.
 * IMPORTANT: No logic is modified in any existing controller.
 */
class AllocationController extends ClientController
{
    use HasFileUpload, HasAttribute;

    private static string $module = 'allocation';

    public function __construct(
        private readonly AllocationService $service
    ) {}

    /* ─────────────────────────────────────────────────────────────
     | LISTING
     ───────────────────────────────────────────────────────────── */
    public function index(): View
    {
        // guard(config('permissions.allocation-view'));

        return view('allocation.index')->with([
            'title' => __('fund_flow.allocation.list_title'),
            'financialYears' => financial_year(),
            'components' => $this->listOf(code: 'major-components', skipParent: true),
        ]);
    }

    /**
     * DataTable AJAX endpoint.
     */
    public function datalist(): JsonResponse
    {
        guard(config('permissions.allocation-view'));

        try {
            $result = $this->service->getAllocationList();

            // Log the result for debugging
            \Log::info('Allocation DataList Result:', ['result' => $result]);

            return response()->json($result);
        } catch (\Exception $e) {
            \Log::error('Allocation DataList Error:', ['error' => $e->getMessage()]);
            return response()->json([
                'draw' => request('draw', 0),
                'recordsTotal' => 0,
                'recordsFiltered' => 0,
                'data' => []
            ]);
        }
    }

    /* ─────────────────────────────────────────────────────────────
     | CREATE
     ───────────────────────────────────────────────────────────── */
    public function create(): View
    {
        guard(config('permissions.allocation-create'));

        $title = 'Create Allocation';
        $details = (object) $this->service->getFormDetails();

        return view('web.allocation.create', [
            'title' => $title,
            'details' => $details,
            'financialYears' => financial_year(),
            'durations' => $this->service->getDurations(),
            'module_url' => 'web/allocation',
        ]);
    }

    /* ─────────────────────────────────────────────────────────────
     | STORE (Create + Update)
     ───────────────────────────────────────────────────────────── */
    public function store(Request $request, ?string $id = null): JsonResponse
    {
        // For PUT/POST updates, $id comes from the route parameter.
        // Fallback to payload id if somehow not in route (shouldn't happen).
        $id = $id ?? ($request->input('id') ?: null);

        if ($id) {
            guard(config('permissions.allocation-update'));
        } else {
            guard(config('permissions.allocation-create'));
        }

        $validator = Validator::make(
            $request->all(),
            AllocationRequest::getRules($id),
            AllocationRequest::messages()
        );

        if ($validator->fails()) {
            return $this->error($validator->errors());
        }

        $result = $this->service->storeAllocation($validator->validated(), $id);

        return $this->created($result);
    }

    /* ─────────────────────────────────────────────────────────────
     | EDIT
     ───────────────────────────────────────────────────────────── */
    public function edit(string $id): View
    {
        guard(config('permissions.allocation-update'));

        $title = 'Edit Allocation';
        $details = (object) $this->service->getFormDetails();
        $row = (array) $this->service->getHeaderDetail($id);
        $lines = $this->service->getLines($id);

        // Load existing sub-durations so the dropdown is pre-populated
        $subDurations = [];
        if (!empty($row['duration_id'])) {
            $subDurations = $this->service->getAttributeValues(
                config('allocation.duration_code', 'duration'),
                $row['duration_id']
            );
        }

        return view('web.allocation.edit', [
            'title' => $title,
            'details' => $details,
            'row' => $row,
            'lines' => $lines,
            'subDurations' => $subDurations,
            'financialYears' => financial_year(),
            'durations' => $this->service->getDurations(),
            'module_url' => 'web/allocation',
        ]);
    }

    /* ─────────────────────────────────────────────────────────────
     | VIEW (Detail)
     ───────────────────────────────────────────────────────────── */
    public function show(string $id): View
    {
        guard(config('permissions.allocation-view'));

        $title = 'Allocation Details';
        $row = (array) $this->service->getHeaderDetail($id);
        $lines = $this->service->getLines($id);

        return view('web.allocation.view', [
            'title' => $title,
            'row' => $row,
            'lines' => $lines,
            'module_url' => 'web/allocation',
        ]);
    }

    /* ─────────────────────────────────────────────────────────────
     | FILE UPLOAD
     ───────────────────────────────────────────────────────────── */
    public function uploadDocument(Request $request): JsonResponse
    {
        abort_if(!acl(config('permissions.allocation-create')) && !acl(config('permissions.allocation-update')), 403);

        $validator = \Illuminate\Support\Facades\Validator::make(
            $request->all(),
            BaseRequest::getCommonFileRules(5000),
            BaseRequest::getCommonFileRulesMessages()
        );

        if ($validator->fails()) {
            return $this->error($validator->errors());
        }

        $uploaded = $this->uploadFile(
            $request->file('file'),
            config('upload.allocation_document_path', 'allocation-documents')
        );

        // Return the file_system_name so the hidden input and document_path column get set correctly
        return response()->json([
            'status' => true,
            'message' => 'File uploaded successfully.',
            'file_name' => $uploaded->file_system_name,
            'data' => $uploaded,
        ]);
    }

    /* ─────────────────────────────────────────────────────────────
     | API: Components by Major Component
     ───────────────────────────────────────────────────────────── */
    public function getComponents(Request $request): JsonResponse
    {
        guard(config('permissions.allocation-view'));

        $components = $this->service->getMajorComponents();
        return response()->json(['data' => $components]);
    }

    /* ─────────────────────────────────────────────────────────────
     | API: Sub-Components by Component
     ───────────────────────────────────────────────────────────── */
    public function getSubComponents(Request $request): JsonResponse
    {
        guard(config('permissions.allocation-view'));

        $subComponents = $this->service->getSubComponents($request->component_id);
        return response()->json(['data' => $subComponents]);
    }

    /* ─────────────────────────────────────────────────────────────
     | API: Attribute Values (for dynamic dropdowns)
     ───────────────────────────────────────────────────────────── */
    public function getAttributeValues(Request $request, string $attributeId): JsonResponse
    {
        guard(config('permissions.allocation-view'));

        $values = $this->service->getAttributeValues($attributeId, $request->parent_id);
        return response()->json(['data' => $values]);
    }

    /**
     * View/Download the uploaded document.
     */
    public function viewDocument(string $id)
    {
        guard(config('permissions.allocation-view'));

        try {
            $row = $this->service->getHeaderDetail($id);
            if (empty($row['document_url'])) {
                abort(404, 'No document attached to this allocation.');
            }

            $path = base_path($row['document_url']);
            if (!file_exists($path)) {
                \Log::error('Allocation document file not found:', ['path' => $path]);
                abort(404, 'Document file not found on server.');
            }

            return response()->file($path);
        } catch (\Exception $e) {
            \Log::error('Error viewing allocation document:', ['error' => $e->getMessage()]);
            abort(404);
        }
    }
}
