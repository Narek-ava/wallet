<?php

use App\Models\RateTemplate;
use Illuminate\Database\Migrations\Migration;

class Add0LevelLimitsForRateTemplates extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        RateTemplate::query()
            ->chunk(100, function ($rates) {
               foreach ($rates as $rate) {
                   $limit = new \App\Models\Limit();
                   $limit->fill([
                       'transaction_amount_max' => 0,
                       'monthly_amount_max' => 0,
                       'rate_template_id' => $rate->id,
                       'level' => 0,
                   ]);
                   $limit->save();
               }
            });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        RateTemplate::query()
            ->chunk(100, function ($rates) {
                foreach ($rates as $rate) {
                    $rate->limits()->where('level', 0)->delete();
                }
            });
    }
}
