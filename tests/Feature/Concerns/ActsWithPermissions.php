<?php

namespace Tests\Feature\Concerns;

use App\Models\User;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\Str;

/**
 * This app's authorization is session-based (see guard()/acl()/hasRole() in
 * app/Helpers/Common.php), not guard/policy based, so tests must seed the session directly
 * rather than relying on a role/permission relation on the authenticated user.
 */
trait ActsWithPermissions
{
    /**
     * APP_URL in this environment includes a subfolder path (e.g. /projects/team_portal),
     * which a real web server strips via rewrite but the in-process test client does not --
     * it becomes part of the path the router tries to match, so every route 404s. Forcing a
     * plain root here fixes routing for the HTTP test client only; it is not the app's actual
     * public URL, so it has no bearing on requests handled by the real web server.
     */
    protected function setUpRootUrlForTesting(): void
    {
        URL::forceRootUrl('http://localhost');
    }

    protected function actingAsUser(array $permissions = [], ?string $activeRole = null, ?User $user = null): User
    {
        $this->setUpRootUrlForTesting();

        $user = $user ?? User::factory()->create();

        session([
            'permissions' => array_map('strtolower', $permissions),
            'active_role' => $activeRole,
        ]);

        $this->actingAs($user);

        return $user;
    }

    protected function createRole(string $name, ?string $slug = null): string
    {
        $roleId = (string) Str::orderedUuid();

        \DB::table('roles')->insert([
            'id' => $roleId,
            'name' => $name,
            'slug' => $slug ?? Str::slug($name),
            'status' => 1,
            'created_by' => (string) Str::orderedUuid(),
            'created_at' => now(),
        ]);

        return $roleId;
    }

    protected function assignRole(string $userId, string $roleId): void
    {
        \DB::table('user_roles')->insert([
            'user_id' => $userId,
            'role_id' => $roleId,
            'type' => 1,
        ]);
    }
}
