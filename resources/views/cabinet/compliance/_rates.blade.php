<div class="row mb-5">

</div>
<!-- <div class="row">
    <div class="col-md-6">
        <h6>{{ t('compliance_rates_account_rates') }}</h6>
    </div>
    <div class="col-md-6">
        <h6>{{ t('compliance_rates_account_limits') }}</h6>
    </div>
</div> -->

<div class="row">
    <div class="col-md-6">
        <h6>{{ t('compliance_rates_account_rates') }}</h6>
        <div class="mt-4">
            <ul class="nav nav-tabs" id="rateTemplateTab" role="tablist">
                @php $currencies = \App\Enums\Currency::getAllCurrencies(); @endphp
                @foreach(\App\Enums\Currency::getAllCurrencies() as $currency)
                    <li class="nav-item">
                        <a class="nav-link pl-4 pr-4  @if ($currency == reset($currencies)) active @endif" id="{{ strtolower($currency) }}Link" data-toggle="tab" href="#{{ strtolower($currency) }}" role="tab" aria-controls="{{ strtolower($currency) }}home" aria-selected="{{ $currency == reset($currencies) ? true : false }}">{{ $currency }}</a>
                    </li>
                @endforeach
            </ul>
        </div>
    </div>
</div>
<br>
<div class="row mt-2 pl-4">
    <div class="col-md-6">
        <div class="tab-content complianceRatesFontSize" id="rateTemplateTabContent">
            @foreach(\App\Enums\Currency::FIAT_CURRENCY_NAMES as $currency)
                @include('cabinet.compliance.partials.rates.rates-fiat',[ 'currency' => $currency ])
            @endforeach

            @foreach(\App\Enums\Currency::getList() as $currency)
                @include('cabinet.compliance.partials.rates.rates-crypto',[ 'currency' => $currency ])
            @endforeach
        </div>
    </div>
    <div class="col-md-6">
        <div class="mt-5 mb-4 d-block d-md-none">
            <h6>{{ t('compliance_rates_account_limits') }}</h6>
        </div>
        @include('cabinet.compliance.partials.rates.limits')
    </div>
</div>
<div class="row">
    <div class="col-md-6">
        <h6>{{ t('compliance_rates_bank_rates') }}</h6>
    </div>
</div>

<div class="row mt-2 pl-4">
    <div class="col-md-6">
        <div class="tab-content complianceRatesFontSize">
            @foreach(\App\Enums\Currency::FIAT_CURRENCY_NAMES as $currency)
                @include('cabinet.compliance.partials.rates.rates-bank',[ 'currency' => $currency ])
            @endforeach
        </div>
    </div>
</div>

