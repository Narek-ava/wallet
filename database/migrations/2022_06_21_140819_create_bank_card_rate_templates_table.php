<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateBankCardRateTemplatesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('bank_card_rate_templates', function (Blueprint $table) {
            $table->uuid('id');
            $table->primary('id');
            $table->integer('status');
            $table->string('name')->unique();
            $table->string('overview_type');
            $table->integer('overview_fee');
            $table->string('transactions_type');
            $table->integer('transactions_fee');
            $table->string('fees_type');
            $table->integer('fees_fee');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('bank_card_rate_templates');
    }
}
