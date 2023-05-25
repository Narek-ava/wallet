@php $exist = false @endphp
<h5 class="transactionTabHeadingColor mb-5">{{ $heading }}</h5>
<div class="row">
    <div class="col-md-1 textBold">{{ t('transaction_history_table_heading_number') }}</div>
    <div class="col-md-1 textBold">{{ t('transaction_history_table_client_id') }}</div>
    <div class="col-md-2 textBold">{{ t('transaction_history_table_heading_date_time') }}</div>
    <div class="col-md-1 textBold">{{ t('transaction_history_table_heading_amount') }}</div>
    <div class="col-md-2 textBold">{{ t('transaction_history_table_heading_type') }}</div>
    <div class="col-md-1 textBold">{{ t('transaction_history_table_heading_status') }}</div>
    <div class="col-md-1 textBold">{{ t('transaction_history_table_heading_substatus') }}</div>
    <div class="col-md-1 textBold">{{ t('transaction_history_table_heading_project') }}</div>
    <div class="col-md-1 textBold">{{ t('transaction_history_table_heading_details') }}</div>
    <div class="col-md-12">
        @foreach($operations as $operation)
            @if((isset($operation->cProfile->profile_id) || $operation->operation_type === \App\Enums\OperationOperationType::TYPE_SYSTEM_FEE_WITHDRAW) && $operation->status == $status)
                @php $exist = true @endphp
                <div class="row backofficeTransactionHistoryItem">
                    <div class="col-md-1">{{ $operation->operation_id }}</div>
                    <div class="col-md-1">{{ $operation->cProfile->profile_id ?? '' }}</div>
                    <div class="col-md-2">{{ $operation->updated_at }}</div>
                    <div class="col-md-1">
                        {{generalMoneyFormat($operation->amount,$operation->from_currency)}}
                    </div>
                    <div class="col-md-2">
                        {{ $operation->getOperationType() }}
                    </div>
                    <div class="col-md-1">{{ \App\Enums\OperationStatuses::getName($operation->status)}}</div>
                    <div class="col-md-1">{{ \App\Enums\OperationSubStatuses::getName($operation->substatus)}}</div>
                    <div class="col-md-1">{{ $operation->cProfile->cUser->project->name ?? '' }}</div>
                    <div class="col-md-2">
                        @if(in_array($operation->operation_type, [\App\Enums\OperationOperationType::TYPE_WITHDRAW_CRYPTO, \App\Enums\OperationOperationType::TYPE_TOP_UP_CRYPTO, \App\Enums\OperationOperationType::TYPE_WITHDRAW_CRYPTO_PF, \App\Enums\OperationOperationType::TYPE_TOP_UP_CRYPTO_PF, \App\Enums\OperationOperationType::TYPE_TOP_UP_CRYPTO_EXTERNAL_PF]))
                            <a href="{{ route('backoffice.withdraw.crypto.transaction', $operation->id) }}">{{ t('see_details_link') }}</a>
                        @elseif($operation->operation_type == \App\Enums\OperationOperationType::TYPE_CRYPTO_TO_CRYPTO_PF)
                            <a href="{{ route('backoffice.topup.crypto.to.crypto.pf.transaction', $operation->id) }}">{{ t('see_details_link') }}</a>

                        @elseif($operation->operation_type == \App\Enums\OperationOperationType::TYPE_WITHDRAW_WIRE_SEPA || $operation->operation_type == \App\Enums\OperationOperationType::TYPE_WITHDRAW_WIRE_SWIFT)
                            <a href="{{ route('backoffice.withdraw.wire.transaction', $operation->id) }}">{{ t('see_details_link') }}</a>


                        @elseif($operation->operation_type == \App\Enums\OperationOperationType::TYPE_FIAT_TOP_UP_BY_WIRE)
                            <a href="{{ route('backoffice.top.up.fiat.wire.show.transaction', $operation->id) }}">{{ t('see_details_link') }}</a>

                        @elseif($operation->operation_type == \App\Enums\OperationOperationType::TYPE_BUY_CRYPTO_FROM_FIAT)
                            <a href="{{ route('backoffice.buy.crypto.from.fiat.transaction', $operation->id) }}">{{ t('see_details_link') }}</a>
                        @elseif($operation->operation_type == \App\Enums\OperationOperationType::TYPE_WITHDRAW_FROM_FIAT_WALLET)
                            <a href="{{ route('backoffice.withdraw.from.fiat.transaction', $operation->id) }}">{{ t('see_details_link') }}</a>
                        @elseif($operation->operation_type == \App\Enums\OperationOperationType::TYPE_BUY_FIAT_FROM_CRYPTO)
                            <a href="{{ route('backoffice.buy.fiat.from.crypto.transaction', $operation->id) }}">{{ t('see_details_link') }}</a>

                            {{--                        @elseif(in_array($operation->operation_type,[\App\Enums\OperationOperationType::TYPE_PROVIDER_WITHDRAW,  \App\Enums\OperationOperationType::TYPE_PROVIDER_TOP_UP]))--}}
{{--                            <a href="{{ route('dashboard.provider.operation.details', ['operation' => $operation->id]) }}">{{ t('see_details_link') }}</a>--}}

                        @elseif(in_array($operation->operation_type, [\App\Enums\OperationOperationType::TYPE_CARD, \App\Enums\OperationOperationType::MERCHANT_PAYMENT, \App\Enums\OperationOperationType::TYPE_CARD_PF]))
                            <a href="{{ route('backoffice.card.transaction', $operation) }}">{{ t('see_details_link') }}</a>
                        @elseif(in_array($operation->operation_type, [\App\Enums\OperationOperationType::TYPE_CARD_ORDER_PAYMENT_SEPA, \App\Enums\OperationOperationType::TYPE_CARD_ORDER_PAYMENT_SWIFT, \App\Enums\OperationOperationType::TYPE_CARD_ORDER_PAYMENT_BANK_CARD]))
                            <a href="{{ route('backoffice.card.order.transaction', $operation) }}">{{ t('see_details_link') }}</a>

                        @elseif($operation->operation_type == \App\Enums\OperationOperationType::TYPE_CARD_ORDER_PAYMENT_CRYPTO)
                            <a href="{{ route('backoffice.card.order.crypto.transaction', $operation) }}">{{ t('see_details_link') }}</a>

                         @elseif(in_array($operation->operation_type, [\App\Enums\OperationOperationType::TYPE_SYSTEM_FEE_WITHDRAW]))
                            <a href="{{ route('backoffice.system.fee.withdraw.transaction', $operation) }}">{{ t('see_details_link') }}</a>
                        @else
                            <a href="{{ route('backoffice.show.transaction', $operation->id) }}">{{ t('see_details_link') }}</a>
                        @endif
                    </div>
                </div>
            @endif
        @endforeach
        @if($exist)
            {{ $operations->appends(request()->all())->fragment($tab)->links() }}
        @endif
    </div>
</div>
