<?php

namespace App\Web\ApplicationStatus;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Http\JsonResponse;
use Illuminate\View\View;
use App\Http\Controllers\ClientController;
use App\Domain\NetworkProvider\NetworkProviderStatus;

class ApplicationStatusController extends ClientController
{
  
    public function msmePage(): View
    {
        return view('tracking.msme_status', [
            'title' => 'MSME Application Status'
        ]);
    }

    public function msmeCheck(Request $request): JsonResponse
    {
        $input = $request->input('udyam_no_or_mobile');

        if (empty($input)) {
            return response()->json([
                'status' => false,
                'message' => 'Please enter Udyam Number / Mobile Number.'
            ]);
        }

        if (is_numeric($input)) {
            if (strlen($input) !== 10) {
                return response()->json([
                    'status' => false,
                    'message' => 'Mobile number must be exactly 10 digits.'
                ]);
            }
        } else {
            if (!preg_match('/^UDYAM-[A-Z]{2}-\d{2}-\d{7}$/i', $input) && !preg_match('/^\d+$/', $input)) {
            }
        }

        $record = DB::table('team_msme_schemes')
            ->where('udyam_no', $input)
            ->orWhere('mobile', $input)
            ->orderByDesc('created_at')
            ->first();

        if (!$record) {
            return response()->json([
                'status' => false,
                'message' => 'No registration found for the provided details.'
            ]);
        }

        /*
        $statusKey = 'pending';
        $message = 'Your MSME application is currently under process.';
        $badgeClass = 'bg-info';
        $badgeIcon = 'fa-info-circle';

        if (isset($record->status)) {
            if ($record->status == 1 || $record->status == 3) {
                $statusKey = 'approve';
                $message = 'Congratulations! Your MSE is registered on TEAM Portal.';
                $badgeClass = 'bg-success';
                $badgeIcon = 'fa-check-circle';
            } elseif ($record->status == 4) {
                $statusKey = 'reject';
                $message = 'Your MSME application has been rejected.';
                $badgeClass = 'bg-danger';
                $badgeIcon = 'fa-times-circle';
            } elseif ($record->status == 5) {
                $statusKey = 'revert';
                $message = 'Your MSME application has been reverted for clarification.';
                $badgeClass = 'bg-warning text-dark';
                $badgeIcon = 'fa-exclamation-triangle';
            }
        }

        $timeline = [
            ['label' => 'Submitted', 'completed' => true, 'current' => false, 'color' => 'success'],
            ['label' => 'Under Review', 'completed' => ($statusKey !== 'pending'), 'current' => ($statusKey === 'pending'), 'color' => 'info'],
            ['label' => 'Processed', 'completed' => in_array($statusKey, ['approve', 'reject']), 'current' => in_array($statusKey, ['approve', 'reject', 'revert']), 'color' => $badgeClass]
        ];

        return response()->json([
            'status' => true,
            'data' => [
                'message' => $message,
                'badge_class' => $badgeClass,
                'badge_icon' => $badgeIcon,
                'display_data' => [
                    ['label' => 'MSE Name', 'value' => $record->msme_name ?? '—'],
                    ['label' => 'Udyam Number', 'value' => $record->udyam_no ?? '—'],
                    ['label' => 'Mobile Number', 'value' => $record->mobile ?? '—'],
                    ['label' => 'Applied Date', 'value' => $record->applied_date ? date('d-M-Y', strtotime($record->applied_date)) : '—'],
                ],
                'timeline' => $timeline,
                'tab' => [
                    'is_timeline_enabled' => true,
                ]
            ]
        ]);
        */

        return response()->json([
            'status' => true,
            'message' => 'This MSE is already registered on TEAM Portal.',
            'data' => [
                'message' => 'This MSE is already registered on TEAM Portal.',
                'badge_class' => 'bg-success',
                'badge_icon' => 'fa-check-circle'
            ]
        ]);
    }

    public function npPage(): View
    {
        return view('tracking.np_status', [
            'title' => 'NP Application Status'
        ]);
    }

    public function npCheck(Request $request): JsonResponse
    {
        $input = trim($request->input('email_or_mobile'));


        if ($input == '') {
            return response()->json([
                'status'  => false,
                'message' => 'Please enter Email / Mobile Number.'
            ]);
        }


        if (is_numeric($input)) {

            if (strlen($input) != 10) {
                return response()->json([
                    'status'  => false,
                    'message' => 'Please enter valid 10 digit mobile number.'
                ]);
            }

        } else {

            if (!filter_var($input, FILTER_VALIDATE_EMAIL)) {
                return response()->json([
                    'status'  => false,
                    'message' => 'Please enter valid Email ID.'
                ]);
            }
        }

        $record = DB::table('network_providers as np')
            ->leftJoin('team_snp_scheme as tss', 'tss.network_provider_id', '=', 'np.id')
            ->leftJoin('users as u', 'u.id', '=', 'np.user_id')
            ->select(
                'np.id',
                'np.organization_name',
                'np.email as np_email',
                'np.status as np_status',
                'np.contact_details',

                'tss.status as tss_status',
                'tss.snp_id',

                'u.email as user_email',
                'u.mobile as user_mobile'
            )
            ->where(function ($q) use ($input) {
                $q->where('np.email', $input)
                ->orWhere('u.email', $input)
                ->orWhere('u.mobile', $input)
                ->orWhere('np.contact_details->primary_contact_no', $input);
            })
            ->orderByDesc('np.created_at')
            ->first();

        if (!$record) {
            return response()->json([
                'status'  => false,
                'message' => 'No application found for the provided details.'
            ]);
        }


        if ($record->np_status !== null && $record->np_status !== '') {

            $dbStatus = (int) $record->np_status;

        } elseif ($record->tss_status !== null && $record->tss_status !== '') {

            $dbStatus = (int) $record->tss_status;

        } else {

            $dbStatus = NetworkProviderStatus::PENDING->value;
        }

        $message    = 'Your registration verification is in progress. Kindly wait for some time.';
        $badgeClass = 'bg-info';
        $badgeIcon  = 'fa-info-circle';


        if ($dbStatus == NetworkProviderStatus::APPROVE->value) {

            $message    = 'You are already registered on TEAM Portal.';
            $badgeClass = 'bg-success';
            $badgeIcon  = 'fa-check-circle';

        } elseif ($dbStatus == NetworkProviderStatus::REJECT->value) {

            $message    = 'Your application has been rejected.';
            $badgeClass = 'bg-danger';
            $badgeIcon  = 'fa-times-circle';

        } elseif ($dbStatus == NetworkProviderStatus::REVERT->value) {

            $message    = 'Your application requires correction / resubmission.';
            $badgeClass = 'bg-warning text-dark';
            $badgeIcon  = 'fa-exclamation-triangle';
        }

        $timeline = [
            [
                'label'     => 'Submitted',
                'completed' => true,
                'current'   => false,
                'color'     => 'success'
            ]
        ];

        if ($dbStatus == NetworkProviderStatus::PENDING->value) {

            $timeline[] = [
                'label'     => 'Pending Verification',
                'completed' => false,
                'current'   => true,
                'color'     => 'info'
            ];

        } elseif ($dbStatus == NetworkProviderStatus::APPROVE->value) {

            $timeline[] = [
                'label'     => 'Verification Completed',
                'completed' => true,
                'current'   => false,
                'color'     => 'success'
            ];

            $timeline[] = [
                'label'     => 'Registered on TEAM Portal',
                'completed' => true,
                'current'   => true,
                'color'     => 'success'
            ];

        } elseif ($dbStatus == NetworkProviderStatus::REJECT->value) {

            $timeline[] = [
                'label'     => 'Under Review',
                'completed' => true,
                'current'   => false,
                'color'     => 'info'
            ];

            $timeline[] = [
                'label'     => 'Rejected',
                'completed' => true,
                'current'   => true,
                'color'     => 'danger'
            ];

        } elseif ($dbStatus == NetworkProviderStatus::REVERT->value) {

            $timeline[] = [
                'label'     => 'Under Review',
                'completed' => true,
                'current'   => false,
                'color'     => 'info'
            ];

            $timeline[] = [
                'label'     => 'Reverted for Correction',
                'completed' => true,
                'current'   => true,
                'color'     => 'warning'
            ];
        }

        return response()->json([
            'status' => true,
            'data'   => [
                'message' => $message,
                'badge_class' => $badgeClass,
                'badge_icon' => $badgeIcon,

                'display_data' => [],
                'hide_data_section' => true,

                'timeline' => $timeline,

                'tab' => [
                    'is_timeline_enabled' => true
                ]
            ]
        ]);
    }

}
