<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddVerifiedAtColumnInCryptoAccountDetailsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('crypto_account_details', function (Blueprint $table) {
            $table->timestamp('verified_at')->nullable()->after('is_hidden');
            $table->double('risk_score')->nullable()->after('verified_at');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('crypto_account_details', function (Blueprint $table) {
            $table->dropColumn('verified_at');
            $table->dropColumn('risk_score');
        });
    }
}
