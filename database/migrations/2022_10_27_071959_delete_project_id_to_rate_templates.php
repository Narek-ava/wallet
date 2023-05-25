<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class DeleteProjectIdToRateTemplates extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('rate_templates', function (Blueprint $table)
        {
            $table->dropForeign('rate_templates_project_id_foreign');
            $table->dropColumn('project_id');
        });

        Schema::table('bank_card_rate_templates', function (Blueprint $table)
        {
            $table->dropForeign('bank_card_rate_templates_project_id_foreign');
            $table->dropColumn('project_id');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('rate_templates', function (Blueprint $table) {
            $table->uuid('project_id')->nullable();
            $table->foreign('project_id')->references('id')->on('projects')->nullOnDelete();
        });

        Schema::table('bank_card_rate_templates', function (Blueprint $table) {
            $table->uuid('project_id')->nullable();
            $table->foreign('project_id')->references('id')->on('projects')->nullOnDelete();
        });
    }
}
