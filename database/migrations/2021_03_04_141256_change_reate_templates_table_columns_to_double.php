<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Doctrine\DBAL\Types\FloatType;
use Doctrine\DBAL\Types\Type;

class ChangeReateTemplatesTableColumnsToDouble extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        if (!Type::hasType('double')) {
            Type::addType('double', FloatType::class);
        }
        Schema::table('rate_templates', function (Blueprint $table) {
            $table->double('opening')->change();
            $table->double('maintenance')->change();
            $table->double('account_closure')->change();
            $table->double('referral_remuneration')->nullable()->change();
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

            $table->string('opening')->change();
            $table->string('maintenance')->change();
            $table->string('account_closure')->change();
            $table->string('referral_remuneration')->change();
        });
    }
}
