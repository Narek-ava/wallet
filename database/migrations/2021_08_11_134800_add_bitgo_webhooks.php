<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddBitgoWebhooks extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {

        Schema::table('crypto_account_details', function (Blueprint $table) {
            $table->boolean('has_webhook')->default(0);
        });

        $cryptoAccountDetails = \App\Models\CryptoAccountDetail::query()
            ->whereNotNull('wallet_id')
            ->where('wallet_id', '!=', '')->get();

        foreach ($cryptoAccountDetails as $cryptoAccountDetail) {
            /* @var \App\Models\CryptoAccountDetail $cryptoAccountDetail*/
            $cryptoAccountDetail->setupWebhook();
        }

    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('crypto_account_details', function (Blueprint $table) {
            $table->dropColumn('has_webhook');
        });
    }
}
