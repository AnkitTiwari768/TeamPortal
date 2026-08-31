<?php

declare(strict_types=1);

namespace App\Web\AdminWorkshopManagement;

use Illuminate\View\View;
use Illuminate\Http\Request;
use App\Http\Controllers\ClientController;
use App\Http\Api\V1\AdminWorkshop\AdminWorkshopService;
use App\Http\Api\V1\AdminWorkshop\AdminWorkshopRequest;
use App\Http\Api\V1\AdminWorkshop\AdminWorkshopDTO;
use App\Http\Api\V1\AdminWorkshop\UploadDocumentRequest;
use App\Http\Api\V1\AdminWorkshop\UploadDocumentAction;
use Illuminate\Support\Facades\DB;

class AdminWorkshopManagementController extends ClientController
{
    private static string $module = 'admin-workshop';

    public function __construct(private AdminWorkshopService $service)
    {
        $this->service = $service;
    }

    public function index(): View
    {
        $title = __('workshop.admin_workshop_management');
        return view('admin_workshops.index', compact('title'))->with('lists', (object) $this->service->getDropdownList());
    }


    public function getDataTable(Request $request)
    {
        $filters = $request->only(['from_date', 'to_date', 'state_id']);
        return $this->success($this->service->getList($filters));
    }


    public function createPage(): View
    {
        $title          = __('workshop.add_admin_workshop');
        $module_url     = static::$module;
        $venue          = $this->service->getAttributesValue('admin-workshop-venue');
        $organizer      = $this->service->getAttributesValue('admin-workshop-organizer-name');
        $conductedBy    = $this->service->getAttributesValue('admin-workshop-conducted-by');
        $mode           = $this->service->getAttributesValue('admin-workshop-mode');
        $target_audience = $this->service->getAttributesValue('admin-workshop-target-audience');
        $duration = $this->service->getAttributesValue('duration');
        $branch_office = $this->service->getBranchOfficeName();
        $row            = null;
        return view('admin_workshops.create', compact('title', 'module_url', 'venue', 'organizer', 'conductedBy', 'mode', 'target_audience', 'row','duration','branch_office'));
    }


    public function store(AdminWorkshopRequest $request)
    {
        //dd($request->all());
        $dto    = AdminWorkshopDTO::fromRequest($request);
        $result = $this->service->store($dto);
        return $this->success(message: __('workshop.admin_workshop_created_success'),data: $result);
    }

    public function editPage(string $id): View
    {
        $title       = __('workshop.edit_admin_workshop');
        $module_url  = static::$module;
        $row         = $this->service->getById($id);

        if (!$row) {
            abort(404, __('workshop.workshop_not_found'));
        }

        $venue           = $this->service->getAttributesValue('admin-workshop-venue');
        $organizer       = $this->service->getAttributesValue('admin-workshop-organizer-name');
        $conductedBy     = $this->service->getAttributesValue('admin-workshop-conducted-by');
        $mode            = $this->service->getAttributesValue('admin-workshop-mode');
        $target_audience = $this->service->getAttributesValue('admin-workshop-target-audience');
        $duration = $this->service->getAttributesValue('duration');
        $uploadedImage = [];
        $subDuration = [];
        $branch_office = $this->service->getBranchOfficeName();

        if (!empty($row->duration)) {
            $subDuration = $this->service->getSubDurationByParent($row->duration);
        }

        if (!empty($row->uploaded_ids)) {
            $fileIds = array_filter(explode(',', $row->uploaded_ids));

            $uploadedImage = DB::table('file_uploads')
                ->whereIn('id', $fileIds)
                ->select('id', 'file_name', 'file_system_name', 'file_path')
                ->get();
        }
        // dd($row);
        return view('admin_workshops.create', compact('title','branch_office', 'subDuration','module_url', 'row', 'id', 'venue', 'organizer', 'conductedBy', 'mode', 'target_audience','duration','uploadedImage'
        ));
    }


    public function update(AdminWorkshopRequest $request, string $id)
    {
        $workshop = $this->service->getById($id);

        if (!$workshop) {
            return $this->error(['message' => __('workshop.workshop_not_found')], 404);
        }

        $dto    = AdminWorkshopDTO::fromRequest($request);
        $result = $this->service->updateAdminWorkshop($id, $dto);

        return $this->success(message: __('workshop.admin_workshop_updated_success'),data: $result);
    }

    public function show(string $id): view
    {
        $title      = __('workshop.view_admin_workshop');
        $module_url = static::$module;
        $row   = $this->service->getByIdshow($id);
        // dd($row);

        if (!$row) {
            return redirect()->back()->with('error', __('workshop.workshop_not_found'));
        }
         // ✅ Uploaded documents fetch
        $uploadedFiles = collect();

        if (!empty($row->uploaded_ids)) {

            $fileIds = array_filter(explode(',', $row->uploaded_ids));
            $uploadedFiles = DB::table('file_uploads') 
                ->whereIn('id', $fileIds)
                ->select(
                    'id',
                    'file_name',
                    'file_system_name',
                    'file_path'
                )
                ->get();
        }
        return view('admin_workshops.view', compact('title', 'module_url', 'row', 'uploadedFiles'));
    }


    public function destroy(string $id)
    {
        $workshop = $this->service->getById($id);

        if (!$workshop) {
            return response()->json(['success' => false, 'message' => __('workshop.workshop_not_found')], 404);
        }

        if (!empty($workshop->uploaded_ids)) {
            $fileIds = array_filter(explode(',', $workshop->uploaded_ids));
            \DB::table('file_uploads')->whereIn('id', $fileIds)->delete();
        }

        \DB::table('admin_workshop_expenses')->where('admin_workshop_id', $id)->delete();
        \DB::table('admin_workshop_schedules')->where('admin_workshop_id', $id)->delete();
        \DB::table('admin_workshops')->where('id', $id)->delete();

        return response()->json(['success' => true, 'message' => __('workshop.admin_workshop_deleted_success')], 200);
    }


    public function uploadDocument(UploadDocumentRequest $request, UploadDocumentAction $action)
    {
        $result = $action->execute($request, $request->toDto());
        return $this->success(message: __('workshop.file_uploaded_success'), data: $result);
    }
    public function getSubDuration(string $id)
    {
        $data = $this->service->getSubDurationByParent($id);
        return response()->json($data);
    }


     public function checkDistrictWorkshop(Request $request)
    {
        //dd($request->all());
        $query = \DB::table('admin_workshops')->where('district_id', $request->district_id);

        if (!empty($request->event_id)) {
            $query->where('id', '!=', $request->event_id);
        }

        $exists = $query->exists();

        return response()->json([
            'exists' => $exists
        ]);
    }
}