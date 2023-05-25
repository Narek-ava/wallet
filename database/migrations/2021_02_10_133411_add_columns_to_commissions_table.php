<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddColumnsToCommissionsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('commissions', function (Blueprint $table) {
            $table->uuid('rate_template_id')->nullable()->after('max_commission');
            $table->foreign('rate_template_id')->references('id')->on('rate_templates');
            $table->integer('commission_type')->nullable()->after('rate_template_id');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('commissions', function (Blueprint $table) {
            $table->dropForeign('rate_template_id');
            $table->dropColumn('rate_template_id');
            $table->dropColumn('commission_type');
        });
    }
}
