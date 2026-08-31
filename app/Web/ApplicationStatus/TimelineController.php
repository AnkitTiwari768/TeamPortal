<?php

declare(strict_types=1);

namespace App\Web\ApplicationStatus;

use App\Http\Controllers\ClientController;
use Illuminate\Http\Request;

class TimelineController extends ClientController
{
    public function __construct(private Service $service)
    {
    }

    public function index(string $tabId)
    {
        $tab = $this->service->findModule($tabId);
        return view('application_status.timeline_index', [
            'title' => 'Timeline Stages',
            'tab' => $tab
        ]);
    }

    public function datalist(string $tabId)
    {
        return $this->success($this->service->getTimelinesForDatalist($tabId));
    }

    public function store(Request $request)
    {
        $this->service->saveTimeline($request->all());
        return $this->success(message: 'Stage created successfully.');
    }

    public function update(Request $request, string $id)
    {
        $this->service->saveTimeline($request->all(), $id);
        return $this->success(message: 'Stage updated successfully.');
    }

    public function delete(string $id)
    {
        $this->service->deleteTimeline($id);
        return $this->success(message: 'Stage deleted successfully.');
    }
}
