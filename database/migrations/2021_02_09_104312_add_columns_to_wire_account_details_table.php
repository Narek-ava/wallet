<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddColumnsToWireAccountDetailsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('wire_account_details', function (Blueprint $table) {
            $table->string('iban')->nullable()->after('time_to_found');
            $table->string('swift')->nullable()->after('iban');
            $table->string('bank_name')->nullable()->after('swift');
            $table->string('bank_address')->nullable()->after('bank_name');
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
            $table->dropColumn('iban');
            $table->dropColumn('swift');
            $table->dropColumn('bank_name');
            $table->dropColumn('bank_address');
        });
    }
}
