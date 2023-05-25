<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddProviderAccountIdToOperation extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('operations', function (Blueprint $table) {
            $table->renameColumn('provider_account_id', 'payment_provider_id');
        });

        Schema::table('operations', function (Blueprint $table) {
            $table->uuid('provider_account_id')->nullable();
            $table->foreign('payment_provider_id')->references('id')->on('payment_providers')->onDelete('SET NULL');
        });



    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('operation', function (Blueprint $table) {
            //
        });
    }
}
