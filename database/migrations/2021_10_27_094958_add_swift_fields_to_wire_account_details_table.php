<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddSwiftFieldsToWireAccountDetailsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('wire_account_details', function (Blueprint $table) {
            $table->string('correspondent_bank')->nullable();
            $table->string('correspondent_bank_swift')->nullable();
            $table->string('intermediary_bank')->nullable();
            $table->string('intermediary_bank_swift')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('wire_account_details', function (Blueprint $table) {
            $table->dropColumn('correspondent_bank');
            $table->dropColumn('correspondent_bank_swift');
            $table->dropColumn('intermediary_bank');
            $table->dropColumn('intermediary_bank_swift');
        });
    }
}
