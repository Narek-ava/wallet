<?php


namespace App\Services;


use App\Enums\Commissions;
use App\Enums\Currency;
use App\Enums\RateTemplatesStatuses;
use App\Models\BankCardRateTemplate;
use App\Models\RatesCategory;
use App\Models\RateTemplate;
use Illuminate\Support\Str;

class RateTemplatesService
{
    public function getDefaultRateTemplateId($typeClient)
    {
        return RateTemplate::query()->where(['is_default' => RateTemplatesStatuses::RATE_TEMPLATE_DEFAULT, 'type_client' => $typeClient, 'status' => RateTemplatesStatuses::STATUS_ACTIVE])->first()->id ?? null;
    }

    public function store($data, $countries)
    {
        $data['id'] = Str::uuid()->toString();
        if (array_key_exists('is_default', $data)) {
            $this->changeDefaultRateTemplate($data['type_client']);
        }
        $rateTemplate = RateTemplate::create($data);
        return $data['id'];
    }

    public function changeDefaultRateTemplate($typeClient)
    {
        RateTemplate::where([
            'is_default' => RateTemplatesStatuses::RATE_TEMPLATE_DEFAULT,
            'type_client' => $typeClient,
        ])->update(['is_default' => RateTemplatesStatuses::RATE_TEMPLATE_NOT_DEFAULT]);
    }

    public function getRateTemplatesServiceActive()
    {
        return RateTemplate::query()->where('status', RateTemplatesStatuses::STATUS_ACTIVE)->get();
    }

    public function getRateTemplatesServiceActivePaginate()
    {
        return RateTemplate::query()->where('status', RateTemplatesStatuses::STATUS_ACTIVE)->paginate(config('cratos.pagination.operations'));
    }

    public function getBankCardRateTemplatesServiceActive()
    {
        return BankCardRateTemplate::query()->where('status', RateTemplatesStatuses::STATUS_ACTIVE)->paginate(config('cratos.pagination.operations'));
    }

    public function getRateTemplatesServiceAll()
    {
        return RateTemplate::query()->paginate(config('cratos.pagination.operations'));
    }

    public function getRateTemplateById($id)
    {
        return RateTemplate::with(['limits' => function($ql){
            $ql->orderBy('level');
        }, 'commissions' => function($qc) {
            $qc->where('is_active', Commissions::COMMISSION_ACTIVE);
        }, 'countries'])->where('id', $id)->first();
    }

    public function getRateTemplateCountriesData($id)
    {
        return [
            'template' => RateTemplate::with([
                'limits' => function ($ql) {
                    $ql->orderBy('level');
                },
                'commissions' => function ($qc) {
                    $qc->where('is_active', Commissions::COMMISSION_ACTIVE);
                },
                'countries'
            ])->where('id', $id)->first(),

            'currencies' => Currency::getAllCurrencies()
        ];
    }

    public function dropExistsOldCommission($rateTemplateId, $type, $commissionType, $currency)
    {
        $rateTemplate = RateTemplate::find($rateTemplateId);
        $rateTemplate->commissions()->where(['commission_type' => $commissionType,
            'type' => $type,
            'currency' => $currency])
            ->update(['is_active' => 0]);
    }

    public function getActiveRateTemplatesOptions($typeClient, $profile)
    {
        $rateTemplates = RateTemplate::whereHas('countries', function($q) use ($profile){
            $q->where('country', $profile->country);
        })->where(['type_client' => $typeClient, 'status' => RateTemplatesStatuses::STATUS_ACTIVE])->get();
        if ($profile->rateTemplate()->get()->isNotEmpty()){
            $rateTemplates = $rateTemplates->merge($profile->rateTemplate()->get());
        }
        $options = '<option></option>';
        if ($rateTemplates->count()) {
            foreach ($rateTemplates as $rate) {
                $selected = $profile->rate_template_id === $rate->id ? 'selected' : '';
                $options .= "<option value='". $rate->id ."' ". $selected ."> $rate->name </option>";
            }
        }
        return $options;
    }


    public function getActiveRatesByAccountType($accountType)
    {
        $queryParams =[
            'status' => RateTemplatesStatuses::STATUS_ACTIVE,
            'type_client' => $accountType
        ];

        return RateTemplate::query()->where($queryParams)->get();
    }

    public function storeBankCardRateTemplate($data)
    {
        BankCardRateTemplate::create([
            'status' => RateTemplatesStatuses::STATUS_ACTIVE,
            'name' => $data['bankCardRateName'],
            'overview_type' => $data['bankCardOverviewType'],
            'overview_fee' => $data['bankCardOverviewFee'],
            'transactions_type' => $data['bankCardTransactionsType'],
            'transactions_fee' => $data['bankCardTransactionsFee'],
            'fees_type' => $data['bankCardFeesType'],
            'fees_fee' => $data['bankCardFeesFee'],
        ]);
    }

    public function getBankCardRateTemplateById($id)
    {
        return BankCardRateTemplate::find($id);
    }

    public function updateBankCardRateTemplate($data)
    {
        $template = $this->getBankCardRateTemplateById($data['bank_card_rate_template_id']);

        $template->update([
            'status' => $data['status'],
            'overview_type' => $data['bankCardOverviewType'],
            'overview_fee' => $data['bankCardOverviewFee'],
            'transactions_type' => $data['bankCardTransactionsType'],
            'transactions_fee' => $data['bankCardTransactionsFee'],
            'fees_type' => $data['bankCardFeesType'],
            'fees_fee' => $data['bankCardFeesFee'],
        ]);
    }

    public function getActiveBankCardRates()
    {
        return BankCardRateTemplate::query()->where('status', RateTemplatesStatuses::STATUS_ACTIVE)->first();
    }

    public function getAllActiveBankCardRates()
    {
        return BankCardRateTemplate::query()->where('status', RateTemplatesStatuses::STATUS_ACTIVE)->get();
    }

    public function getRateTemplateByReferralPartnerId($referralPartnerId)
    {
        return RateTemplate::query()->where('referral_partner_id', $referralPartnerId)->first();
    }
}
