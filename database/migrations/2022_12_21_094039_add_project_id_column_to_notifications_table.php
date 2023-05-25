<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddProjectIdColumnToNotificationsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        if (!Schema::hasColumn('notifications', 'project_id')) {
            Schema::table('notifications', function (Blueprint $table) {
                $table->uuid('project_id')->nullable();
                $table->foreign('project_id')->references('id')->on('projects')->cascadeOnDelete();
            });
        }
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        if (Schema::hasColumn('notifications', 'project_id')) {
            Schema::table('notifications', function (Blueprint $table) {
                $table->dropForeign('notifications_project_id_foreign');
                $table->dropColumn('project_id');
            });
        }
    }
}
