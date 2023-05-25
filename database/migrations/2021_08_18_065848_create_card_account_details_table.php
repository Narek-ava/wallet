<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateCardAccountDetailsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('card_account_details', function (Blueprint $table) {
            $table->uuid('id');
            $table->primary('id');
            $table->integer('type');
            $table->string('number', 16);
            $table->timestamp('verify_date')->nullable();
            $table->uuid('account_id');
            $table->double('risk_score');
            $table->timestamp('valid_until');
            $table->timestamps();

            $table->foreign('account_id')
                ->references('id')
                ->on('accounts');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('card_account_details');
    }
}
