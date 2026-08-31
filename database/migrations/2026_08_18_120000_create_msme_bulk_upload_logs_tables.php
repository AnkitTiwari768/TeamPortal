<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Two tables: one row per cron/manual-upload run (team_msme_bulk_upload_logs), one row per
 * MSME/Udyam processed within that run (team_msme_bulk_upload_log_details, FK'd to the run).
 * Deliberately separate from team_msme_scheme_drafts so this logging can never affect the
 * existing draft/temp processing tables.
 *
 * created_by is char(36) to match users.id but carries no FK, mirroring user_logs (see that
 * migration for why: no FK on the closest existing log table, and users.id collation drift
 * makes a hard FK brittle). Collation is aligned below the same way.
 */
return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('team_msme_bulk_upload_logs')) {
            Schema::create('team_msme_bulk_upload_logs', function (Blueprint $table) {
                $table->char('id', 36)->primary();
                $table->string('type', 30)->comment('cron_snp,cron_ia,manual_upload');
                $table->tinyInteger('role_type')->nullable()->comment('1-snp,2-ia');
                $table->string('status', 20)->default('Pending')->comment('Pending,Migrated,Failed');

                $table->integer('total_processed')->default(0);
                $table->integer('success_count')->default(0);
                $table->integer('failed_count')->default(0);
                $table->integer('retry_count')->default(0);
                $table->integer('skipped_count')->default(0);
                $table->integer('reconciled_count')->default(0);
                $table->integer('pending_count')->nullable();

                $table->string('batch_id')->nullable()->comment('team_msme_scheme_drafts.batch_id for this run, when applicable');

                $table->string('file_name')->nullable();
                $table->unsignedBigInteger('file_size')->nullable()->comment('bytes');
                $table->string('file_extension', 20)->nullable();

                $table->text('error_message')->nullable();

                $table->char('created_by', 36)->nullable()->comment('null for cron runs');
                $table->timestamp('started_at')->nullable();
                $table->timestamp('completed_at')->nullable();
                $table->unsignedInteger('duration_ms')->nullable();
                $table->timestamp('created_at')->nullable();
                $table->timestamp('updated_at')->nullable();

                $table->index('type', 'idx_msme_bulk_logs_type');
                $table->index('status', 'idx_msme_bulk_logs_status');
                $table->index('role_type', 'idx_msme_bulk_logs_role_type');
                $table->index('batch_id', 'idx_msme_bulk_logs_batch_id');
                $table->index('created_by', 'idx_msme_bulk_logs_created_by');
                $table->index('created_at', 'idx_msme_bulk_logs_created_at');
                $table->index(['type', 'created_at'], 'idx_msme_bulk_logs_type_created');
            });
        }

        if (! Schema::hasTable('team_msme_bulk_upload_log_details')) {
            Schema::create('team_msme_bulk_upload_log_details', function (Blueprint $table) {
                $table->char('id', 36)->primary();
                $table->char('log_id', 36);

                $table->string('udyam_no', 24)->nullable();
                $table->string('mobile', 20)->nullable();
                $table->tinyInteger('role_type')->nullable()->comment('1-snp,2-ia');
                $table->string('status', 20)->comment('Pending,Migrated,Failed');

                $table->unsignedTinyInteger('attempt_number')->nullable();
                $table->json('dependency_details')->nullable()->comment('resolved product_category_id/current_state_business_id/ondc_transaction_type_id etc');
                $table->text('error_message')->nullable();

                $table->string('reference_table', 64)->nullable()->comment('team_msme_scheme_drafts or team_msme_scheme_temps');
                $table->char('reference_id', 36)->nullable();

                $table->timestamp('created_at')->nullable();

                $table->index('log_id', 'idx_msme_bulk_log_details_log_id');
                $table->index('udyam_no', 'idx_msme_bulk_log_details_udyam_no');
                $table->index('mobile', 'idx_msme_bulk_log_details_mobile');
                $table->index('status', 'idx_msme_bulk_log_details_status');
                $table->index(['log_id', 'status'], 'idx_msme_bulk_log_details_log_status');

                $table->foreign('log_id', 'fk_msme_bulk_log_details_log_id')
                    ->references('id')->on('team_msme_bulk_upload_logs')
                    ->onDelete('cascade');
            });
        }

        $this->alignUsersIdCollation();
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
        $current = $this->collationOf('team_msme_bulk_upload_logs', 'created_by');

        if (! $current || $target === $current) {
            return;
        }

        DB::statement(
            "ALTER TABLE `team_msme_bulk_upload_logs`
             MODIFY `created_by` CHAR(36) CHARACTER SET {$charset} COLLATE {$target} NULL"
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
        Schema::dropIfExists('team_msme_bulk_upload_log_details');
        Schema::dropIfExists('team_msme_bulk_upload_logs');
    }
};
