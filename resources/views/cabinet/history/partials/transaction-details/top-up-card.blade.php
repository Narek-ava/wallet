<div class="display-none details{{ $operation->id }} col-md-12">
    <hr class="hr-style">
    <div class="col-md-12 mt-3 fs14">
        <div class="row">
            <div class="@if(in_array($operation->operation_type, [\App\Enums\OperationOperationType::MERCHANT_PAYMENT, \App\Enums\OperationOperationType::TYPE_CARD_PF])) col-md-2 @else col-md-4 @endif mt-4">
                <span class="activeLink">{{ t('comment') }}</span> <br>
                <div class="operation-comment">
                    <span>{!! $operation->comment !!}</span>
                </div>
            </div>
            <div class="col-md-2 mt-4">
                <span class="activeLink">{{ t('top_up_deposit_type') }}</span><br>{{ \App\Enums\OperationOperationType::getName($operation->operation_type) }}<br><br>
                <span class="activeLink">{{ t('top_up_deposit_currency') }}</span><br>{{ strtoupper($operation->from_currency)  ?? '-' }}<br><br>

                <span class="activeLink">Report</span><br>
                <a href="{{ route('cabinet.download.transaction.report.pdf', ['operation' => $operation->id]) }}">
                    <img src="{{ asset('/cratos.theme/images/pdf.png') }}" width="20" class="pdf-icon pb-1">
                    {{  t('ui_wire_operation_report') }}
                </a>
                <br><br>
</div>
            <div class="col-md-2 mt-4">
                <span class="activeLink">{{ t('exchange_to') }}</span><br>{{ $operation->to_currency  ?? '-' }}<br><br>
                <span class="activeLink">{{ t('card_number') }}</span><br>{{ $operation->fromAccount->cardAccountDetail->card_number  ?? '-' }}<br><br>
                <span class="activeLink">{{ t('transaction_id') }}</span><br>{{ $operation->getCardTransactionReference()  ?? '-' }}<br><br>
            </div>
            <div class="col-md-2 mt-4">
                <span class="activeLink">{{ t('top_up_fee') }}</span><br>{{ $operation->getTopUpFeeWithMinCommission() }}<br><br>
                <span class="activeLink">{{ t('blockchain_fee') }}</span><br>{{ ($operation->getCardTransferBlockchainFee() ?? 0) . ' ' . $operation->to_currency }}<br><br>
            </div>
            <div class="col-md-2 mt-4">
                <span class="activeLink">{{ t('exchange_rate') }}</span><br>1 {{ $operation->to_currency }} = {{ $operation->getExchangeTransaction() ? round($operation->getExchangeTransaction()->exchange_rate, 2) : '-' }}<br><br>
                <span class="activeLink">{{ t('credited') }}</span><br>{{ $operation->credited }}<br><br>
                @if ($operation->getCryptoExplorerUrl())
                    <a href="{{ $operation->getCryptoExplorerUrl() }}" target="_blank">{{ t('ui_view_transaction_details') }}</a>
                @endif
            </div>
        </div>
    </div>
</div>
