<div class="tab-pane fade" id="{{ strtolower($currency) }}" role="tabpanel" aria-labelledby="{{ strtolower($currency) }}Link">
    <div class="row complianceRatesTableWidth d-none d-md-flex">
        <div class="col-md-7 textBold">{{ t('compliance_rates_table_heading_type') }}</div>
        <div class="col-md-5 textBold">{{ t('compliance_rates_table_heading_fee') }}</div>
    </div>
    <div class="row mt-3 complianceRatesTableWidth">
        <div class="col-md-12 card-default br20">
            <div class="row bordered complianceRateTableRow">
                <div class="col-md-7" data-label-sm="{{ t('compliance_rates_table_heading_type') }}">{{ t('compliance_rates_percents_crypto') }}</div>
                <div class="col-md-5" data-label-sm="{{ t('compliance_rates_table_heading_fee') }}">{{ $rateTemplate->cryptoIncoming($currency) ?? 0 }}% /{{ $rateTemplate->cryptoOutgoing($currency) ?? 0 }}%</div>
            </div>
            <div class="row bordered complianceRateTableRow">
                <div class="col-md-7" data-label-sm="{{ t('compliance_rates_table_heading_type') }}">{{ t('compliance_rates_blockchain') }}</div>
                <div class="col-md-5" data-label-sm="{{ t('compliance_rates_table_heading_fee') }}">{{ formatMoney($rateTemplate->blockchainIncoming($currency), $currency) ?? 0 }} /{{ formatMoney($rateTemplate->blockchainOutgoing($currency), $currency) ?? 0 }} {{ $currency }}</div>
            </div>
        </div>
    </div>
</div>
