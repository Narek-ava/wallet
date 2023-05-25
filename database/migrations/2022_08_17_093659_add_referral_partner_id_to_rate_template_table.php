<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddReferralPartnerIdToRateTemplateTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('rate_templates', function (Blueprint $table) {
            $table->uuid('referral_partner_id')->nullable()->after('referral_remuneration');
            $table->foreign('referral_partner_id')->references('id')->on('referral_partners')->nullOnDelete();
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
            $table->dropForeign('rate_template_referral_partner_id_foreign');
            $table->dropColumn('referral_partner_id');
        });
    }
}
