<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * user_id / performed_by are char(36) to match users.id, but deliberately carry no foreign
 * key: audit_trail.user_id (the closest existing log table) has none either, and a hard FK
 * onto users.id is brittle here (see the role_types migration for the same issue). Indexes
 * give the query performance without that risk.
 *
 * The read side still JOINs onto users (to resolve names), and MySQL refuses to compare two
 * char columns whose collations differ ("Illegal mix of collations") even without an FK, so
 * user_id/performed_by are realigned to users.id's actual collation below -- the same fix
 * the role_types migration applies to role_type_permissions.permission_id.
 */
return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('user_logs')) {
            Schema::create('user_logs', function (Blueprint $table) {
                $table->char('id', 36)->primary();
                $table->char('user_id', 36);
                $table->char('performed_by', 36)->nullable();
                $table->string('action', 50);
                $table->text('description')->nullable();
                $table->json('old_values')->nullable();
                $table->json('new_values')->nullable();
                $table->timestamp('created_at')->nullable();

                $table->index('user_id', 'idx_user_logs_user_id');
                $table->index('performed_by', 'idx_user_logs_performed_by');
                $table->index('action', 'idx_user_logs_action');
                $table->index('created_at', 'idx_user_logs_created_at');
                $table->index(['user_id', 'created_at'], 'idx_user_logs_user_created');
            });
        } else {
            $this->convertIdToUuid();
        }

        $this->alignUsersIdCollation();
    }

    /**
     * Covers an environment where user_logs already exists with the original bigint
     * auto-increment id (this table shipped that way briefly before switching to a UUID
     * primary key to match the rest of the app). Converts in place instead of dropping the
     * table, since it may already hold real log rows. Existing rows are backfilled with a
     * generated UUID so id always holds a UUID value afterward, per the current requirement.
     */
    private function convertIdToUuid(): void
    {
        if (DB::connection()->getDriverName() !== 'mysql') {
            return;
        }

        $dataType = DB::table('information_schema.COLUMNS')
            ->where('TABLE_SCHEMA', DB::connection()->getDatabaseName())
            ->where('TABLE_NAME', 'user_logs')
            ->where('COLUMN_NAME', 'id')
            ->value('DATA_TYPE');

        if ($dataType === null || $dataType === 'char') {
            return;
        }

        DB::statement('ALTER TABLE `user_logs` DROP PRIMARY KEY, MODIFY `id` BIGINT NOT NULL');
        DB::statement('ALTER TABLE `user_logs` MODIFY `id` CHAR(36) NOT NULL');
        DB::statement('UPDATE `user_logs` SET `id` = UUID() WHERE CHAR_LENGTH(`id`) <> 36');
        DB::statement('ALTER TABLE `user_logs` ADD PRIMARY KEY (`id`)');
    }

    private function alignUsersIdCollation(): void
    {
        if (DB::connection()->getDriverName() !== 'mysql') {
            return;
        }

        $target = $this->collationOf('users', 'id');

        if (! $target) {
            return;
        }

        $charset = explode('_', $target)[0];

        foreach (['user_id' => false, 'performed_by' => true] as $column => $nullable) {
            $current = $this->collationOf('user_logs', $column);

            if (! $current || $target === $current) {
                continue;
            }

            $null = $nullable ? 'NULL' : 'NOT NULL';
            DB::statement(
                "ALTER TABLE `user_logs`
                 MODIFY `{$column}` CHAR(36) CHARACTER SET {$charset} COLLATE {$target} {$null}"
            );
        }
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
        Schema::dropIfExists('user_logs');
    }
};
