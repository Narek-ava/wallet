<div class="tab-pane fade @if($currency == \App\Enums\Currency::CURRENCY_USD)show active @endif" id="{{ strtolower($currency) }}" role="tabpanel" aria-labelledby="{{strtolower($currency)}}Link">
    <div class="row complianceRatesTableWidth d-none d-md-flex">
        <div class="col-md-7 textBold">{{ t('compliance_rates_table_heading_type') }}</div>
        <div class="col-md-5 textBold">{{ t('compliance_rates_table_heading_fee') }}</div>
    </div>
    <div class="row mt-3 complianceRatesTableWidth">
        <div class="col-md-12 card-default br20">
            <div class="row bordered complianceRateTableRow">
                <div class="col-md-7" data-label-sm="{{ t('compliance_rates_table_heading_type') }}">{{ t('compliance_rates_percents_sepa') }}</div>
                <div class="col-md-5" data-label-sm="{{ t('compliance_rates_table_heading_fee') }}">{{ $rateTemplate->sepaIncoming($currency) ?? 0 }}% /{{ $rateTemplate->sepaOutgoing($currency) ?? 0 }}%</div>
            </div>
            <div class="row bordered complianceRateTableRow">
                <div class="col-md-7" data-label-sm="{{ t('compliance_rates_table_heading_type') }}">{{ t('compliance_rates_percents_swift') }}</div>
                <div class="col-md-5" data-label-sm="{{ t('compliance_rates_table_heading_fee') }}">{{ $rateTemplate->swiftIncoming($currency) ?? 0 }}% /{{ $rateTemplate->swiftOutgoing($currency) ?? 0 }}%</div>
            </div>

            @if(config('cratos.enable_fiat_wallets'))
            <div class="row bordered complianceRateTableRow">
                <div class="col-md-7" data-label-sm="{{ t('compliance_rates_table_heading_type') }}">{{ t('rate_by_crypto_fiat_wallet') }}
                </div>
                <div class="col-md-5" data-label-sm="{{ t('compliance_rates_table_heading_fee') }}">{{ $rateTemplate->fiatBuyCryptoFromFiatWallet($currency) ?? 0 }}% </div>
            </div>

            <div class="row bordered complianceRateTableRow">
                <div class="col-md-7" data-label-sm="{{ t('compliance_rates_table_heading_type') }}">{{ t('rate_top_up_fiat_wallet_by_wire') }}
                </div>
                <div class="col-md-5" data-label-sm="{{ t('compliance_rates_table_heading_fee') }}">{{ $rateTemplate->fiatTopUpFiatByWire($currency) ?? 0 }}% </div>
            </div>

            <div class="row bordered complianceRateTableRow">
                <div class="col-md-7" data-label-sm="{{ t('compliance_rates_table_heading_type') }}">{{ t('rate_withdraw_fiat_wallet') }}
                </div>
                <div class="col-md-5" data-label-sm="{{ t('compliance_rates_table_heading_fee') }}">{{ $rateTemplate->fiatWithdrawFiatByWire($currency) ?? 0 }}%</div>
            </div>

            <div class="row bordered complianceRateTableRow">
                <div class="col-md-7" data-label-sm="{{ t('compliance_rates_table_heading_type') }}">{{ t('top_up_fiat_by_crypto') }}
                </div>
                <div class="col-md-5" data-label-sm="{{ t('compliance_rates_table_heading_fee') }}">{{ $rateTemplate->fiatByFiatFromCryptoWallet($currency) ?? 0 }}%</div>
            </div>
            @endif
            <div class="row bordered complianceRateTableRow">
                <div class="col-md-7" data-label-sm="{{ t('compliance_rates_table_heading_type') }}">{{ t('compliance_rates_percents_bank_card') }}</div>
                <div class="col-md-5" data-label-sm="{{ t('compliance_rates_table_heading_fee') }}">{{ $rateTemplate->bankCard($currency) ?? 0 }}%</div>
            </div>
        </div>
    </div>
</div>
