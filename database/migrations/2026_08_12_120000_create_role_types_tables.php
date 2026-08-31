<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * The Role Type tables already exist on the primary database, so the create steps are
 * guarded by hasTable() and only provision fresh environments.
 *
 * The collation alignment below runs unconditionally: role_type_permissions was created as
 * utf8mb4_unicode_ci while permissions.id is utf8mb4_general_ci, and MySQL refuses to join
 * two char columns whose collations differ ("Illegal mix of collations"). role_type_id has
 * to keep role_types.id's collation because of the foreign key, so only permission_id is
 * realigned -- which is exactly how role_permissions.permission_id is already defined.
 */
return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('role_types')) {
            Schema::create('role_types', function (Blueprint $table) {
                $table->char('id', 36)->primary();
                $table->string('name');
                $table->string('slug');
                $table->boolean('status')->default(1);
                $table->timestamp('created_at')->nullable();
                $table->timestamp('updated_at')->nullable();

                $table->unique('name', 'uk_role_types_name');
                $table->unique('slug', 'uk_role_types_slug');
            });
        }

        if (! Schema::hasTable('role_type_permissions')) {
            Schema::create('role_type_permissions', function (Blueprint $table) {
                $table->char('role_type_id', 36);
                $table->char('permission_id', 36);

                $table->primary(['role_type_id', 'permission_id']);

                $table->foreign('role_type_id', 'fk_rtp_role_type')
                    ->references('id')
                    ->on('role_types')
                    ->onDelete('cascade')
                    ->onUpdate('cascade');
            });
        }

        $this->alignPermissionIdCollation();
    }

    private function alignPermissionIdCollation(): void
    {
        if (DB::connection()->getDriverName() !== 'mysql') {
            return;
        }

        $target = $this->collationOf('permissions', 'id');
        $current = $this->collationOf('role_type_permissions', 'permission_id');

        if (! $target || ! $current || $target === $current) {
            return;
        }

        $charset = explode('_', $target)[0];

        DB::statement(
            "ALTER TABLE `role_type_permissions`
             MODIFY `permission_id` CHAR(36) CHARACTER SET {$charset} COLLATE {$target} NOT NULL"
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
        Schema::dropIfExists('role_type_permissions');
        Schema::dropIfExists('role_types');
    }
};
