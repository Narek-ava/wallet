<?php

use Illuminate\Database\Seeder;
use App\Models\{Cabinet\CProfile, Cabinet\CUser, RatesCategory};
use Illuminate\Support\{Facades\DB, Facades\Log, Str};

class RatesSeed extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $r = new \App\Services\RatesService;

        $fill = function ($key, $in) use (&$values, $r) {
            $s = new \stdClass;
            $s->$key = [null, $in[0], $in[1], $in[2]];// yes, 'cos 1-3, not 0-2

            $values += $r->getValuesFor3Levels($key, $s);
        };

        //  Individual basic rates
        $values = [];
        $fill('application_processing_fee', [0, 0, 0]);
        $fill('account_maintenance', [0, 0, 0]);
        $fill('account_closure', [0, 0, 0]);
        $fill('all_transactions_month_limit', [14999, 14999, null]);

        $type = 'incoming_sepa';
        $fill($type . '_eur_limit', [499, 4999, null]);
        $fill($type . '_rate', [1.5, 1.5, 1.5]);
        $fill($type . '_eur_min', [30, 30, 30]);
        $type = 'outgoing_sepa';
        $fill($type . '_eur_limit', [499, 4999, null]);
        $fill($type . '_rate', [1.5, 1.5, 1.5]);
        $fill($type . '_eur_min', [35, 35, 35]);
        $type = 'incoming_swift';
        $fill($type . '_eur_limit', [499, 4999, null]);
        $fill($type . '_usd_limit', [499, 4999, null]);
        $fill($type . '_rate', [1.5, 1.5, 1.5]);
        $fill($type . '_eur_min', [50, 50, 50]);
        $fill($type . '_usd_min', [40, 40, 40]);
        $type = 'outgoing_swift';
        $fill($type . '_eur_limit', [499, 4999, null]);
        $fill($type . '_usd_limit', [499, 4999, null]);
        $fill($type . '_rate', [1.5, 1.5, 1.5]);
        $fill($type . '_eur_min', [65, 65, 65]);
        $fill($type . '_usd_min', [65, 65, 65]);

        $type = 'incoming_card_eea';
        $fill($type . '_eur_limit', [499, 4999, null]);
        $fill($type . '_usd_limit', [499, 4999, null]);
        $fill($type . '_rate', [3, 3, 3]);
        $fill($type . '_eur_min', [0, 0, 0]);
        $fill($type . '_usd_min', [0, 0, 0]);
        $type = 'outgoing_card_eea';
        $fill($type . '_eur_limit', [499, 4999, null]);
        $fill($type . '_usd_limit', [499, 4999, null]);
        $fill($type . '_rate', [3, 3, 3]);
        $fill($type . '_eur_min', [0, 0, 0]);
        $fill($type . '_usd_min', [0, 0, 0]);
        $type = 'incoming_card_non_eea';
        $fill($type . '_eur_limit', [499, 4999, null]);
        $fill($type . '_usd_limit', [499, 4999, null]);
        $fill($type . '_rate', [5, 5, 5]);
        $fill($type . '_eur_min', [0, 0, 0]);
        $fill($type . '_usd_min', [0, 0, 0]);
        $type = 'outgoing_card_non_eea';
        $fill($type . '_eur_limit', [499, 4999, null]);
        $fill($type . '_usd_limit', [499, 4999, null]);
        $fill($type . '_rate', [5, 5, 5]);
        $fill($type . '_eur_min', [0, 0, 0]);
        $fill($type . '_usd_min', [0, 0, 0]);

        $type = 'incoming_crypto';
        $fill($type . '_eur_limit', [499, 4999, null]);
        $fill($type . '_usd_limit', [499, 4999, null]);
        $fill($type . '_rate', [0, 0, 0]);
        $fill($type . '_eur_min', [0, 0, 0]);
        $fill($type . '_usd_min', [0, 0, 0]);
        $type = 'outgoing_crypto_external';
        $fill($type . '_eur_limit', [499, 4999, null]);
        $fill($type . '_usd_limit', [499, 4999, null]);
        $fill($type . '_rate', [0.15, 0.15, 0.15]);
        $fill($type . '_eur_min', [0, 0, 0]);
        $fill($type . '_usd_min', [0, 0, 0]);
        $type = 'outgoing_crypto_internal';
        $fill($type . '_eur_limit', [499, 4999, null]);
        $fill($type . '_usd_limit', [499, 4999, null]);
        $fill($type . '_rate', [0, 0, 0]);
        $fill($type . '_eur_min', [0, 0, 0]);
        $fill($type . '_usd_min', [0, 0, 0]);

        $type = 'exchange';
        $fill($type . '_eur_limit', [499, 4999, null]);
        $fill($type . '_usd_limit', [499, 4999, null]);
        $fill($type . '_rate', [1.5, 1.5, 1.5]);
        $fill($type . '_eur_min', [0, 0, 0]);
        $fill($type . '_usd_min', [0, 0, 0]);


        $id = Str::uuid();
        $c = RatesCategory::create([
            'id' => $id,
            'default_for_account_type' => CProfile::TYPE_INDIVIDUAL,
            'title' => 'Individual basic rates',
        ]);

        $models = $r->mapNewValues($values);
        foreach ($models as $model) {
            $model->fill([
                'rates_category_id' => $id,
                'id' => Str::uuid(),
            ])->save();
        }


        //  Corporate basic rates
        $values = [];
        $fill('application_processing_fee', [250, 250, 250]);
        $fill('account_maintenance', [50, 50, 50]);
        $fill('account_closure', [null, null, null]);
        $fill('all_transactions_month_limit', [24999, 24999, null]);

        $type = 'incoming_sepa';
        $fill($type . '_eur_limit', [499, 4999, null]);
        $fill($type . '_rate', [1, 1, 1]);
        $fill($type . '_eur_min', [70, 70, 70]);
        $type = 'outgoing_sepa';
        $fill($type . '_eur_limit', [499, 4999, null]);
        $fill($type . '_rate', [1, 1, 1]);
        $fill($type . '_eur_min', [35, 35, 35]);
        $type = 'incoming_swift';
        $fill($type . '_eur_limit', [499, 4999, null]);
        $fill($type . '_usd_limit', [499, 4999, null]);
        $fill($type . '_rate', [1, 1, 1]);
        $fill($type . '_usd_min', [80, 80, 80]);
        $fill($type . '_eur_min', [90, 90, 90]);
        $type = 'outgoing_swift';
        $fill($type . '_eur_limit', [499, 4999, null]);
        $fill($type . '_usd_limit', [499, 4999, null]);
        $fill($type . '_rate', [1, 0, 0]);
        $fill($type . '_eur_min', [65, 65, 65]);
        $fill($type . '_usd_min', [65, 65, 65]);

        $type = 'incoming_card_eea';
        $fill($type . '_eur_limit', [499, 4999, null]);
        $fill($type . '_usd_limit', [499, 4999, null]);
        $fill($type . '_rate', [0, 0, 0]);
        $fill($type . '_eur_min', [0, 0, 0]);
        $fill($type . '_usd_min', [0, 0, 0]);
        $type = 'outgoing_card_eea';
        $fill($type . '_eur_limit', [499, 4999, null]);
        $fill($type . '_usd_limit', [499, 4999, null]);
        $fill($type . '_rate', [0, 0, 0]);
        $fill($type . '_eur_min', [0, 0, 0]);
        $fill($type . '_usd_min', [0, 0, 0]);
        $type = 'incoming_card_non_eea';
        $fill($type . '_eur_limit', [499, 4999, null]);
        $fill($type . '_usd_limit', [499, 4999, null]);
        $fill($type . '_rate', [0, 0, 0]);
        $fill($type . '_eur_min', [0, 0, 0]);
        $fill($type . '_usd_min', [0, 0, 0]);
        $type = 'outgoing_card_non_eea';
        $fill($type . '_eur_limit', [499, 4999, null]);
        $fill($type . '_usd_limit', [499, 4999, null]);
        $fill($type . '_rate', [0, 0, 0]);
        $fill($type . '_eur_min', [0, 0, 0]);
        $fill($type . '_usd_min', [0, 0, 0]);

        $type = 'incoming_crypto';
        $fill($type . '_eur_limit', [499, 4999, null]);
        $fill($type . '_usd_limit', [499, 4999, null]);
        $fill($type . '_rate', [0, 0, 0]);
        $fill($type . '_eur_min', [0, 0, 0]);
        $fill($type . '_usd_min', [0, 0, 0]);
        $type = 'outgoing_crypto_external';
        $fill($type . '_eur_limit', [499, 4999, null]);
        $fill($type . '_usd_limit', [499, 4999, null]);
        $fill($type . '_rate', [0.15, 0.15, 0.15]);
        $fill($type . '_eur_min', [0, 0, 0]);
        $fill($type . '_usd_min', [0, 0, 0]);
        $type = 'outgoing_crypto_internal';
        $fill($type . '_eur_limit', [499, 4999, null]);
        $fill($type . '_usd_limit', [499, 4999, null]);
        $fill($type . '_rate', [0, 0, 0]);
        $fill($type . '_eur_min', [0, 0, 0]);
        $fill($type . '_usd_min', [0, 0, 0]);

        $type = 'exchange';
        $fill($type . '_eur_limit', [499, 4999, null]);
        $fill($type . '_usd_limit', [499, 4999, null]);
        $fill($type . '_rate', [2, 2, 2]);
        $fill($type . '_eur_min', [0, 0, 0]);
        $fill($type . '_usd_min', [0, 0, 0]);

        $id = Str::uuid();
        $c = RatesCategory::create([
            'id' => $id,
            'default_for_account_type' => CProfile::TYPE_CORPORATE,
            'title' => 'Corporate basic rates',
        ]);

        $models = $r->mapNewValues($values);
        foreach ($models as $model) {
            $model->fill([
                'rates_category_id' => $id,
                'id' => Str::uuid(),
            ])->save();
        }

        $ratesCats = [
            1 => $corpCatId = $r->getRatesCategoryForAccountType(1),
            2 => $corpCatId = $r->getRatesCategoryForAccountType(2),
        ];

        foreach (CProfile::all() as $cProfile) {
            $cProfile->rates_category_id = $ratesCats[$cProfile->account_type];
            $cProfile->save();
        }
    }
}
