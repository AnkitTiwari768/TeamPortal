<?php

declare(strict_type=1);

namespace App\Domain\Claim;

use App\Traits\Respond;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

final readonly class ClaimWorkflowController
{
    use Respond;

    public function approve(Request $request, ApproveClaimAction $action)
    {
        $validated = $request->validate([
            'batch_id' => ['required', 'uuid', 'exists:dy_batches,id'],
            'claim_id' => ['required', function ($attribute, $value, $fail) {
                // Check if it's a comma-separated string or single UUID
                $ids = is_string($value) ? explode(',', $value) : (array) $value;
                foreach ($ids as $id) {
                    if (!preg_match('/^[0-9a-f]{8}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{12}$/i', trim($id))) {
                        $fail("The $attribute field must contain valid UUIDs.");
                        return;
                    }
                }
            }],
            'claim_type' => ['required', 'exists:claim_types,slug'],
            'comments' => 'nullable|max:1000',
            'tds' => 'nullable|numeric|min:0|max:100',
            'review_status' => 'sometimes|string' // Add this if needed
        ]);

        // Convert comma-separated string to array if needed
        if (is_string($validated['claim_id']) && str_contains($validated['claim_id'], ',')) {
            $validated['claim_id'] = array_map('trim', explode(',', $validated['claim_id']));
        }

        $result = $action->execute($validated);
        return $this->success($result, 'Claims have been approved successfully');
    }

    public function reject(Request $request, RejectClaimAction $action)
    {
        $validated = $request->validate([
            'batch_id' => ['required', 'uuid', 'exists:dy_batches,id'],
            'claim_id' => ['required', function ($attribute, $value, $fail) {
                $ids = is_string($value) ? explode(',', $value) : (array) $value;
                foreach ($ids as $id) {
                    if (!preg_match('/^[0-9a-f]{8}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{12}$/i', trim($id))) {
                        $fail("The $attribute field must contain valid UUIDs.");
                        return;
                    }
                }
            }],
            'claim_type' => ['required', 'exists:claim_types,slug'],
            'comments' => 'nullable|max:1000',
            'review_status' => 'sometimes|string'
        ]);

        if (is_string($validated['claim_id']) && str_contains($validated['claim_id'], ',')) {
            $validated['claim_id'] = array_map('trim', explode(',', $validated['claim_id']));
        }

        $result = $action->execute($validated);
        return $this->success($result, 'Claims have been rejected successfully');
    }

    public function sendQuery(Request $request, SendClaimQueryAction $action)
    {
        $validated = $request->validate([
            'batch_id' => ['required', 'uuid', 'exists:dy_batches,id'],
            'claim_id' => ['required', function ($attribute, $value, $fail) {
                $ids = is_string($value) ? explode(',', $value) : (array) $value;
                foreach ($ids as $id) {
                    if (!preg_match('/^[0-9a-f]{8}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{12}$/i', trim($id))) {
                        $fail("The $attribute field must contain valid UUIDs.");
                        return;
                    }
                }
            }],
            'claim_type' => ['required', 'exists:claim_types,slug'],
            'comments' => 'nullable|max:1000',
            'review_status' => 'sometimes|string'
        ]);

        if (is_string($validated['claim_id']) && str_contains($validated['claim_id'], ',')) {
            $validated['claim_id'] = array_map('trim', explode(',', $validated['claim_id']));
        }

        $result = $action->execute($validated);
        return $this->success($result, 'Query has been raised successfully');
    }

    public function invoiceReuploadRequest(Request $request, InvoiceReuploadRequestAction $action)
    {
        $validated = $request->validate([
            'batch_id' => ['required', 'uuid', 'exists:dy_batches,id'],
            'claim_type' => ['required', 'exists:claim_types,slug'],
            'comments' => 'nullable|max:1000',
            'review_status' => 'sometimes|string'
        ]);

        $result = $action->execute($validated);
        return $this->success($result, 'Request for invoice re-upload has been sent successfully');
    }
}