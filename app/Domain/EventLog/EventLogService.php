<?php

declare(strict_types=1);

namespace App\Domain\EventLog;

use App\Http\Services\ApiService;
use App\Domain\EventLog\EventLog;
use Illuminate\Support\Facades\DB;

class EventLogService extends ApiService
{
    protected array $columns = [
        1 => 'event_logs.event_name',
        2 => 'event_logs.executed_at',
    ];

    public function getLogs()
    {
        [$limit, $order, $dir, $search, $page, $filters] = $this->getDataTableParams('event_logs');

        $eventName = is_array($filters) ? ($filters['filter_event_name'] ?? $filters['event_name'] ?? null) : null;
        $search = ($search && $this->escape_special_characters($search)) ? $search : null;

        $query = EventLog::select('id', 'event_name', 'executed_at');

        if ($eventName) {
            $query->where('event_logs.event_name', 'like', '%' . $eventName . '%');
        }

        if ($search) {
            $query->where(function ($q) use ($search) {
                $q->where('event_logs.event_name', 'like', '%' . $search . '%')
                    ->orWhere(DB::raw('DATE_FORMAT(event_logs.executed_at, "%d-%m-%Y %H:%i:%s")'), 'like', '%' . $search . '%');
            });
        }

        $query->orderBy($order, $dir);

        if ($page) {
            return $this->getDataTableResult(
                EventLogResource::collection($query->paginate($limit))
            );
        }

        return EventLogResource::collection($query->get());
    }

    public function findById(int $id): ?EventLog
    {
        return EventLog::with('creator')->find($id);
    }

    public function delete(int $id): bool
    {
        $log = EventLog::find($id);
        if ($log) {
            return $log->delete();
        }
        return false;
    }
}
