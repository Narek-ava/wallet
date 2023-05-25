<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddMerchantIdFieldToPaymentFormsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('payment_forms', function (Blueprint $table) {
            $table->uuid('c_profile_id')->after('status');
            $table->foreign('c_profile_id')->references('id')->on('c_profiles')->cascadeOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('payment_forms', function (Blueprint $table) {
            $table->dropForeign('payment_forms_c_profile_id_foreign');
            $table->dropColumn('c_profile_id');
        });
    }
}
