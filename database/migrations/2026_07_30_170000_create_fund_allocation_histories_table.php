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
        Schema::create('fund_allocation_histories', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('fund_allocation_id');
            $table->string('financial_year');
            $table->uuid('duration_id')->nullable();
            $table->uuid('sub_duration_id')->nullable();
            $table->string('type')->default('create');
            $table->decimal('fresh_allocation_amount', 15, 2)->default(0);
            $table->decimal('carried_forward_amount', 15, 2)->default(0);
            $table->json('component_lines')->nullable();
            $table->decimal('total_amount_allocated_after', 15, 2)->default(0);
            $table->decimal('total_available_amount_after', 15, 2)->default(0);
            $table->string('sanction_order_number')->nullable();
            $table->date('sanction_order_date')->nullable();
            $table->string('document_path')->nullable();
            $table->text('remarks')->nullable();
            $table->uuid('created_by')->nullable();
            $table->timestamps();

            $table->foreign('fund_allocation_id')
                  ->references('id')
                  ->on('fund_allocations')
                  ->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('fund_allocation_histories');
    }
};
