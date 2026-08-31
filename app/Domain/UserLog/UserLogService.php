<?php

declare(strict_types=1);

namespace App\Domain\UserLog;

use App\Traits\DataTable;
use Illuminate\Support\Facades\DB;

class UserLogService
{
    use DataTable;

    protected array $columns = [
        1 => 'user_logs.action',
        2 => 'target_user.first_name',
        3 => 'actor_user.first_name',
        4 => 'user_logs.created_at',
    ];

    private const SENSITIVE_NEEDLES = ['password', 'token', 'secret', 'otp'];

    private const DIFF_EXCLUDED_KEYS = ['id', 'created_at', 'created_by', 'updated_at', 'updated_by', 'status'];

    public function logCreated(string $userId, array $newValues, ?string $performedBy = null): UserLog
    {
        $newValues = $this->sanitize($newValues);
        $targetName = $this->resolveUserName($userId) ?? ($newValues['first_name'] ?? 'User');
        $actorName = $this->resolveUserName($performedBy) ?? 'System';

        return $this->write(
            $userId,
            UserLogType::Created,
            $performedBy,
            "{$targetName} was created by {$actorName}.",
            null,
            $newValues
        );
    }

    /**
     * Diffs $oldRow against $newRow over the keys present in $newRow (i.e. exactly the
     * fields the calling action set), excluding bookkeeping columns and 'status' (status
     * changes are logged separately via logStatusChanged as Activated/Deactivated).
     */
    public function logUpdated(string $userId, array $oldRow, array $newRow, ?string $performedBy = null): ?UserLog
    {
        [$oldChanged, $newChanged] = $this->diff($oldRow, $newRow);

        if (! $oldChanged && ! $newChanged) {
            return null;
        }

        $targetName = $this->resolveUserName($userId) ?? 'User';
        $actorName = $this->resolveUserName($performedBy) ?? 'System';

        return $this->write(
            $userId,
            UserLogType::Updated,
            $performedBy,
            "{$targetName}'s profile was updated by {$actorName}.",
            $oldChanged,
            $newChanged
        );
    }

    public function logStatusChanged(string $userId, $oldStatus, $newStatus, ?string $performedBy = null): ?UserLog
    {
        if ($oldStatus === null || $newStatus === null || (int) $oldStatus === (int) $newStatus) {
            return null;
        }

        $isActivating = (int) $newStatus === 1;
        $type = $isActivating ? UserLogType::Activated : UserLogType::Deactivated;
        $verb = $isActivating ? 'activated' : 'deactivated';

        $targetName = $this->resolveUserName($userId) ?? 'User';
        $actorName = $this->resolveUserName($performedBy) ?? 'System';

        return $this->write(
            $userId,
            $type,
            $performedBy,
            "{$targetName} was {$verb} by {$actorName}.",
            ['status' => (int) $oldStatus],
            ['status' => (int) $newStatus]
        );
    }

    public function logRoleChanged(string $userId, array $oldRoleIds, array $newRoleIds, ?string $performedBy = null): ?UserLog
    {
        $oldIds = array_values(array_unique(array_filter($oldRoleIds)));
        $newIds = array_values(array_unique(array_filter($newRoleIds)));

        sort($oldIds);
        sort($newIds);

        if ($oldIds === $newIds) {
            return null;
        }

        $allIds = array_values(array_unique(array_merge($oldIds, $newIds)));
        $names = $allIds ? DB::table('roles')->whereIn('id', $allIds)->pluck('name', 'id') : collect();

        $oldNames = array_values(array_filter(array_map(fn ($id) => $names[$id] ?? null, $oldIds)));
        $newNames = array_values(array_filter(array_map(fn ($id) => $names[$id] ?? null, $newIds)));

        $targetName = $this->resolveUserName($userId) ?? 'User';
        $actorName = $this->resolveUserName($performedBy) ?? 'System';

        return $this->write(
            $userId,
            UserLogType::RoleChanged,
            $performedBy,
            "{$targetName}'s role was changed from " . (implode(', ', $oldNames) ?: 'none')
                . ' to ' . (implode(', ', $newNames) ?: 'none') . " by {$actorName}.",
            ['roles' => $oldNames],
            ['roles' => $newNames]
        );
    }

    public function logPasswordChanged(string $userId, ?string $performedBy = null): UserLog
    {
        $targetName = $this->resolveUserName($userId) ?? 'User';
        $actorName = $this->resolveUserName($performedBy) ?? 'System';

        return $this->write(
            $userId,
            UserLogType::PasswordChanged,
            $performedBy,
            "{$targetName}'s password was changed by {$actorName}.",
            null,
            null
        );
    }

    private function write(string $userId, UserLogType $type, ?string $performedBy, string $description, ?array $oldValues, ?array $newValues): UserLog
    {
        return UserLog::create([
            'user_id' => $userId,
            'performed_by' => $performedBy,
            'action' => $type->value,
            'description' => $description,
            'old_values' => $oldValues,
            'new_values' => $newValues,
            'created_at' => now(),
        ]);
    }

    /**
     * @return array{0: array, 1: array} [changed old values, changed new values], keyed by field name
     */
    private function diff(array $oldRow, array $newRow): array
    {
        $oldChanged = [];
        $newChanged = [];

        foreach ($newRow as $key => $value) {
            if (in_array($key, self::DIFF_EXCLUDED_KEYS, true)) {
                continue;
            }

            $oldValue = $oldRow[$key] ?? null;

            if ((string) $oldValue === (string) $value) {
                continue;
            }

            $oldChanged[$key] = $oldValue;
            $newChanged[$key] = $value;
        }

        return [$this->sanitize($oldChanged), $this->sanitize($newChanged)];
    }

    /**
     * Strips any key whose lowercased name contains 'password', 'token', 'secret' or 'otp' —
     * covers password_confirmation/remember_token/api_token/etc. without a long denylist.
     * old_values/new_values must NEVER carry credentials.
     */
    private function sanitize(?array $values): ?array
    {
        if ($values === null) {
            return null;
        }

        foreach ($values as $key => $value) {
            $lower = strtolower((string) $key);
            foreach (self::SENSITIVE_NEEDLES as $needle) {
                if (str_contains($lower, $needle)) {
                    unset($values[$key]);
                    break;
                }
            }
        }

        return $values;
    }

    private function resolveUserName(?string $userId): ?string
    {
        if (! $userId) {
            return null;
        }

        $user = DB::table('users')->select('first_name', 'last_name')->where('id', $userId)->first();

        if (! $user) {
            return null;
        }

        $name = trim(($user->first_name ?? '') . ' ' . ($user->last_name ?? ''));

        return $name !== '' ? $name : null;
    }

    // ----- Read side (list + detail for the admin UI) -----

    public function getLogs()
    {
        [$limit, $order, $dir, $search, $page, $filters] = $this->getDataTableParams('user_logs');

        $filterUser = $filters['filter_user'] ?? null;
        $filterAdmin = $filters['filter_admin'] ?? null;
        $filterAction = $filters['filter_action'] ?? null;
        $dateFrom = $this->parseFilterDate($filters['filter_date_from'] ?? null);
        $dateTo = $this->parseFilterDate($filters['filter_date_to'] ?? null);

        $search = ($search && $this->escape_special_characters($search)) ? $search : null;

        $query = UserLog::query()
            ->select(
                'user_logs.*',
                'target_user.first_name as target_first_name',
                'target_user.last_name as target_last_name',
                'target_user.email as target_email',
                'actor_user.first_name as actor_first_name',
                'actor_user.last_name as actor_last_name',
                'actor_user.email as actor_email'
            )
            ->leftJoin('users as target_user', 'user_logs.user_id', '=', 'target_user.id')
            ->leftJoin('users as actor_user', 'user_logs.performed_by', '=', 'actor_user.id');

        if ($filterUser) {
            $query->where(function ($q) use ($filterUser) {
                $q->where('target_user.first_name', 'like', "%{$filterUser}%")
                    ->orWhere('target_user.last_name', 'like', "%{$filterUser}%")
                    ->orWhere('target_user.email', 'like', "%{$filterUser}%");
            });
        }

        if ($filterAdmin) {
            $query->where(function ($q) use ($filterAdmin) {
                $q->where('actor_user.first_name', 'like', "%{$filterAdmin}%")
                    ->orWhere('actor_user.last_name', 'like', "%{$filterAdmin}%")
                    ->orWhere('actor_user.email', 'like', "%{$filterAdmin}%");
            });
        }

        if ($filterAction) {
            $query->where('user_logs.action', $filterAction);
        }

        if ($dateFrom) {
            $query->whereDate('user_logs.created_at', '>=', $dateFrom);
        }

        if ($dateTo) {
            $query->whereDate('user_logs.created_at', '<=', $dateTo);
        }

        if ($search) {
            $query->where(function ($q) use ($search) {
                $q->where('user_logs.action', 'like', "%{$search}%")
                    ->orWhere('user_logs.description', 'like', "%{$search}%")
                    ->orWhere('target_user.first_name', 'like', "%{$search}%")
                    ->orWhere('target_user.last_name', 'like', "%{$search}%")
                    ->orWhere('target_user.email', 'like', "%{$search}%")
                    ->orWhere('actor_user.first_name', 'like', "%{$search}%")
                    ->orWhere('actor_user.last_name', 'like', "%{$search}%")
                    ->orWhere('actor_user.email', 'like', "%{$search}%");
            });
        }

        $query->orderBy($order, $dir);

        if ($page) {
            return $this->getDataTableResult(
                UserLogResource::collection($query->paginate($limit))
            );
        }

        return UserLogResource::collection($query->get());
    }

    public function findById(int $id): ?UserLog
    {
        return UserLog::with(['user', 'performedByUser'])->find($id);
    }

    private function parseFilterDate(?string $value): ?string
    {
        if (! $value) {
            return null;
        }

        $date = \DateTime::createFromFormat('d-m-Y', $value);

        return $date ? $date->format('Y-m-d') : null;
    }
}
