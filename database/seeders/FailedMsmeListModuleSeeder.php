<?php

declare(strict_types=1);

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

/**
 * Registers the Failed MSME List screen in the menu (modules) and creates its
 * view permission, so it shows in the sidebar and passes acl().
 *
 * Idempotent — safe to re-run.
 *
 *   php artisan db:seed --class=Database\\Seeders\\FailedMsmeListModuleSeeder
 */
class FailedMsmeListModuleSeeder extends Seeder
{
    private const MODULE_SLUG = 'failed-msme-list';
    private const MODULE_URL  = 'failed-msme-list';

    /** Parent menu: "MSE" (slug: mse). */
    private const PARENT_SLUG = 'mse';

    public function run(): void
    {
        $parentId = DB::table('modules')->where('slug', self::PARENT_SLUG)->value('id');

        if (!$parentId) {
            $this->command?->error('Parent module "' . self::PARENT_SLUG . '" not found. Skipping.');

            return;
        }

        // Place it right after the existing MSE Bulk Registration entry.
        $sortOrder = (int) DB::table('modules')
            ->where('parent_id', $parentId)
            ->max('sort_order') + 1;

        $moduleId = DB::table('modules')->where('slug', self::MODULE_SLUG)->value('id');

        if ($moduleId) {
            DB::table('modules')
                ->where('id', $moduleId)
                ->update([
                    'name'       => 'Failed MSME List',
                    'url'        => self::MODULE_URL,
                    'parent_id'  => $parentId,
                    'status'     => 1,
                    'updated_at' => now(),
                ]);

            $this->command?->info('Module "Failed MSME List" updated.');
        } else {
            $moduleId = (string) Str::orderedUuid();

            // created_by is NOT NULL; attribute the row to the MSE parent's owner.
            $createdBy = DB::table('modules')->where('id', $parentId)->value('created_by');

            DB::table('modules')->insert([
                'id'          => $moduleId,
                'parent_id'   => $parentId,
                'module_type' => DB::table('modules')->where('id', $parentId)->value('module_type'),
                'name'        => 'Failed MSME List',
                'slug'        => self::MODULE_SLUG,
                'url'         => self::MODULE_URL,
                'sort_order'  => $sortOrder,
                'icon'        => 'alert-triangle',
                'type'        => 1,
                'description' => 'Bulk-upload drafts that failed Udyam verification',
                'status'      => 1,
                'created_at'  => now(),
                'created_by'  => $createdBy,
            ]);

            $this->command?->info('Module "Failed MSME List" created.');
        }

        $this->syncPermission($moduleId, 'Failed Msme View', 'failed-msme-view');
    }

    private function syncPermission(string $moduleId, string $name, string $slug): void
    {
        $exists = DB::table('permissions')->where('slug', $slug)->exists();

        if ($exists) {
            DB::table('permissions')
                ->where('slug', $slug)
                ->update([
                    'module_id' => $moduleId,
                    'name'      => $name,
                    'status'    => 1,
                ]);

            return;
        }

        DB::table('permissions')->insert([
            'id'          => (string) Str::orderedUuid(),
            'name'        => $name,
            'slug'        => $slug,
            'description' => $name,
            'module_id'   => $moduleId,
            'status'      => 1,
            'created_at'  => now(),
            'updated_at'  => now(),
        ]);
    }
}
