<?php 

declare(strict_types=1);

namespace App\Domain\Batch;

use App\Web\Notification\SendNotificationEvent;
use Illuminate\Support\Facades\DB;

trait Notify
{
    public function notify(
        string $templateKey,
        int $type,
        ?string $toRoleSlug = null,
        ?string $toUserId = null, 
        ?array $message = null
    ): void
    {
        $fromUserId = authId();
        $fromRoleId = DB::table('user_roles')->where('user_id', $fromUserId)->value('role_id');
        $toRoleId = DB::table('roles')->where('slug', $toRoleSlug)->value('id');

        event(new SendNotificationEvent(
            templateKey: $templateKey,
            fromUserId: $fromUserId,
            formRole: $fromRoleId,
            toRole: $toRoleId,
            toUserId: $toUserId,
            message: $message,
            type: $type
        ));
    }

    protected function getUserNameById($userId): string
    {
        return DB::table('users')
            ->select(DB::raw("CONCAT_WS(' ', first_name, last_name) as name"))
            ->where('id', $userId)
            ->first()
            ->name;
    }
}
