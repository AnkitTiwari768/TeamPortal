<?php

declare(strict_types=1);

namespace App\Web\ApplicationStatus;

use App\Http\Controllers\ClientController;

class LogController extends ClientController
{
    public function __construct(private Service $service) {}

    public function index()
    {
        // guard('tracking-management-view');
        return view('application_status.log_index', ['title' => 'Tracking Logs']);
    }

    public function datalist()
    {
        // guard('tracking-management-view');
        return $this->success($this->service->getLogsForDatalist());
    }
}
