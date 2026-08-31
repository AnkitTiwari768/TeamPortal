<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Links a Role to the Role Type it was built from.
 *
 * The column is created with role_types.id's collation rather than the connection default:
 * roles.id is utf8mb4_bin while role_types.id is utf8mb4_unicode_ci, and MySQL refuses both
 * the foreign key and any later join when the two sides disagree.
 *
 * Nullable on purpose -- every role that already exists predates Role Types, and the Add
 * Role form must keep working on an installation that has no role types yet.
 */
return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasColumn('roles', 'role_type_id')) {
            return;
        }

        $collation = $this->collationOf('role_types', 'id') ?? 'utf8mb4_unicode_ci';
        $charset = explode('_', $collation)[0];

        DB::statement(
            "ALTER TABLE `roles`
             ADD COLUMN `role_type_id` CHAR(36) CHARACTER SET {$charset} COLLATE {$collation} NULL AFTER `department_id`,
             ADD KEY `roles_role_type_id_index` (`role_type_id`)"
        );

        DB::statement(
            'ALTER TABLE `roles`
             ADD CONSTRAINT `fk_roles_role_type` FOREIGN KEY (`role_type_id`)
             REFERENCES `role_types` (`id`) ON DELETE SET NULL ON UPDATE CASCADE'
        );
    }

    private function collationOf(string $table, string $column): ?string
    {
        return DB::table('information_schema.COLUMNS')
            ->where('TABLE_SCHEMA', DB::connection()->getDatabaseName())
            ->where('TABLE_NAME', $table)
            ->where('COLUMN_NAME', $column)
            ->value('COLLATION_NAME');
    }

    public function down(): void
    {
        if (! Schema::hasColumn('roles', 'role_type_id')) {
            return;
        }

        DB::statement('ALTER TABLE `roles` DROP FOREIGN KEY `fk_roles_role_type`');
        DB::statement('ALTER TABLE `roles` DROP COLUMN `role_type_id`');
    }
};
