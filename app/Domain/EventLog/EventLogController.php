<?php

declare(strict_types=1);

namespace App\Domain\EventLog;

use App\Http\Controllers\ClientController;
use Illuminate\Http\Request;
use Illuminate\View\View;

final class EventLogController extends ClientController
{
    public function __construct(private EventLogService $service)
    {
        $this->service = $service;
    }

    public function index(): View
    {
        // guard(config('permissions.event-log-view') ?? 'auth');
        return view('event-logs.index')
            ->with('title', __('Event Logs'));
    }

    public function datalist(): mixed
    {
        // guard(config('permissions.event-log-view') ?? 'auth');
        try {
            $result = $this->service->getLogs();
            return $this->success($result);
        } catch (\Throwable $e) {
            return $this->handleException($e);
        }
    }

    public function show(int $id): mixed
    {
        // guard(config('permissions.event-log-view') ?? 'auth');
        try {
            $log = $this->service->findById($id);
            if (!$log) {
                return $this->error([], __('Message.not_found'));
            }
            return $this->success($log);
        } catch (\Throwable $e) {
            return $this->handleException($e);
        }
    }

    public function destroy(int $id): mixed
    {
        // guard(config('permissions.event-log-delete') ?? 'auth');
        try {
            $this->service->delete($id);
            return $this->success([], __('Event Log deleted successfully.'));
        } catch (\Throwable $e) {
            return $this->handleException($e);
        }
    }
}
