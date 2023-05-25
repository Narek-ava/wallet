<div class="col-md-12 display-none details{{ $operation->id }}">
    <hr class="hr-style">
    <div class="col-md-12 mt-3 fs14">
        <div class="row">
            <div class="col-md-2 mt-4">
                <span class="activeLink">{{ t('comment') }}</span>
                <div class="operation-comment">
                    @if($operation->comment)
                        {{ $operation->comment }}
                    @else
                        <span>{{ t('top_up_wire_no_comment') }}</span>
                    @endif
                </div>
            </div>
            @if($operation->operation_type == \App\Enums\OperationOperationType::TYPE_CARD_ORDER_PAYMENT_CRYPTO)
            <div class="col-md-2 mt-4">
                <span class="activeLink">{{ t('type') }}</span><br>{{ $operation->getOperationType() ?? '-'}}<br><br>
                <span class="activeLink">{{ t('withdrawal_currency') }}</span><br>{{ $operation->from_currency ?? '-' }}<br><br>
            </div>
            <div class="col-md-2 mt-4">
                <span class="activeLink">{{ t('withdrawal_exchange_to') }}</span><br>{{ strtoupper($operation->to_currency)  ?? '-' }}<br><br>
                <span class="activeLink">{{ t('withdrawal_amount_euro') }}</span><br> {{ eur_format($operation->amount_in_euro ?? null) }}<br><br>

            </div>
            <div class="col-md-2 mt-4">
                <span class="activeLink">{{ t('withdrawal_blockchain_fee') }}</span><br>{{ formatMoney($operation->blockchainFee, $operation->from_currency) ?? 0 }}<br><br>
                <span class="activeLink">Report</span><br>@if($operation->status == \App\Enums\OperationStatuses::SUCCESSFUL)
                    <a href="{{ route('cabinet.download.transaction.report.pdf', ['operation' => $operation->id]) }}"><img src="{{ asset('/cratos.theme/images/pdf.png') }}" width="20" class="pdf-icon pb-1">{{  t('withdrawal_report') }}</a>
                @else - @endif<br><br>
            </div>
            <div class="col-md-2 mt-4">
                <span class="activeLink">{{ t('withdrawal_exchange_rate') }}</span><br>1 {{ $operation->to_currency }} = {{ $operation->getExchangeTransaction() ? round($operation->getExchangeTransaction()->exchange_rate, 2) : '-' }}<br><br>
                @if ($operation->getCryptoExplorerUrl())
                    <a href="{{ $operation->getCryptoExplorerUrl() }}" target="_blank">{{ t('ui_view_transaction_details') }}</a>
                @endif
            </div>
            @else
                <div class="col-md-2 mt-4">
                    <span class="activeLink">{{ t('type') }}</span><br>{{$operation->getOperationType() ?? '-' }}<br><br>
                </div>
                <div class="col-md-2 mt-4">
                    <span class="activeLink">{{ t('currency') }}</span><br>{{ $operation->from_currency ?? '-' }}<br><br>
                    <span class="activeLink">{{ t('amount_euro') }}</span><br>{{ eur_format($operation->amount_in_euro ?? null) }}<br><br>
{{--                    <span class="activeLink">{{ t('top_up_fee') }}</span><br>{{ $operation->getTopUpFeeWithMinCommission() }}<br><br>--}}

                </div>
                <div class="col-md-2 mt-4">
                    <span class="activeLink">{{ t('bank_country') }}</span><br>{{ $operation->fromAccount ? \App\Models\Country::getCountryNameByCode($operation->fromAccount->country) : '-' }}<br><br>
                    <span class="activeLink">{{ t('cratos_bank', ['name' => $operation->cProfile->cUser->project->name ?? '']) }}</span>
                    <a href="{{ route('client.download.pdf.operation', ['operationId' => $operation->id]) }}" class="d-block">
                        <img src="{{ asset('/cratos.theme/images/pdf.png') }}" width="20" class="pdf-icon pb-1">
                        {{$operation->providerAccount->name ?? ''}}
                    </a>

                </div>
                <div class="col-md-2 mt-4">
                    <span class="activeLink">Report</span><br>
                    @if($operation->status == \App\Enums\OperationStatuses::SUCCESSFUL)
                        <a href="{{ route('cabinet.download.transaction.report.pdf', ['operation' => $operation->id]) }}">
                            <img src="{{ asset('/cratos.theme/images/pdf.png') }}" width="20" class="pdf-icon pb-1">
                            {{  t('ui_wire_operation_report') }}
                        </a>
                    @else - @endif<br><br>
                </div>
            @endif
        </div>
    </div>
</div>
