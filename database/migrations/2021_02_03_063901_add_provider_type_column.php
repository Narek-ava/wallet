<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddProviderTypeColumn extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('payment_providers', function (Blueprint $table) {
            $table->integer('provider_type')->after('id');
            $table->integer('currency')->nullable()->after('type');
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
            $table->dropColumn('provider_type');
            $table->dropColumn('currency');
        });
    }
}
