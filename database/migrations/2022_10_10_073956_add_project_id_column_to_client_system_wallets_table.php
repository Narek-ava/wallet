<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddProjectIdColumnToClientSystemWalletsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('client_system_wallets', function (Blueprint $table) {
            $table->uuid('project_id')->nullable();
            $table->dropUnique('client_system_wallets_currency_unique');

            $table->foreign('project_id')->references('id')->on('projects')->nullOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('client_system_wallets', function (Blueprint $table) {
            $table->dropForeign('client_system_wallets_project_id_foreign');

            $table->dropColumn('project_id');
            $table->unique('currency');


        });
    }
}
