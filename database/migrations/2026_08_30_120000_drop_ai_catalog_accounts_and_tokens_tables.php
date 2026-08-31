<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\Schema;

/**
 * ai_catalog_accounts and ai_catalog_tokens are no longer used — the
 * AiCatalog flow is based entirely on team_msme_schemes and its
 * is_from_aicatalog flag. down() recreates both tables (same shape as the
 * migrations this replaces) only as an emergency rollback path; nothing
 * in the application writes to them once this has run.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::dropIfExists('ai_catalog_tokens');
        Schema::dropIfExists('ai_catalog_accounts');
    }

    public function down(): void
    {
        if (! Schema::hasTable('ai_catalog_accounts')) {
            Schema::create('ai_catalog_accounts', function (\Illuminate\Database\Schema\Blueprint $table) {
                $table->char('id', 36)->primary();
                $table->string('mobile_number', 10)->unique();
                $table->string('udyam_registration_number', 20)->unique();
                $table->unsignedTinyInteger('failed_login_attempts')->default(0);
                $table->timestamp('locked_until')->nullable();
                $table->timestamps();
            });
        }

        if (! Schema::hasTable('ai_catalog_tokens')) {
            Schema::create('ai_catalog_tokens', function (\Illuminate\Database\Schema\Blueprint $table) {
                $table->char('id', 36)->primary();
                $table->char('account_id', 36);
                $table->timestamp('issued_at');
                $table->timestamp('expires_at');
                $table->timestamp('revoked_at')->nullable();
                $table->timestamps();

                $table->index('account_id');
                $table->index('expires_at');
            });
        }
    }
};
