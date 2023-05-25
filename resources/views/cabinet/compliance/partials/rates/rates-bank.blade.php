<div class="tab-pane fade @if($currency == \App\Enums\Currency::CURRENCY_USD)show active @endif"
     id="{{ strtolower($currency) }}" role="tabpanel" aria-labelledby="{{strtolower($currency)}}Link">
    <div class="row complianceRatesTableWidth d-none d-md-flex">
        <div class="col-md-7 textBold">{{ t('cards_conditions_overview') }}</div>
    </div>
    <div class="row mt-3 complianceRatesTableWidth">
        <div class="col-md-12 card-default br20">
            <div class="row bordered complianceRateTableRow">
                <div class="col-md-7"
                     data-label-sm="{{ t('compliance_rates_table_heading_type') }}">{{ t('compliance_rates_table_heading_type') }}</div>
                <div class="col-md-5"
                     data-label-sm="{{ t('compliance_rates_table_heading_fee') }}">{{ t('compliance_rates_table_heading_fee') }}</div>
            </div>
            <div class="row bordered complianceRateTableRow">
                <div class="col-md-7"
                     data-label-sm="{{ t('compliance_rates_table_heading_type') }}">{{ $bankCardRateTemplate->overview_type ?? '' }}</div>
                <div class="col-md-5"
                     data-label-sm="{{ t('compliance_rates_table_heading_fee') }}"> {{  $bankCardRateTemplate->overview_fee ?? '' }}</div>
            </div>
        </div>
    </div>

    <br>
    <div class="row complianceRatesTableWidth d-none d-md-flex">
        <div class="col-md-7 textBold">{{ t('cards_conditions_transactions') }}</div>
    </div>
    <div class="row mt-3 complianceRatesTableWidth">
        <div class="col-md-12 card-default br20">
            <div class="row bordered complianceRateTableRow">
                <div class="col-md-7"
                     data-label-sm="{{ t('compliance_rates_table_heading_type') }}">{{ t('compliance_rates_table_heading_type') }}</div>
                <div class="col-md-5"
                     data-label-sm="{{ t('compliance_rates_table_heading_fee') }}">{{ t('compliance_rates_table_heading_fee') }}</div>
            </div>
            <div class="row bordered complianceRateTableRow">
                <div class="col-md-7"
                     data-label-sm="{{ t('compliance_rates_table_heading_type') }}"> {{ $bankCardRateTemplate->transactions_type ?? '' }} </div>
                <div class="col-md-5"
                     data-label-sm="{{ t('compliance_rates_table_heading_fee') }}">  {{ $bankCardRateTemplate->transactions_fee ?? '' }} </div>
            </div>
        </div>
    </div>

    <br>
    <div class="row complianceRatesTableWidth d-none d-md-flex">
        <div class="col-md-7 textBold">{{ t('fees') }}</div>
    </div>
    <div class="row mt-3 complianceRatesTableWidth">
        <div class="col-md-12 card-default br20">
            <div class="row bordered complianceRateTableRow">
                <div class="col-md-7"
                     data-label-sm="{{ t('compliance_rates_table_heading_type') }}">{{ t('compliance_rates_table_heading_type') }}</div>
                <div class="col-md-5"
                     data-label-sm="{{ t('compliance_rates_table_heading_fee') }}">{{ t('compliance_rates_table_heading_fee') }}</div>
            </div>
            <div class="row bordered complianceRateTableRow">
                <div class="col-md-7"
                     data-label-sm="{{ t('compliance_rates_table_heading_type') }}">{{ $bankCardRateTemplate->fees_type ?? '' }}  </div>
                <div class="col-md-5"
                     data-label-sm="{{ t('compliance_rates_table_heading_fee') }}"> {{ $bankCardRateTemplate->fees_fee ?? '' }} </div>
            </div>
        </div>
    </div>
</div>
