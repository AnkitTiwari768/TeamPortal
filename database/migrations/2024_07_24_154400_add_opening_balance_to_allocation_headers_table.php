<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('allocation_headers', function (Blueprint $table) {
            if (!Schema::hasColumn('allocation_headers', 'opening_balance')) {
                $table->decimal('opening_balance', 15, 2)->default(0.00)->after('sub_duration_id');
            }
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('allocation_headers', function (Blueprint $table) {
            if (Schema::hasColumn('allocation_headers', 'opening_balance')) {
                $table->dropColumn('opening_balance');
            }
        });
    }
};
