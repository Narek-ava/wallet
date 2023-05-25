<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class ModifyPaymentFormsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {

        Schema::disableForeignKeyConstraints();

        Schema::table('payment_forms', function (Blueprint $table) {
            $table->uuid('card_provider_id')->nullable()->change();
        });

        Schema::enableForeignKeyConstraints();

    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::disableForeignKeyConstraints();

        Schema::table('payment_forms', function (Blueprint $table) {
            $table->uuid('card_provider_id')->nullable(false)->change();
        });

        Schema::enableForeignKeyConstraints();

    }
}
