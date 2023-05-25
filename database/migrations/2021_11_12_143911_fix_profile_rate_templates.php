<?php

use App\Models\Cabinet\CProfile;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class FixProfileRateTemplates extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        $rateTemplatesService = new \App\Services\RateTemplatesService();

        $individualDefaultRate = $rateTemplatesService->getDefaultRateTemplateId(CProfile::TYPE_INDIVIDUAL);

        CProfile::query()
            ->where('account_type', CProfile::TYPE_INDIVIDUAL)
            ->whereHas('rateTemplate', function ($q) {
                return $q->where('status', \App\Enums\RateTemplatesStatuses::STATUS_DISABLED);
            })->update(['rate_template_id' => $individualDefaultRate]);


        $corporateDefaultRate = $rateTemplatesService->getDefaultRateTemplateId(CProfile::TYPE_CORPORATE);

        CProfile::query()
            ->where('account_type', CProfile::TYPE_CORPORATE)
            ->whereHas('rateTemplate', function ($q) {
                return $q->where('status', \App\Enums\RateTemplatesStatuses::STATUS_DISABLED);
            })->update(['rate_template_id' => $corporateDefaultRate]);
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        //
    }
}
