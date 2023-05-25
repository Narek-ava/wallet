<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddOperationIdColumnToCollectedCryptoFeesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('collected_crypto_fees', function (Blueprint $table) {
            $table->uuid('operation_id')->nullable();

            $table->foreign('operation_id')->references('id')->on('operations')->nullOnDelete();
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
            $table->dropForeign('collected_crypto_fees_operation_id_foreign');

            $table->dropColumn('operation_id');
        });
    }
}
