<?php

declare(strict_types=1);

namespace App\Web\ApplicationStatus;

use App\Http\Controllers\ClientController;
use Illuminate\Http\Request;
use Illuminate\View\View;

class StatusTabController extends ClientController
{
    public function __construct(private Service $service)
    {
    }

    public function index(): View
    {
        return view('application_status.status_tab_index', [
            'title' => 'Tracking Status Tabs'
        ]);
    }

    public function datalist()
    {
        return $this->success($this->service->getTabsForDatalist());
    }

    public function create(): View
    {
        return view('application_status.status_tab_form', [
            'title' => 'Add Status Tab'
        ]);
    }

    public function store(Request $request)
    {
        // Simple code style: no validation logic in controller
        $this->service->saveModule($request->all());
        return $this->success(message: 'Status Tab created successfully.');
    }

    public function edit(string $id): View
    {
        $row = $this->service->findModule($id);
        return view('application_status.status_tab_form', [
            'title' => 'Edit Status Tab',
            'id'    => $id,
            'row'   => $row
        ]);
    }

    public function update(Request $request, string $id)
    {
        $this->service->saveModule($request->all(), $id);
        return $this->success(message: 'Status Tab updated successfully.');
    }

    public function delete(string $id)
    {
        $this->service->deleteModule($id);
        return $this->success(message: 'Status Tab deleted successfully.');
    }
}
