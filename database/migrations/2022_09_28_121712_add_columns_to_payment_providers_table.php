<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddColumnsToPaymentProvidersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('payment_providers', function (Blueprint $table) {
            $table->decimal('plastic_card_amount')->nullable();
            $table->decimal('virtual_card_amount')->nullable();
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
            $table->dropColumn('plastic_card_amount');
            $table->dropColumn('virtual_card_amount');
        });
    }
}
