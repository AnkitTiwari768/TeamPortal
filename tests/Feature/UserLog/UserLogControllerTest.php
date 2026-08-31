<?php

namespace Tests\Feature\UserLog;

use App\Domain\UserLog\UserLog;
use App\Models\User;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\Feature\Concerns\ActsWithPermissions;
use Tests\TestCase;

class UserLogControllerTest extends TestCase
{
    use DatabaseTransactions, ActsWithPermissions;

    private function baseParams(array $filterOverrides = [], array $overrides = []): array
    {
        return array_merge([
            'draw' => 1,
            'length' => 10,
            'page' => 1,
            'start' => 0,
            'order' => [['column' => 0, 'dir' => 'desc']],
            'search' => ['value' => ''],
            'filters' => array_merge([
                'filter_user' => '',
                'filter_admin' => '',
                'filter_action' => '',
                'filter_date_from' => '',
                'filter_date_to' => '',
            ], $filterOverrides),
        ], $overrides);
    }

    public function test_datalist_and_index_require_user_log_view_permission(): void
    {
        $this->actingAsUser([], 'administrator');

        $this->getJson('/user-logs/datalist?' . http_build_query($this->baseParams()))->assertForbidden();
        $this->get('/user-logs')->assertForbidden();
    }

    public function test_datalist_succeeds_with_permission(): void
    {
        $this->actingAsUser(['user-log-view'], 'administrator');

        $this->getJson('/user-logs/datalist?' . http_build_query($this->baseParams()))->assertOk();
    }

    public function test_datalist_filters_by_affected_user_performed_by_and_action(): void
    {
        $admin = $this->actingAsUser(['user-log-view'], 'administrator');
        $target = User::factory()->create(['first_name' => 'Findable', 'last_name' => 'Target']);
        $other = User::factory()->create(['first_name' => 'Unrelated', 'last_name' => 'Person']);

        UserLog::create([
            'user_id' => $target->id,
            'performed_by' => $admin->id,
            'action' => 'created',
            'description' => 'test entry',
            'new_values' => ['first_name' => 'Findable'],
            'created_at' => now(),
        ]);

        UserLog::create([
            'user_id' => $other->id,
            'performed_by' => $admin->id,
            'action' => 'updated',
            'description' => 'unrelated entry',
            'new_values' => ['first_name' => 'Unrelated'],
            'created_at' => now(),
        ]);

        $byUser = $this->getJson('/user-logs/datalist?' . http_build_query(
            $this->baseParams(['filter_user' => 'Findable'])
        ))->json('data.data');
        $this->assertCount(1, $byUser);
        $this->assertSame('Findable Target', $byUser[0]['user_name']);

        $byAdmin = $this->getJson('/user-logs/datalist?' . http_build_query(
            $this->baseParams(['filter_admin' => 'Findable'])
        ))->json('data.data');
        $this->assertCount(0, $byAdmin, 'Performed-by filter must not match the affected user\'s name.');

        $byAction = $this->getJson('/user-logs/datalist?' . http_build_query(
            $this->baseParams(['filter_action' => 'updated'])
        ))->json('data.data');
        $this->assertCount(1, $byAction);
        $this->assertSame('updated', $byAction[0]['action']);
    }

    public function test_datalist_filters_by_date_range(): void
    {
        $admin = $this->actingAsUser(['user-log-view'], 'administrator');
        $target = User::factory()->create();

        UserLog::create([
            'user_id' => $target->id,
            'performed_by' => $admin->id,
            'action' => 'created',
            'created_at' => '2020-01-01 10:00:00',
        ]);

        UserLog::create([
            'user_id' => $target->id,
            'performed_by' => $admin->id,
            'action' => 'updated',
            'created_at' => now(),
        ]);

        $rows = $this->getJson('/user-logs/datalist?' . http_build_query(
            $this->baseParams(['filter_date_from' => '01-01-2020', 'filter_date_to' => '01-01-2020'])
        ))->json('data.data');

        $this->assertCount(1, $rows);
        $this->assertSame('created', $rows[0]['action']);
    }

    public function test_datalist_is_paginated(): void
    {
        $admin = $this->actingAsUser(['user-log-view'], 'administrator');
        $target = User::factory()->create();

        for ($i = 0; $i < 15; $i++) {
            UserLog::create([
                'user_id' => $target->id,
                'performed_by' => $admin->id,
                'action' => 'updated',
                'created_at' => now(),
            ]);
        }

        $response = $this->getJson('/user-logs/datalist?' . http_build_query(
            $this->baseParams([], ['length' => 10, 'page' => 1])
        ));

        $response->assertOk();
        $this->assertSame(10, $response->json('data.per_page'));
        $this->assertGreaterThanOrEqual(15, $response->json('data.recordsTotal'));
        $this->assertSame(1, $response->json('data.current_page'));
        $this->assertCount(10, $response->json('data.data'));
    }
}
