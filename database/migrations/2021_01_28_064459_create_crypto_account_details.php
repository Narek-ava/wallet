<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateCryptoAccountDetails extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('crypto_account_details', function (Blueprint $table) {
            $table->uuid('id');
            $table->string('coin');
            $table->string('label');
            $table->string('passphrase');
            $table->string('address');
            $table->string('wallet_id');
            $table->uuid('account_id');
            $table->json('wallet_data');
            $table->integer('is_hidden')->default(0);
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
        Schema::dropIfExists('crypto_account_details');
    }
}
