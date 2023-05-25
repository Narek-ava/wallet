<div class="row">
    <div class="col-md-12">
        <div class="row complianceRatesTableWidth d-none d-md-flex">
            <div class="col-md-4 textBold">{{ t('compliance_rates_limits_table_heading_level') }}</div>
            <div class="col-md-4 textBold">{{ t('compliance_rates_limits_table_heading_transaction_limit') }}</div>
            <div class="col-md-4 textBold">{{ t('compliance_rates_limits_table_heading_monthly_limit') }}</div>
        </div>
        <div class="row mt-3 complianceRatesTableWidth">
            <div class="col-md-12 card-default br20">
                <div class="row bordered complianceRateTableRow">
                    <div class="col-md-4"
                         data-label-sm="{{ t('compliance_rates_limits_table_heading_level') }}">{{ t('compliance_rates_verification') }} {{ t('enum_compliance_level_level_0')}}</div>
                    <div class="col-md-4"
                         data-label-sm="{{ t('compliance_rates_limits_table_heading_transaction_limit') }}">{{ eur_format($rateTemplate->limits[\App\Enums\RatesCommissions::LIMIT_LEVEL_0]['transaction_amount_max']?? '-' )}}</div>
                    <div class="col-md-4"
                         data-label-sm="{{ t('compliance_rates_limits_table_heading_monthly_limit') }}">{{ eur_format($rateTemplate->limits[\App\Enums\RatesCommissions::LIMIT_LEVEL_0]['monthly_amount_max'] ?? '-') }}</div>
                </div>
                <div class="row bordered complianceRateTableRow">
                    <div class="col-md-4"
                         data-label-sm="{{ t('compliance_rates_limits_table_heading_level') }}">{{ t('compliance_rates_verification') }} {{ t('enum_compliance_level_level_1')}}</div>
                    <div class="col-md-4"
                         data-label-sm="{{ t('compliance_rates_limits_table_heading_transaction_limit') }}">{{ eur_format($rateTemplate->limits[\App\Enums\RatesCommissions::LIMIT_LEVEL_1]['transaction_amount_max']?? '-' )}}</div>
                    <div class="col-md-4"
                         data-label-sm="{{ t('compliance_rates_limits_table_heading_monthly_limit') }}">{{ eur_format($rateTemplate->limits[\App\Enums\RatesCommissions::LIMIT_LEVEL_1]['monthly_amount_max'] ?? '-') }}</div>
                </div>
                <div class="row bordered complianceRateTableRow">
                    <div class="col-md-4"
                         data-label-sm="{{ t('compliance_rates_limits_table_heading_level') }}">{{ t('compliance_rates_verification') }} {{ t('enum_compliance_level_level_2')}}</div>
                    <div class="col-md-4"
                         data-label-sm="{{ t('compliance_rates_limits_table_heading_transaction_limit') }}">{{eur_format($rateTemplate->limits[\App\Enums\RatesCommissions::LIMIT_LEVEL_2]['transaction_amount_max'] ?? '-')}}</div>
                    <div class="col-md-4"
                         data-label-sm="{{ t('compliance_rates_limits_table_heading_monthly_limit') }}">{{ eur_format($rateTemplate->limits[\App\Enums\RatesCommissions::LIMIT_LEVEL_2]['monthly_amount_max'] ?? '-')}}</div>
                </div>
                <div class="row complianceRateTableRow">
                    <div class="col-md-4"
                         data-label-sm="{{ t('compliance_rates_limits_table_heading_level') }}">{{ t('compliance_rates_verification') }} {{ t('enum_compliance_level_level_3')}}</div>
                    <div class="col-md-4"
                         data-label-sm="{{ t('compliance_rates_limits_table_heading_transaction_limit') }}">{{eur_format($rateTemplate->limits[\App\Enums\RatesCommissions::LIMIT_LEVEL_3]['transaction_amount_max'] ?? '-')}}</div>
                    <div class="col-md-4"
                         data-label-sm="{{ t('compliance_rates_limits_table_heading_monthly_limit') }}">{{eur_format($rateTemplate->limits[\App\Enums\RatesCommissions::LIMIT_LEVEL_3]['monthly_amount_max'] ?? '-') }}</div>
                </div>
            </div>
        </div>
    </div>
</div>
