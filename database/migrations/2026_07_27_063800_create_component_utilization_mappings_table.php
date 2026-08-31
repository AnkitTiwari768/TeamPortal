<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('component_utilization_mappings', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('financial_year');
            $table->string('duration')->nullable();
            $table->string('sub_duration')->nullable();
            $table->string('status')->default('Active');
            $table->uuid('created_by')->nullable();
            $table->uuid('updated_by')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('component_utilization_mappings');
    }
};
