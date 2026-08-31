<?php

namespace Tests\Feature\UserLog;

use App\Domain\User\ChangePasswordAction;
use App\Domain\User\CreateUserAction;
use App\Domain\User\CreateUserDTO;
use App\Domain\UserLog\UserLog;
use App\Http\Api\V1\User\SaveUserAction;
use App\Http\Api\V1\User\UserDTO;
use App\Models\User;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Str;
use Tests\Feature\Concerns\ActsWithPermissions;
use Tests\TestCase;

class UserLogServiceTest extends TestCase
{
    use DatabaseTransactions, ActsWithPermissions;

    public function test_user_creation_is_logged_with_actor_and_sanitized_values(): void
    {
        $admin = $this->actingAsUser(['user-create'], 'administrator');

        $email = 'jane.doe.' . Str::random(8) . '@example.test';

        app(CreateUserAction::class)->execute(CreateUserDTO::fromArray([
            'first_name' => 'Jane',
            'middle_name' => null,
            'last_name' => 'Doe',
            'email' => $email,
            'mobile' => '9876543210',
            'status' => 1,
            'roles' => null,
        ]));

        $createdUser = User::where('email', $email)->firstOrFail();

        $log = UserLog::where('user_id', $createdUser->id)->where('action', 'created')->first();

        $this->assertNotNull($log, 'Expected a "created" user log entry.');
        $this->assertSame($admin->id, $log->performed_by);
        $this->assertSame('Jane', $log->new_values['first_name'] ?? null);
        $this->assertNull($log->old_values);
        $this->assertArrayNotHasKey('password', $log->new_values);
    }

    public function test_update_logs_only_the_changed_fields_with_old_and_new_values(): void
    {
        $admin = $this->actingAsUser(['user-update'], 'administrator');
        $target = User::factory()->create(['first_name' => 'Old', 'last_name' => 'Name', 'mobile' => '1111111111']);
        $roleId = $this->createRole('Viewer', 'viewer-' . Str::random(6));
        $this->assignRole($target->id, $roleId);

        app(SaveUserAction::class)->execute(
            UserDTO::create([
                'first_name' => 'New',
                'last_name' => 'Name',
                'email' => $target->email,
                'mobile' => $target->mobile,
                'status' => 1,
                'roles' => [$roleId],
            ], $target->id),
            $target->id
        );

        $log = UserLog::where('user_id', $target->id)->where('action', 'updated')->first();

        $this->assertNotNull($log, 'Expected an "updated" user log entry.');
        $this->assertSame($admin->id, $log->performed_by);
        $this->assertSame('Old', $log->old_values['first_name'] ?? null);
        $this->assertSame('New', $log->new_values['first_name'] ?? null);
        // Unchanged fields must not appear in the diff.
        $this->assertArrayNotHasKey('mobile', $log->old_values);
        $this->assertArrayNotHasKey('mobile', $log->new_values);
        // Status is reported via a dedicated activated/deactivated entry, not here.
        $this->assertArrayNotHasKey('status', $log->new_values);
    }

    public function test_role_change_produces_a_dedicated_entry_with_resolved_role_names(): void
    {
        $this->actingAsUser(['user-update'], 'administrator');
        $target = User::factory()->create();
        $oldRoleId = $this->createRole('Viewer', 'viewer-' . Str::random(6));
        $newRoleId = $this->createRole('Editor', 'editor-' . Str::random(6));
        $this->assignRole($target->id, $oldRoleId);

        app(SaveUserAction::class)->execute(
            UserDTO::create([
                'first_name' => $target->first_name,
                'last_name' => $target->last_name,
                'email' => $target->email,
                'mobile' => $target->mobile,
                'status' => 1,
                'roles' => [$newRoleId],
            ], $target->id),
            $target->id
        );

        $log = UserLog::where('user_id', $target->id)->where('action', 'role_changed')->first();

        $this->assertNotNull($log, 'Expected a "role_changed" user log entry.');
        $this->assertSame(['Viewer'], $log->old_values['roles'] ?? null);
        $this->assertSame(['Editor'], $log->new_values['roles'] ?? null);
    }

    public function test_status_change_produces_activated_or_deactivated_not_a_generic_update(): void
    {
        $this->actingAsUser(['user-update'], 'administrator');
        $target = User::factory()->create(['status' => 1]);
        $roleId = $this->createRole('Viewer', 'viewer-' . Str::random(6));
        $this->assignRole($target->id, $roleId);

        app(SaveUserAction::class)->execute(
            UserDTO::create([
                'first_name' => $target->first_name,
                'last_name' => $target->last_name,
                'email' => $target->email,
                'mobile' => $target->mobile,
                'status' => 0,
                'roles' => [$roleId],
            ], $target->id),
            $target->id
        );

        $deactivated = UserLog::where('user_id', $target->id)->where('action', 'deactivated')->first();
        $genericUpdate = UserLog::where('user_id', $target->id)->where('action', 'updated')->first();

        $this->assertNotNull($deactivated, 'Expected a "deactivated" user log entry.');
        $this->assertSame(1, $deactivated->old_values['status'] ?? null);
        $this->assertSame(0, $deactivated->new_values['status'] ?? null);

        if ($genericUpdate) {
            $this->assertArrayNotHasKey('status', $genericUpdate->new_values ?? []);
        }
    }

    public function test_password_change_is_logged_without_storing_the_password(): void
    {
        $admin = $this->actingAsUser(['user-update'], 'administrator');
        $target = User::factory()->create();

        app(ChangePasswordAction::class)->execute($target->id, 'Sup3rSecret!New');

        $log = UserLog::where('user_id', $target->id)->where('action', 'password_changed')->first();

        $this->assertNotNull($log, 'Expected a "password_changed" user log entry.');
        $this->assertSame($admin->id, $log->performed_by);
        $this->assertNull($log->old_values);
        $this->assertNull($log->new_values);
        $this->assertStringNotContainsString('Sup3rSecret', (string) $log->description);
    }

    public function test_actor_and_target_are_distinguished_when_admin_edits_another_user(): void
    {
        $admin = $this->actingAsUser(['user-update'], 'administrator');
        $target = User::factory()->create();
        $roleId = $this->createRole('Viewer', 'viewer-' . Str::random(6));
        $this->assignRole($target->id, $roleId);

        app(SaveUserAction::class)->execute(
            UserDTO::create([
                'first_name' => 'Changed',
                'last_name' => $target->last_name,
                'email' => $target->email,
                'mobile' => $target->mobile,
                'status' => 1,
                'roles' => [$roleId],
            ], $target->id),
            $target->id
        );

        $log = UserLog::where('user_id', $target->id)->where('action', 'updated')->first();

        $this->assertNotNull($log);
        $this->assertSame($target->id, $log->user_id);
        $this->assertSame($admin->id, $log->performed_by);
        $this->assertNotSame($log->user_id, $log->performed_by);
    }

    public function test_sensitive_fields_are_never_persisted_in_old_or_new_values(): void
    {
        $this->actingAsUser(['user-create'], 'administrator');

        $email = 'secure.' . Str::random(8) . '@example.test';

        app(CreateUserAction::class)->execute(CreateUserDTO::fromArray([
            'first_name' => 'Secure',
            'middle_name' => null,
            'last_name' => 'User',
            'email' => $email,
            'mobile' => '9876500000',
            'status' => 1,
            'roles' => null,
        ]));

        $createdUser = User::where('email', $email)->firstOrFail();
        $log = UserLog::where('user_id', $createdUser->id)->where('action', 'created')->firstOrFail();

        foreach (array_keys($log->new_values ?? []) as $key) {
            $lower = strtolower($key);
            $this->assertStringNotContainsString('password', $lower);
            $this->assertStringNotContainsString('token', $lower);
            $this->assertStringNotContainsString('secret', $lower);
            $this->assertStringNotContainsString('otp', $lower);
        }
    }
}
