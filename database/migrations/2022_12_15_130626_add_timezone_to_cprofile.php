<?php

use App\Enums\TimezoneEnum;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddTimezoneToCprofile extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('c_profiles', function (Blueprint $table) {
            $table->string('timezone')->default(TimezoneEnum::TIMEZONE_DEFAULT);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('c_profiles', function (Blueprint $table) {
            $table->dropColumn('timezone');
        });
    }
}
