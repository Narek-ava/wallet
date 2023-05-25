<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class DropProfileIdColumnFromRateTemplatesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('rate_templates', function (Blueprint $table) {
            $table->dropForeign('rate_templates_profile_id_foreign');
            $table->dropColumn('profile_id');
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
            $table->uuid('profile_id')->nullable()->after('type_client');
            $table->foreign('profile_id')->references('id')->on('c_profiles');
        });
    }
}
