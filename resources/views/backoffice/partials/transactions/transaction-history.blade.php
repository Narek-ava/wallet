<div class="col-md-12">
    <div class=" d-block mb-5">
        @if(count($transactions) != 0)
            <div class="col-md-12 pt-4">
                <div class="row d-none d-md-flex">
                    <div class="col-md-1 activeLink">N#</div>
                    <div class="col-md-1 activeLink">{{ t('type') }}</div>
                    <div class="col-md-1 activeLink">{{ t('date') }}</div>
                    <div class="col-md-2 activeLink text-center">{{ t('from') }}</div>
                    <div class="col-md-1 activeLink">{{ t('amount') }}</div>
                    <div class="col-md-1 activeLink">{{ t('currency') }}</div>
                    <div class="col-md-2 activeLink text-center">{{ t('to') }}</div>
                    <div class="col-md-1 activeLink">{{ t('status') }}</div>
                    <div class="col-md-1 activeLink" style="max-width: 150px;">{{ t('details') }}</div>
                </div>
            </div>
            @foreach($transactions as $index => $transaction)
                <div class="col-md-12 mt-4 history-element bold">
                    <div class="row align-items-center">
                        <div title="{{ $transaction->transaction_id }}" class="m col-md-1 history-element-item" data-label-sm="N#">{{ $loop->iteration }}</div>
                        <div class="col-md-1 history-element-item" data-label-sm="{{ t('type') }}">{{ \App\Enums\TransactionType::getName($transaction->type) ?? '-' }}</div>
                        <div title="{{$transaction->created_at}}" class="col-md-1 history-element-item" data-label-sm="{{ t('date') }}">{{ $transaction->commit_date ? date('d.m.Y', strtotime($transaction->commit_date)) : '-' }}</div>

                        <div class="col-md-2 history-element-item" data-label-sm="{{ t('from') }}"> {{ $transaction->fromAccount->cardAccountDetail->card_number ?? ($transaction->fromAccount->name ?? '-') }} </div>
                        <div class="position-relative">
                        <div class="amountHover"  @if($transaction->type == \App\Enums\TransactionType::EXCHANGE_TRX)
                             @endif class="col-md-10 history-element-item " data-label-sm="{{ t('amount') }}">{{ generalMoneyFormat($transaction->trans_amount, $transaction->fromAccount->currency) ?? '-' }}</div>
                        <div class="transactionAmount ">{{generalMoneyFormat($transaction->recipient_amount,$transaction->fromAccount->currency)}}</div>
                        </div>
                        <div class="col-md-1 history-element-item" data-label-sm="{{ t('currency') }}">{{ $transaction->fromAccount->currency ?? '-' }}</div>
                        <div class="col-md-2 history-element-item" data-label-sm="{{ t('to') }}">{{ $transaction->toAccount->cardAccountDetail->card_number ?? ($transaction->toAccount->name ?? '-') }}</div>
                        <div class="col-md-1 history-element-item" data-label-sm="{{ t('status') }}">{{ \App\Enums\TransactionStatuses::getName($transaction->status) ?? '-'}}</div>

                        <div class="col-md-1 d-block history-element-item activeLink" data-label-sm="{{ t('details') }}" style="max-width: 150px;">
                            @if(($transaction->type == \App\Enums\TransactionType::CRYPTO_TRX ||
                                $transaction->type == \App\Enums\TransactionType::EXCHANGE_TRX)
                                && $transaction->status == \App\Enums\TransactionStatuses::PENDING
                                && config('app.env') == 'local')
                                <form action="{{ route('approve-transaction', $transaction->id) }}" method="post">
                                    @csrf
                                    <button type="submit" class="btn themeBtn mt-2 round-border">
                                        {{ t('approve_transaction') }}
                                    </button>
                                </form>
                            @endif
                                @if(!in_array($transaction->type, [\App\Enums\TransactionType::SYSTEM_FEE, \App\Enums\TransactionType::BLOCKCHAIN_FEE]))
                                    <button type="button" class="btn mt-2 border-none" data-toggle="modal"
                                            style="background-color: transparent"
                                            data-target="#trxDetailsPopup"
                                            onclick="showTrxBankDetails('{{ $transaction->id }}')">
                                        {{ t('see_details') }}
                                    </button>
                                    @include('backoffice.partials.transactions._transaction-detail')
                                @endif
                        </div>
                    </div>
                </div>



                @foreach($transaction->feeChildTransactions as $feeTransaction)
                    <div class="col-md-12 mt-4 history-element">
                        <div class="row align-baseline">
                            <div title="{{ $feeTransaction->transaction_id }}" class="col-md-1 history-element-item" data-label-sm="N#">{{ $loop->parent->iteration. '.' .$loop->iteration }}</div>
                            <div class="col-md-1 history-element-item"
                                 data-label-sm="{{ t('type') }}">{{ \App\Enums\TransactionType::getName($feeTransaction->type) ?? '-' }}</div>
                            <div title="{{$transaction->created_at}}" class="col-md-1 history-element-item"
                                 data-label-sm="{{ t('date') }}">{{ $feeTransaction->commit_date ? date('d.m.Y', strtotime($transaction->commit_date)) : '-' }}</div>
                            <div class="col-md-2 history-element-item"
                                 data-label-sm="{{ t('from') }}">{{ $feeTransaction->fromAccount->name ?? '-' }}</div>
                            <div class="position-relative">
                            <div
                                @if($transaction->type == \App\Enums\TransactionType::EXCHANGE_TRX)
                                @endif class="col-md-2 history-element-item amountHover"
                                data-label-sm="{{ t('amount') }}">{{ generalMoneyFormat($feeTransaction->trans_amount, $feeTransaction->fromAccount->currency) ?? '-' }}</div>
                            <div class="transactionAmount ">{{generalMoneyFormat($feeTransaction->trans_amount, $feeTransaction->fromAccount->currency)}}</div>
                            </div>
                            <div class="col-md-1 history-element-item"
                                 data-label-sm="{{ t('currency') }}">{{ $feeTransaction->fromAccount->currency ?? '-' }}</div>
                            <div class="col-md-2 history-element-item"
                                 data-label-sm="{{ t('to') }}">{{ $feeTransaction->toAccount->name ?? '-'}}</div>
                            <div class="col-md-1 history-element-item"
                                 data-label-sm="{{ t('status') }}">{{ \App\Enums\TransactionStatuses::getName($feeTransaction->status) ?? '-'}}</div>
                            <div class="col-md-1 d-block history-element-item activeLink"
                                 data-label-sm="{{ t('details') }}" style="max-width: 150px;">
                            </div>
                        </div>
                    </div>
                @endforeach
            @endforeach
        @endif
    </div>
    {!! $transactions->appends(request()->query())->links() !!}
</div>
<style>
    .transactionAmount{
        display: none;
        color: #fe3d2b;
        overflow: hidden;
        position: absolute;
    }
    .amountHover{
        max-width: 110px;
        white-space: nowrap;
        overflow: hidden;
        text-overflow: ellipsis;
        width: 100%;
        display: inline-block;
        margin-top: 12px;
    }


</style>
<script>
    let transactionAmount =  document.getElementsByClassName('amountHover');

    function hideOrShow(id,show){
        if(show){
            return document.getElementById(id).style = 'display: block'
        }else {
            return document.getElementById(id).style = 'display: none'
        }
    }

    for(let i = 0; i < transactionAmount.length; i++) {
        (function(index) {
            document.getElementsByClassName('transactionAmount')[index].id = index;

            transactionAmount[index].addEventListener("mouseenter", function(e) {
                hideOrShow(index,"show")
                console.log( e.id);
            })
            transactionAmount[index].addEventListener("mouseleave", function() {
                console.log("Clicked index: " + index);
                hideOrShow(index)

            })
        })(i);
    }
</script>
