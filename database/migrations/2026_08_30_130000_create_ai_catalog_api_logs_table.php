<?php

use App\Http\Api\V1\AiCatalog\AiCatalogConstants;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Records every AiCatalog API request/response (see
 * App\Http\Api\V1\AiCatalog\AiCatalogApiLogMiddleware) for debugging and
 * tracking — both successful and failed requests are logged.
 */
return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable(AiCatalogConstants::API_LOGS_TABLE)) {
            return;
        }

        Schema::create(AiCatalogConstants::API_LOGS_TABLE, function (Blueprint $table) {
            $table->char('id', 36)->primary();
            $table->string('endpoint', 150);
            $table->string('method', 10);
            $table->string('mobile_number', 10)->nullable();
            $table->string('udyam_registration_number', 20)->nullable();
            $table->unsignedSmallInteger('status_code');
            $table->boolean('is_success');
            $table->string('error_code', 100)->nullable();
            $table->text('error_message')->nullable();
            $table->longText('response_body')->nullable();
            $table->timestamps();

            $table->index('endpoint');
            $table->index('created_at');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists(AiCatalogConstants::API_LOGS_TABLE);
    }
};
