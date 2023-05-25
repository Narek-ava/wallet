<?php

use App\Models\ClientSystemWallet;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateClientSystemWalletsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('client_system_wallets', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('wallet_id')->nullable();
            $table->string('passphrase')->nullable();
            $table->string('currency')->unique();
            $table->timestamps();
        });

        foreach (\App\Enums\Currency::getList() as $currency) {
            $clientWallet = new ClientSystemWallet();
            $clientWallet->currency = $currency;
            $clientWallet->save();
        }
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('client_system_wallets');
    }
}
