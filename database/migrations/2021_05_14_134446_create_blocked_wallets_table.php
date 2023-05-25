<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateBlockedWalletsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('crypto_account_details', function (Blueprint $table) {
            $table->uuid('id')->primary()->change();
        });

        Schema::create('blocked_wallets', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->text('reason');
            $table->string('file')->nullable()->default(null);
            $table->uuid('operation_id')->nullable();
            $table->foreign('operation_id')->references('id')->on('operations');
            $table->uuid('wallet_id');
            $table->foreign('wallet_id')->references('id')->on('crypto_account_details');
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
        Schema::dropIfExists('blocked_wallets');
    }
}
