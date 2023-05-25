<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddWalletIdColumnToCryptoWebhooksTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('crypto_webhooks', function (Blueprint $table) {
            $table->string('wallet_id')->nullable()->after('crypto_account_detail_id');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('crypto_webhooks', function (Blueprint $table) {
            $table->dropColumn('wallet_id');
        });
    }
}
