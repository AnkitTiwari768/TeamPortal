<?php

declare(strict_types=1);

namespace App\Domain\UserLog;

use App\Http\Controllers\ClientController;
use Illuminate\View\View;

final class UserLogController extends ClientController
{
    public function __construct(private UserLogService $service)
    {
        $this->service = $service;
    }

    public function index(): View
    {
        guard(config('permissions.user-log-view'));

        return view('user-logs.index')
            ->with('title', __('User Activity Logs'))
            ->with('actionOptions', UserLogType::options());
    }

    public function datalist(): mixed
    {
        guard(config('permissions.user-log-view'));

        try {
            return $this->success($this->service->getLogs());
        } catch (\Throwable $e) {
            return $this->handleException($e);
        }
    }

    public function show(int $id): mixed
    {
        guard(config('permissions.user-log-view'));

        $log = $this->service->findById($id);

        if (! $log) {
            abort(404);
        }

        return view('user-logs.details')
            ->with('title', __('User Log Details'))
            ->with('log', $log);
    }
}
