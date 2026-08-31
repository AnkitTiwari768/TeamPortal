<?php

declare(strict_types=1);

namespace App\Web\ApplicationStatus;

use App\Http\Controllers\ClientController;
use Illuminate\Http\JsonResponse;
use Illuminate\View\View;

class StatusController extends ClientController
{
    public function __construct(private Service $service)
    {
    }

    public function page(string $slug): View
    {
        $tab = $this->service->getTabBySlug($slug);

        if (!$tab) {
            abort(404, 'Status module not found.');
        }

        return view('application_status.public_status', [
            'title' => $tab->label,
            'tab' => $tab,
        ]);
    }

    public function check(StatusRequest $request, string $tabKey): JsonResponse
    {
        $criteria = array_filter($request->except(['_token']), fn($v) => !empty($v));

        if (empty($criteria))
            return $this->error('Please provide at least one search criteria.');

        $result = $this->service->resolve(
            tabKey: $tabKey,
            criteria: $criteria,
            ip: $request->ip(),
            userAgent: $request->userAgent() ?? ''
        );

        if (!$result['status']) {
            return $this->error($result['message']);
        }

        return $this->success($result['data'], $result['data']['message'] ?? 'Record found.');
    }
}
