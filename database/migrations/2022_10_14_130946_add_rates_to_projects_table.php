<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddRatesToProjectsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('projects', function (Blueprint $table) {
            $table->uuid('individual_rate_templates_id')->nullable()->after('color_settings');
            $table->uuid('corporate_rate_templates_id')->nullable()->after('individual_rate_templates_id');
            $table->uuid('bank_card_rate_templates_id')->nullable()->after('corporate_rate_templates_id');

            $table->foreign('individual_rate_templates_id')->references('id')->on('rate_templates')->nullOnDelete();
            $table->foreign('corporate_rate_templates_id')->references('id')->on('rate_templates')->nullOnDelete();
            $table->foreign('bank_card_rate_templates_id')->references('id')->on('bank_card_rate_templates')->nullOnDelete();

        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('projects', function (Blueprint $table) {
            $table->dropForeign('individual_rate_templates_id');
            $table->dropForeign('corporate_rate_templates_id');
            $table->dropForeign('bank_card_rate_templates_id');
            $table->dropColumn('individual_rate_templates_id');
            $table->dropColumn('corporate_rate_templates_id');
            $table->dropColumn('bank_card_rate_templates_id');
        });
    }
}
