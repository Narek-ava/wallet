{{-- возможно, лучше так --}}

<div class="row">

    <br><br><br><br><br><br>

</div>

<div class="row">
    <table id="example" class="table  dt-responsive nowrap" style="width:80%">
        <thead>
        <tr class="common-shadow-theme">
            <th style="width:55%" scope="col">{{ C\_rates('all_transactions_month_limit') }}</th>
            <th colspan="3" class="{{ $rates['css'] }}" style="width:45%">{{ $rates['values']['all_transactions_month_limit'] }} €</th>
        </tr>
        </thead>
    </table>

</div>
<div class="row">

    <div class="table-responsive">
        <table id="example" class="table  dt-responsive nowrap" style="width:80%">
            <thead>
            <tr class="common-shadow-theme">
                <th style="width:55%" scope="col">{{ t('ui_operations_type') }}</th>
                <th style="width:15%" scope="col">{{ t('compliance_rates_limits_table_heading_transaction_limit') }}</th>
                <th style="width:15%" scope="col">{{ t('ui_rate') }}</th>
                <th style="width:15%" scope="col">{{ t('ui_min') }}</th>
            </tr>
            </thead>
            <tbody>

            @foreach((new \App\Services\RatesService)->getOperationTypes() as $oType)
                <tr class="common-shadow-theme">
                    <td>{{ C\_rates($oType) }}</td>
                    <td class="{{ $rates['css'] }}">{{ $rates['values'][$oType . '_limit'] }} €</td>
                    <td class="{{ $rates['css'] }}">{{ $rates['values'][$oType . '_rate'] }} %</td>
                    <td class="{{ $rates['css'] }}">{{ $rates['values'][$oType . '_min'] }} €</td>
                </tr>
            @endforeach

            </tbody>
        </table>

    </div>
</div>

