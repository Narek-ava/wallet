<?php

use App\Models\CryptoAccountDetail;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class UpdateExternalWalletsInCryptoAccountDetails extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        $cryptoAccountDetails = CryptoAccountDetail::whereHas('account', function($q) {
            $q->where('is_external', 1);
        })->get();

        foreach ($cryptoAccountDetails as $cryptoAccountDetail){
            $cryptoAccountDetail->update([
                'label' => $cryptoAccountDetail->address
            ]);
        }
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('crypto_account_tables', function (Blueprint $table) {
            //
        });
    }
}
