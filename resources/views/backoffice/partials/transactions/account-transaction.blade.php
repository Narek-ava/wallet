@php $exist = false @endphp
<h1 class="transactionTabHeadingColor">{{ $heading }}</h1>
<div class="row">
    <div class="col-md-2 textBold">{{ t('transaction_history_table_heading_number') }}</div>
    <div class="col-md-2 textBold">{{ t('transaction_history_table_heading_date_time') }}</div>
    <div class="col-md-2 textBold">{{ t('transaction_history_table_heading_amount') }}</div>
    <div class="col-md-2 textBold">{{ t('transaction_history_table_heading_type') }}</div>
    <div class="col-md-2 textBold">{{ t('transaction_history_table_heading_status') }}</div>
    <div class="col-md-2 textBold">{{ t('transaction_history_table_heading_details') }}</div>
    <div class="col-md-12">
        @foreach($filteredOperations ?? $operations as $operation)
            @php $exist = true @endphp
            <div class="row backofficeTransactionHistoryItem">
                <div class="col-md-2">{{ $operation->operation_id }}</div>
                <div class="col-md-2">{{ $operation->updated_at }}</div>
                <div class="col-md-2">{{ $operation->amount }}</div>
                <div class="col-md-2">
                    {{ $operation->getOperationType() }}
                </div>
                <div class="col-md-2">{{ \App\Enums\OperationStatuses::NAMES[$operation->status] ?? null }}</div>
                <div class="col-md-2">
                    @if(in_array($operation->operation_type, [\App\Enums\OperationOperationType::TYPE_WITHDRAW_CRYPTO, \App\Enums\OperationOperationType::TYPE_TOP_UP_CRYPTO, \App\Enums\OperationOperationType::TYPE_WITHDRAW_CRYPTO_PF, \App\Enums\OperationOperationType::TYPE_TOP_UP_CRYPTO_PF, \App\Enums\OperationOperationType::TYPE_TOP_UP_CRYPTO_EXTERNAL_PF]))
                        <a href="{{ route('backoffice.withdraw.crypto.transaction', $operation->id) }}">{{ t('see_details_link') }}</a>
                    @elseif($operation->operation_type == \App\Enums\OperationOperationType::TYPE_CRYPTO_TO_CRYPTO_PF)
                        <a href="{{ route('backoffice.topup.crypto.to.crypto.pf.transaction', $operation->id) }}">{{ t('see_details_link') }}</a>
                    @elseif($operation->operation_type == \App\Enums\OperationOperationType::TYPE_WITHDRAW_WIRE_SEPA || $operation->operation_type == \App\Enums\OperationOperationType::TYPE_WITHDRAW_WIRE_SWIFT)
                        <a href="{{ route('backoffice.withdraw.wire.transaction', $operation->id) }}">{{ t('see_details_link') }}</a>
                    @elseif(in_array($operation->operation_type, [\App\Enums\OperationOperationType::TYPE_CARD, \App\Enums\OperationOperationType::MERCHANT_PAYMENT, \App\Enums\OperationOperationType::TYPE_CARD_PF]))
                        <a href="{{ route('backoffice.card.transaction', $operation) }}">{{ t('see_details_link') }}</a>
                    @else
                        <a href="{{ route('backoffice.show.transaction', $operation->id) }}">{{ t('see_details_link') }}</a>
                    @endif
                </div>
            </div>
        @endforeach
        {!! $filteredOperations->appends(request()->query())->links() !!}
    </div>
</div>

