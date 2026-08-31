<?php

declare(strict_types=1);

namespace App\Web\Workshop;

use Illuminate\View\View;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use App\Http\Controllers\ClientController;
use App\Web\Workshop\{WorkshopService, ListWorkshopAction};
use App\Traits\HasAttribute;
use App\Traits\HasGeolocation;


class WorkshopController extends ClientController
{
    use HasAttribute, HasGeolocation;

    private static string $module = 'proposed-workshop';
    private const MODULE_URL = 'Proposed Workshop';

    public function __construct(private WorkshopService $service) {}

    // public function index(): View
    // {      
    //     guard('workshop-management-view');
    //     $title = __('workshop.proposed_workshop');

    //     if(hasRole('msme')){
    //         $cardData = $this->service->cardData();
    //         // dd($cardData);
    //         return view('workshop.cardDetails', compact('title','cardData'));

    //     }else{

    //         return view('workshop.index', compact('title')); 
    //     }        
    // }

    public function index(): View
    {
        $title = __('workshop.proposed_workshop');

        if (hasRole('msme')) {
            $cardData = $this->service->cardData();
            return view('workshop.cardDetails')->with(compact('title', 'cardData'));
        }

        return view('workshop.index')->with(compact('title'));
    }

    public function getDataTable()
    {
        return $this->success(data: app(ListWorkshopAction::class)->execute());
    }

    public function createPage(): View
    {
        $title = __('workshop.add_proposed_workshop');
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

        $row = null;

        return view('workshop.form')
            ->with(
                compact(
                    'title',
                    'module_url',
                    'row',
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

    public function storeEvent(WorkshopRequest $request, CreateWorkshopAction $action)
    {
        return $this->created(
            $action->execute($request->toDto())
        );
    }

    public function editPage(string $id): View
    {
        $title = __('workshop.edit_proposed_workshop');
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

        return view('workshop.form')
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

    public function updateEvent(WorkshopRequest $request, string $id, CreateWorkshopAction $action)
    {
        return $this->updated(
            $action->execute($request->toDto(), $id)
        );
    }

    public function uploadEventImage(UploadWorkshopEventImageRequest $request, UploadWorkshopEventImageAction $action)
    {
        $result = $action->execute($request, $request->toDto());
        return $this->success(message: __('workshop.image_uploaded_success'), data: $result);
    }

    public function show($id)
    {
        $title = __('workshop.view_proposed_workshop');
        $module_url = static::$module;

        $data = $this->service->getWorkshopDetails($id);

        if (!$data) {
            return redirect()->back()->with('error', __('workshop.event_not_found'));
        }

        $event = $data['event'];
        $event_for = $data['event_for'];
        $uploadedImage = $data['uploadedImage'];
        $org_name = $data['org_name'];

        if (hasRole('msme')) {
            $scheduleData = $this->service->getScheduleSummary($event->schedules);
            return view('workshop.card_detail_view', compact('event', 'title', 'uploadedImage', 'event_for', 'scheduleData', 'module_url', 'org_name'));
        } else {
            return view('workshop.show', compact('event', 'title', 'uploadedImage', 'event_for', 'module_url', 'org_name'));
        }
    }
     public function deleteEvent(string $id, DeleteWorkshopAction $action)
    {
        $response = $action->execute($id);

        if ($response['status']) {
            return response()->json([
                'status' => true,
                'message' => $response['message'],
            ]);
        }

        return response()->json([
            'status' => false,
            'message' => $response['message'],
            'errors' => [],
        ], 422);
    }   
     public function getEventCards()
    {
        $cardData = $this->service->cardData();

        if ($cardData) {
            return response()->json([
                'status' => true,
                'message' => __('workshop.data_fetched_success')
            ], 200);
        } else {
            return response()->json([
                'status' => false,
                'message' => __('workshop.something_went_wrong')
            ], 200);
        }
    }

    public function checkDistrictWorkshop(Request $request)
    {
        //dd($request->all());
        $query = \DB::table('workshops')->where('district_id', $request->district_id);

        if (!empty($request->event_id)) {
            $query->where('id', '!=', $request->event_id);
        }

        $exists = $query->exists();

        return response()->json([
            'exists' => $exists
        ]);
    }
}
