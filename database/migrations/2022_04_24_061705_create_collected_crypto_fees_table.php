<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateCollectedCryptoFeesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('collected_crypto_fees', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('currency');
            $table->decimal('amount', 20, 8);
            $table->string('wallet_id');
            $table->uuid('client_account_id');
            $table->uuid('system_account_id');
            $table->foreign('client_account_id')->references('id')->on('accounts')->cascadeOnDelete();
            $table->foreign('system_account_id')->references('id')->on('accounts')->cascadeOnDelete();
            $table->boolean('is_collected')->default(0);
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
        Schema::table('collected_crypto_fees', function (Blueprint $table) {
            $table->dropForeign('collected_crypto_fees_client_account_id_foreign');
            $table->dropForeign('collected_crypto_fees_provider_account_id_foreign');
        });
        Schema::dropIfExists('collected_crypto_fees');
    }
}
