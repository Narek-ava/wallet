<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddNewFieldsToPaymentForms extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('payment_forms', function (Blueprint $table) {
            $table->string('website_url')->nullable()->after('rate_template_id');
            $table->string('description')->nullable()->after('website_url');
            $table->string('merchant_logo')->nullable()->after('description');
            $table->double('incoming_fee')->nullable()->after('merchant_logo');
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
            $table->dropColumn('website_url');
            $table->dropColumn('description');
            $table->dropColumn('merchant_logo');
            $table->dropColumn('incoming_fee');
        });
    }
}
