<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class ChangeColumnsInPaymentProvidersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('payment_providers', function (Blueprint $table) {
            $table->dropColumn('type');
            $table->integer('currency')->nullable()->change();
            $table->uuid('b_user_id')->nullable()->after('currency');
            $table->foreign('b_user_id')->references('id')->on('b_users')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('payment_providers', function (Blueprint $table) {
            $table->string('type')->after('status');
            $table->dropForeign('b_user_id');
            $table->dropColumn('b_user_id');
        });
    }
}
