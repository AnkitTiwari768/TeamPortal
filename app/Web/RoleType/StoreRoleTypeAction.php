<?php

declare(strict_types=1);

namespace App\Web\RoleType;

use Illuminate\Support\Facades\DB;

class StoreRoleTypeAction
{
    public function __construct(private SyncRoleTypePermissionsAction $syncRoleTypePermissions) {}

    /**
     * Creates or updates a role type and its permission set inside a single transaction, so
     * a failure while writing role_type_permissions also rolls back the role_types row.
     *
     * `$payload['permissions_submitted']` tells us the form actually rendered the permission
     * tree. Without it an unchecked-everything submit is indistinguishable from a submit by
     * a user who has no permission to see that section, and we would silently wipe the
     * existing grants.
     */
    public function execute(array $payload, ?string $roleTypeId = null): array
    {
        $syncPermissions = (bool) ($payload['permissions_submitted'] ?? false);
        $permissions = $payload['permissions'] ?? [];

        try {
            return DB::transaction(function () use ($payload, $roleTypeId, $syncPermissions, $permissions) {

                $roleTypeData = [
                    'name' => trim($payload['name']),
                    'slug' => $payload['slug'],
                    'status' => isset($payload['status']) && $payload['status'] !== null
                        ? (int) $payload['status']
                        : (int) config('constant.ACTIVE'),
                    'updated_at' => currentDateTime()
                ];

                if (! $roleTypeId) {
                    $roleTypeData['created_at'] = currentDateTime();

                    $roleType = RoleType::create($roleTypeData);
                    $roleTypeId = (string) $roleType->id;
                } else {
                    $roleType = RoleType::find($roleTypeId);

                    if (! $roleType) {
                        return [
                            'status' => false,
                            'error' => __('message.invalid_role_type')
                        ];
                    }

                    $roleType->fill($roleTypeData)->save();
                }

                if ($syncPermissions) {
                    $this->syncRoleTypePermissions->execute($roleTypeId, $permissions);
                }

                return [
                    'status' => true,
                    'data' => true
                ];
            });
        } catch (\Throwable $e) {
            \Log::error('Role type could not be saved: ' . $e->getMessage());

            return [
                'status' => false,
                'error' => __('message.role_type_save_failed')
            ];
        }
    }
}
