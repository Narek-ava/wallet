<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddIsPaidColumnToWallesterAccountDetailsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('wallester_account_details', function (Blueprint $table) {
            $table->boolean('is_paid')->default(false);
            $table->uuid('operation_id')->nullable();

            $table->foreign('operation_id')->references('id')->on('operations')->nullOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('wallester_account_details', function (Blueprint $table) {
            $table->dropColumn('is_paid');
            $table->dropForeign('wallester_account_details_operation_id_foreign');
            $table->dropColumn('operation_id');
        });
    }
}
