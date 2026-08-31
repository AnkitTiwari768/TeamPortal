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
        Schema::create('component_utilization_mapping_details', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('mapping_id');
            $table->string('source_major_component')->nullable();
            $table->string('source_sub_component')->nullable();
            $table->decimal('allocated_amount', 15, 2)->default(0);
            $table->decimal('released_amount', 15, 2)->default(0);
            $table->decimal('remaining_balance', 15, 2)->default(0);
            $table->decimal('amount_to_be_allocated', 15, 2)->default(0);
            $table->string('target_category')->nullable();
            $table->string('target_sub_component')->nullable();
            $table->text('remarks')->nullable();
            $table->string('status')->default('Active');
            $table->uuid('created_by')->nullable();
            $table->uuid('updated_by')->nullable();
            $table->timestamps();

            $table->foreign('mapping_id')
                  ->references('id')
                  ->on('component_utilization_mappings')
                  ->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('component_utilization_mapping_details');
    }
};
