<?php

use App\Models\Country;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddAlphanumericSenderToCountriesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        if (Schema::hasColumn('countries', 'is_alphanumeric_sender')) {
            return;
        }
        Schema::table('countries', function (Blueprint $table) {
            $table->boolean('is_alphanumeric_sender')->default(true);
        });

        Country::whereIn('code', [
            'ar', 'bs', 'be', 'ca', 'ky', 'cl', 'cn', 'co', 'cr', 'do', 'ec', 'sv', 'gf', 'gu', 'hu', 'in', 'il', 'kg', 'mw', 'my', 'mc', 'nz', 'ni', 'pk', 'pa', 'pe', 'py', 'pr', 'ru', 'za', 'kr', 'sy', 'tw', 'tn', 'uy', 'us', 'zw',
        ])->update(['is_alphanumeric_sender' => false]);

    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('countries', function (Blueprint $table) {
            $table->dropColumn('is_alphanumeric_sender');
        });
    }
}
