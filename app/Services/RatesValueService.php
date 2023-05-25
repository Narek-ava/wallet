<?php
namespace App\Services;

use App\Models\RatesValues;

class RatesValueService
{
    public function getRateValueRate($rates_category_id, $compliance_level, $by)
    {
        $key = 'incoming_'.$by.'_rate';
        return RatesValues::where(['key' => $key, 'rates_category_id' => $rates_category_id, 'level' => $compliance_level])->first()->value;
    }

    public function getRateValueMonthLimit($rates_category_id, $compliance_level)
    {
        $key = 'all_transactions_month_limit';
        return RatesValues::where(['key' => $key, 'rates_category_id' => $rates_category_id, 'level' => $compliance_level])->first()->value;
    }

    public function getRateValueLimit($rates_category_id, $compliance_level, $by, $currency)
    {
        $key = 'incoming_'.$by.'_'.$currency.'_limit';
        return RatesValues::where(['key' => $key, 'rates_category_id' => $rates_category_id, 'level' => $compliance_level])->first()->value;
    }
}
