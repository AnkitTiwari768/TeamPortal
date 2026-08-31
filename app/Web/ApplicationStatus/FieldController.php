<?php

declare(strict_types=1);

namespace App\Web\ApplicationStatus;

use App\Http\Controllers\ClientController;
use Illuminate\Http\Request;

class FieldController extends ClientController
{
    public function __construct(private Service $service) {}

    public function index(string $tabId)
    {
        $tab = $this->service->findModule($tabId);
        return view('application_status.field_index', [
            'title' => 'Search Fields',
            'tab'   => $tab
        ]);
    }

    public function datalist(string $tabId)
    {
        return $this->success($this->service->getFieldsForDatalist($tabId));
    }

    public function store(Request $request)
    {
        $this->service->saveField($request->all());
        return $this->success(message: 'Field created successfully.');
    }

    public function update(Request $request, string $id)
    {
        $this->service->saveField($request->all(), $id);
        return $this->success(message: 'Field updated successfully.');
    }

    public function delete(string $id)
    {
        $this->service->deleteField($id);
        return $this->success(message: 'Field deleted successfully.');
    }
}
