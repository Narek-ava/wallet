<?php


namespace App\Services;


use App\Models\Cabinet\CProfile;
use App\Models\RatesCategory;
use App\Models\RatesValues;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class RatesService
{
    const STATUS_ACTIVE = 1;
    const STATUS_INACTIVE = 0;

    public static function typeSubs(): array
    {
        return ['limit', 'rate', 'min']; // @todo CONST
    }


    public function getRatesCategoryForAccountType(int $account_type): string
    {
        return RatesCategory::where('default_for_account_type', $account_type)
            ->firstOrFail()->id;
    }


    public function cProfileValues(CProfile $cProfile): array
    {
        $rates = [];

        /**
         * @var  $getActualComplianceLevel
         * @todo состыкоать со статусами и прочим
         * в результате должно отдавать 1-3 или 0
         */
        $getActualComplianceLevel = function (CProfile $cProfile) {
            if (!$cProfile->rates_category_id) {
                // вообще-то так не бывает
                return 0;
            } else {
                // по CRATOS-402 Spec - статус Клиента надо сложнее, но там ничего толком не описано
                return $cProfile->compliance_level;
            }
        };

        $complianceLevel = $getActualComplianceLevel($cProfile);

        if (!$complianceLevel) {

            $rates['all_transactions_month_limit'] = 0;
            foreach ($this->getOperationTypes() as $oType) {
                $key = $oType . '_limit';
                $rates[$key] = 0;
                $key = $oType . '_rate';
                $rates[$key] = '-';
                $key = $oType . '_min';
                $rates[$key] = '-';
            }
            return [
                'css' => 'textRed',
                'values' => $rates,
            ];
        } else {
            $valuesDB = $this->getValueListFromDB($cProfile->rates_category_id);
            /** @var array $cValues все значения категории */
            $cValues = $this->getValueListFrom($valuesDB);
            foreach ($cValues as $key => $valuesOf3Levels) {
                $rates[$key] = $valuesOf3Levels[$complianceLevel] ?? '-';
            }

            return [
                'css' => '', // @note no special CSS needed at now
                'values' => $rates,
            ];
        }
    }

    public function cProfileSelectOptions(?string $currentId = ''): string
    {
        $cs = RatesCategory::where('status', self::STATUS_ACTIVE)
            ->orWhere('id', $currentId)
            ->get();
        $html = '';
        foreach ($cs as $c) {
            $d = ($c->status != self::STATUS_ACTIVE) ? ' disabled ' : '';
            $s = ($c->id === $currentId) ? ' selected ' : '';
            $o = '<option ' . $d . $s . ' value="' . $c->id . '">' . $c->title . "</option>\n";
            $html .= $o;
        }
        if (!$currentId) {
            $o = '<option disabled selected  value="">' . "</option>\n";
            $html = $o . $html;
        }
        return $html;
    }

    /**
     * @param array $s Source of category values
     * @return array
     */
    public function mapNewValues(array $s): array
    {
        $r = [];
        foreach ($s as $key => $value0) {
            for ($l = 1; $l <= 3; $l++) {
                $model = new RatesValues;
                $model->fill([
                    'key' => $key,
                    'level' => $l,
                    'value' => $value0[$l],
                ]);
                $r[] = $model;
            }
        }
        return $r;
    }


    // s = data
    protected function map3ValuesToNewModels($key, array $s) //models for create
    {
        $r = [];
        for ($l = 1; $l <= 3; $l++) {
            $model = new RatesValues;
            $model->fill([
                'key' => $key,
                'level' => $l,
                'value' => $s[$key],
                'id' => Str::uuid(),
            ]);
            $r[] = $model;
        }
        return $r;

    }

    /**
     * @param Request|array from RatesValues - source
     */
    public function getValuesFor3Levels(string $key, $s): array
    {
        if (!is_array($s->$key ?? null)) {
            $s = new \stdClass;
            $s->$key = [null, null, null, null];  // yes, 'cos 1-3, not 0-2
        }

        $result = [
            $key => [
                1 => \C\rates_format($s->$key[1]),
                2 => \C\rates_format($s->$key[2]),
                3 => \C\rates_format($s->$key[3]),
            ]
        ];
        return $result;

    }

    /** @deprecated */
    public function getDim(string $key): int
    {
        return self::DIM[$key] ?? 0;
    }

    public function getValueListFromDB(string $rates_category_id): object
    {
        $values = new \stdClass;
        $models = RatesValues::where('rates_category_id', $rates_category_id)->get();
        foreach ($models as $model) {
            $key = $model->key;
            if (!isset($values->$key)) {
                $values->$key = [null, null, null, null];  // yes, 'cos 1-3, not 0-2
            }
            $values->$key[$model->level] = $model->value;
        }
        return $values;
    }

    /**
     * @param $s - source, can be array or collection of values
     * @return array
     */
    public function getValueListFrom($s = null): array
    {
        $values = [];

        $values += $this->getValuesFor3Levels('application_processing_fee', $s);
        $values += $this->getValuesFor3Levels('account_maintenance', $s);
        $values += $this->getValuesFor3Levels('account_closure', $s);

        $values += $this->getValuesFor3Levels('all_transactions_month_limit', $s);

        $types = self::getOperationTypes();
        $values['_operation_types'] = $types;
        foreach ($types as $type) {
            $values += $this->getValuesFor3Levels($type . '_limit', $s);
            $values += $this->getValuesFor3Levels($type . '_rate', $s);
            $values += $this->getValuesFor3Levels($type . '_min', $s);
        }

        return $values;
    }

    public function getDiffOfValueList(array $values1, array $values2): array
    {
        $diff = [];
        $c = function ($v1, $v2) {
            $v1 = \C\rates_format($v1);
            $v2 = \C\rates_format($v2);
            return bccomp($v1, $v2, \C\RATES_SCALE);
        };
        foreach (self::getOperationTypes() as $type) {
            foreach (self::typeSubs() as $subtype) {
                $key = $type . '_' . $subtype;
                for ($l = 1; $l <= 3; $l++) {
                    if ($c($values1[$key][$l] ?? null, $values2[$key][$l] ?? null) != 0) {
                        $diff[$key][$l] = $values1[$key][$l] ?? null;
                    }
                }
            }
        }
        foreach (self::getCommonTypes() as $key) {
            for ($l = 1; $l <= 3; $l++) {
                if ($c($values1[$key][$l] ?? null, $values2[$key][$l] ?? null) != 0) {
                    $diff[$key][$l] = $values1[$key][$l] ?? null;
                }
            }
        }

        return $diff;
    }

    public function getCommonTypes(): array
    {
        return static::TYPES_COMMON ?? [];
    }

    public function getOperationTypes(): array
    {
        $keys = static::TYPES_OPERATION ?? [];
        foreach ($keys as &$key) {
            $key = strtolower(str_replace([' ', '-'], '_', $key));
        }
        return $keys;
    }


    const DIM = [
        'account_closure' => 0,
        'account_maintenance' => 0,
        'application_processing_fee' => 0,
        'all_transactions_month_limit' => 0,
        '_limit' => 0,
        '_rate' => 0,
        '_min' => 0,
    ];

    const CRYPTOS = [
        'Algorand' => 'ALGO',
        'Bitcoin' => 'BTC',
        'Bitcoin Cash' => 'BCH',
        'PAX Gold' => 'PAXG',
        'Stellar Lumens' => 'XLM',
        'USD Coin' => 'USDC',
        'Zcash' => 'ZEC',
    ];

    const TYPES_OPERATION = [
        'Incoming SEPA EUR',
        'Outgoing SEPA EUR',
        'Incoming SWIFT EUR',
        'Incoming SWIFT USD',
        'Outgoing SWIFT EUR',
        'Outgoing SWIFT USD',
        'Incoming card EEA EUR',
        'Incoming card EEA USD',
        'Outgoing card EEA EUR',
        'Outgoing card EEA USD',
        'Incoming card non-EEA EUR',
        'Incoming card non-EEA USD',
        'Outgoing card non-EEA EUR',
        'Outgoing card non-EEA USD',
        'Incoming crypto EUR',
        'Incoming crypto USD',
        'Outgoing crypto external EUR',
        'Outgoing crypto external USD',
        'Outgoing crypto internal EUR',
        'Outgoing crypto internal USD',
        'Exchange EUR',
        'Exchange USD',
    ];

    const SUBTYPES = [
        'limit',
        'rate',
        'min',
    ];

    const TYPES_COMMON = [
        'application_processing_fee',
        'account_maintenance',
        'account_closure',
        'all_transactions_month_limit',
    ];

    //** not used, but can be in future */
    const VALUES2 = [

        'Application processing fee' => [
            'sequence' => 1010,
            'per' => 'once',
            'category' => true,
        ],
        'Account maintenance' => [
            'sequence' => 1020,
            'per' => 'month',
            'category' => true,
        ],
        'Account closure' => [
            'sequence' => 1020,
            'per' => 'once',
            'category' => true,
        ],

        'Transactions limit' => [
            'sequence' => 2010,
            'per' => 'month',
            'category' => true,
        ],

        'Incoming funds to exchange via SEPA' => [
            'category' => true,
            'sequence' => 10010,
            'rate_group' => true,
// ==>            'level' => true,

        ],
        'Outgoing funds from exchange via SEPA' => [],
        'Incoming funds to exchange via SWIFT' => [],
        'Outgoing funds from exchange via SWIFT' => [],
        'Incoming funds via crypto' => [],
        'Outgoing funds via crypto (external wallet)' => [],
        'Outgoing funds via crypto (internal wallet)' => [],
        'Exchange' => [],


        'Withdrawal fee (common)' => [],
        'Withdrawal fee (Corporate manual)' => [],

        'Crypto deposit and withdrawal minimums' => [],

    ];

    //** not used, but can be in future */
    const VALUES3 = [

        'Application processing fee' => [
            'sequence' => 1010,
            'per' => 'once',
            'category' => true,
        ],
        'Account maintenance' => [
            'sequence' => 1020,
            'per' => 'month',
            'category' => true,
        ],
        'Account closure' => [
            'sequence' => 1020,
            'per' => 'once',
            'category' => true,
        ],

        'Transactions limit' => [
            'sequence' => 2010,
            'per' => 'month',
            'category' => true,
        ],

        'Incoming funds to exchange via SEPA' => [
            'category' => true,
            'sequence' => 10010,
            'rate_group' => true,
// ==>            'level' => true,

        ],
        'Outgoing funds from exchange via SEPA' => [],
        'Incoming funds to exchange via SWIFT' => [],
        'Outgoing funds from exchange via SWIFT' => [],
        'Incoming funds via crypto' => [],
        'Outgoing funds via crypto (external wallet)' => [],
        'Outgoing funds via crypto (internal wallet)' => [],
        'Exchange' => [],


        'Withdrawal fee (common)' => [],
        'Withdrawal fee (Corporate manual)' => [],

        'Crypto deposit and withdrawal minimums' => [],

    ];

}
