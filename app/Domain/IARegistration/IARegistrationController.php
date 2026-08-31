<?php

declare(strict_types=1);

namespace App\Domain\IARegistration;

use App\Traits\Respond;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

final readonly class IARegistrationController
{
    use Respond;

    public function register(IARegistrationRequest $request, IARegistrationAction $action): JsonResponse
    {
        return $this->created(
            message: __('Form submitted successfully. Your credentials will be sent to your email once your profile has been reviewed.'),
            data: $action->execute($request->validated())
        );
    }
    public function updateIAProfile(IARegistrationRequest $request, UpdateIAProfileAction $action): JsonResponse
    {
        // /dd($request->all());
        return $this->created(
            message: __('Your updated profile has been submitted and it is under review'),
            data: $action->execute($request->validated())
        );
    }

    public function approveIA(Request $request, ApproveIA $action): JsonResponse
    {
        $validated = $request->validate([
            'ia_registration_id' => 'required|exists:industrial_associations,id',
            'remarks' => 'nullable|max:1000'
        ]);

        $action->execute(data: $validated);
        return $this->success(message: 'Association Registration Approved Successfully');
    }


    public function rejectIA(Request $request, RejectIA $action): JsonResponse
    {
        $validated = $request->validate([
            'ia_registration_id' => 'required|exists:industrial_associations,id',
            'remarks' => 'nullable|max:1000'
        ]);

        $action->execute(data: $validated);
        return $this->success(message: 'Association Registration Rejected Successfully');
    }

    public function pendingIA(ListPendingIA $action): JsonResponse
    {
        return $this->success(data: $action->execute());
    }

    public function verifiedIA(ListPendingIA $action): JsonResponse
    {
        return $this->success(data: $action->execute(status: IAStatus::APPROVE));
    }

    public function show(ViewIARegistration $action, string $id): JsonResponse
    {
        return $this->success(data: $action->execute($id));
    }

    public function getDashboardDetails(GetDashboardDetailsAction $action)
    {
        return response()->json($action->execute());
    }
}
