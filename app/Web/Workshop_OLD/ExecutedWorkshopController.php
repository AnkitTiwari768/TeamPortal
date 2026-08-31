<?php

declare(strict_types=1);

namespace App\Web\Workshop;

use Illuminate\View\View;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use App\Http\Controllers\ClientController;
use App\Web\Workshop\{WorkshopService, ListWorkshopAction, UpdateExecutedWorkshopRequest, UpdateExecutedWorkshopAction, UploadSupportingDocumentRequest};
use App\Traits\HasAttribute;
use App\Traits\HasGeolocation;


class ExecutedWorkshopController extends ClientController
{
    use HasAttribute, HasGeolocation;

    private const MODULE_URL = 'executed-workshop';
    private static string $module = 'executed-workshop';
    public function __construct(private WorkshopService $service) {}

    public function index(): View
    {
        $title = __('workshop.executed_workshop');

        return view('executed-workshops.index')->with(compact('title'));
    }

    public function getDataTable()
    {
        return $this->success(data: app(ListExecutedWorkshopAction::class)->execute());
    }


    public function editPage(string $id): View
    {
        $title = __('workshop.edit_executed_workshop');
        $module_url = self::MODULE_URL;
        $duration = $this->listOf(code: 'duration', skipParent: true);
        $workshopCategories = $this->listOf(code: 'workshop-categories', skipParent: true);
        $workshopModes = $this->listOf(code: 'admin-workshop-mode', skipParent: true);
        $workshopConductBy = $this->listOf(code: 'admin-workshop-conducted-by', skipParent: true);
        $workshopTargetAudience = $this->listOf(code: 'admin-workshop-target-audience', skipParent: true);
        $stateList = $this->getStates(skipNational: true);
        $eventForRoles = $this->service->getEventForRoles();
        $organizerNames = $this->service->getOrganizerNames();
        $branchOffices = $this->service->getNsicBranchOffices();
        $uploadedImage = $this->service->getUploadImageData($id);
        $row = (array) $this->service->getEventData($id);

        return view('executed-workshops.edit')
            ->with(
                compact(
                    'title',
                    'module_url',
                    'row',
                    'id',
                    'uploadedImage',
                    'duration',
                    'workshopCategories',
                    'workshopModes',
                    'workshopConductBy',
                    'workshopTargetAudience',
                    'eventForRoles',
                    'organizerNames',
                    'branchOffices',
                    'stateList'
                )
            );
    }

    public function updateEvent(UpdateExecutedWorkshopRequest $request, string $id, UpdateExecutedWorkshopAction $action)
    {
        return $this->updated(
            $action->execute($request->toDto(), $id)
        );
    }

    public function uploadSupportingDocument(UploadSupportingDocumentRequest $request)
    {
        $file = $request->file('file');
        $uuid = \App\Utils\UuidGenerator::uuid7();
        $fileSystemName = \App\Utils\UuidGenerator::uuid7() . '.' . $file->getClientOriginalExtension();
        $filePath = config('upload.supporting_document_path', 'uploads/supporting_documents');

        $fileUploadData = [
            'id' => $uuid,
            'file_name' => $file->getClientOriginalName(),
            'file_path' => $filePath,
            'file_system_name' => $fileSystemName,
            'file_extension' => $file->getClientOriginalExtension(),
            'file_type' => $file->getClientMimeType(),
            'file_size' => $file->getSize(),
            'created_at' => \Carbon\Carbon::now(),
            'created_by' => auth()->user()->id ?? null
        ];

        $file->storeAs($fileUploadData['file_path'], $fileUploadData['file_system_name']);
        \Illuminate\Support\Facades\DB::table('file_uploads')->insert($fileUploadData);

        $previewUrl = asset('storage/app/' . $filePath . '/' . $fileSystemName);

        return $this->success(
            message: 'Supporting document uploaded successfully.',
            data: [
                'id' => $uuid,
                'preview_url' => $previewUrl
            ]
        );
    }

    public function changeStatus(Request $request, string $id)
    {
        $validator = Validator::make($request->all(), [
            'status' => 'required|in:Completed,Cancelled',
            'remark' => 'nullable|string|max:1000',
        ]);

        if ($validator->fails()) {
            return $this->error($validator->errors(), $validator->errors()->first());
        }

        $updated = $this->service->updateStatus($id, $validator->validated());

        if ($updated) {
            return $this->success(message: __('workshop.status_updated_successfully'));
        }

        return $this->error([], __('workshop.status_update_failed'));
    }

    public function show($id)
    {
        $title = __('workshop.view_admin_workshop');
        $module_url = static::$module;

        $data = $this->service->getEcecutedWorkshopDetails($id);

        if (!$data) {
            return redirect()->back()->with('error', __('workshop.event_not_found'));
        }

        $event = $data['event'];
        $event_for = $data['event_for'];
        $uploadedImage = $data['uploadedImage'];
        $org_name = $data['org_name'];
        $workshopExpense = $data['workshopExpense']; // Added

        // Debug
        // dd($workshopExpense);

        if (hasRole('msme')) {
            $scheduleData = $this->service->getScheduleSummary($event->schedules);

            return view('workshop.card_detail_view', compact(
                'event',
                'title',
                'uploadedImage',
                'event_for',
                'scheduleData',
                'module_url',
                'org_name',
                'workshopExpense'
            ));
        } else {
            return view('executed-workshops.show', compact(
                'event',
                'title',
                'uploadedImage',
                'event_for',
                'module_url',
                'org_name',
                'workshopExpense'
            ));
        }
    }
}
