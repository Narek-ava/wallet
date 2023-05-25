<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateReferralLinksTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('referral_links', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('name');
            $table->uuid('partner_id')->nullable();
            $table->uuid('individual_rate_templates_id')->nullable();
            $table->uuid('corporate_rate_templates_id')->nullable();
            $table->date('activation_date');
            $table->date('deactivation_date');
            $table->timestamps();

            $table->foreign('partner_id')->references('id')->on('referral_partners')->nullOnDelete();
            $table->foreign('individual_rate_templates_id')->references('id')->on('rate_templates')->nullOnDelete();
            $table->foreign('corporate_rate_templates_id')->references('id')->on('rate_templates')->nullOnDelete();

        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('referral_links');
    }
}
