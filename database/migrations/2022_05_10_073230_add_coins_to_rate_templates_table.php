<?php

use App\Enums\Commissions;
use App\Enums\CommissionType;
use App\Enums\Currency;
use App\Models\Commission;
use App\Models\RateTemplate;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;

class AddCoinsToRateTemplatesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        $newCoins = array_diff(Currency::getList(), [
            Currency::CURRENCY_BTC,
            Currency::CURRENCY_LTC,
            Currency::CURRENCY_BCH,
        ]);

        $dataRate = [Commissions::TYPE_INCOMING, Commissions::TYPE_OUTGOING];
        $rateTemplates = RateTemplate::all()->pluck('id')->toArray();

        foreach ($rateTemplates as $rateId) {
            foreach ($newCoins as $coin) {
                foreach ($dataRate as $type) {
                    Commission::create([
                        'id' => Str::uuid()->toString(),
                        'commission_name' => 'name',
                        'type' => $type,
                        'fixed_commission' => 0,
                        'percent_commission' => 0,
                        'min_commission' => 0,
                        'max_commission' => 0,
                        'min_amount' => 0,
                        'refund_transfer_percent' => 0.5,
                        'refund_transfer' => 0,
                        'refund_minimum_fee' => 0,
                        'blockchain_fee' => 0.000001,
                        'rate_template_id' => $rateId,
                        'commission_type' => CommissionType::TYPE_CRYPTO,
                        'currency' => Currency::ALL_NAMES[$coin],
                    ]);
                }
            }
        }
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        $newCoins = array_diff(Currency::getList(), [
            Currency::CURRENCY_BTC,
            Currency::CURRENCY_LTC,
            Currency::CURRENCY_BCH,
        ]);

        Commission::whereIn('currency', array_values($newCoins))->delete();
    }
}
