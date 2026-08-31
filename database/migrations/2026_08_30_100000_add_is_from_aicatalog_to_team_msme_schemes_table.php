<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * team_msme_schemes is a pre-existing table with no earlier migration in
 * this codebase (its schema lives only in the live database). This adds
 * one nullable-default column on top of it without touching anything else
 * on the table, so every existing INSERT/UPDATE into team_msme_schemes
 * that doesn't reference this column keeps working unchanged.
 */
return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('team_msme_schemes')) {
            return;
        }

        if (Schema::hasColumn('team_msme_schemes', 'is_from_aicatalog')) {
            return;
        }

        Schema::table('team_msme_schemes', function (Blueprint $table) {
            $table->boolean('is_from_aicatalog')->default(false);
        });
    }

    public function down(): void
    {
        if (! Schema::hasTable('team_msme_schemes') || ! Schema::hasColumn('team_msme_schemes', 'is_from_aicatalog')) {
            return;
        }

        Schema::table('team_msme_schemes', function (Blueprint $table) {
            $table->dropColumn('is_from_aicatalog');
        });
    }
};
