<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddLimitsColumnToWallesterAccountDetails extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('wallester_account_details', function (Blueprint $table) {
            $table->text('limits')->nullable()->after('cvv');
            $table->dropColumn('cvv');
            $table->dropColumn('expiry_date');
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
            $table->dropColumn('limits');
        });
    }
}
