<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class AddTimestampsToTransactionsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('transactions', function (Blueprint $table) {
            $table->timestamps();
            $table->index('tx_id');
            $table->index('ref_id');
        });
        DB::statement('UPDATE `transactions` SET `created_at` = `creation_date`,`updated_at`=`creation_date`');
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('transactions', function (Blueprint $table) {
            $table->dropColumn('created_at');
            $table->dropColumn('updated_at');
            $table->dropForeign('transactions_operation_id_foreign');
            $table->dropIndex('transactions_operation_id_foreign');
            $table->dropIndex('transactions_tx_id_index');
            $table->dropIndex('transactions_ref_id_index');
        });
    }
}
