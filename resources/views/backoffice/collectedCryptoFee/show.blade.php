@extends('backoffice.layouts.backoffice')
@section('title', t('operation') . '#' . $operation->operation_id)

@section('content')
    <div class="row mb-4 pb-4">
        <div class="col-md-12">
            <h2 class="mb-3 large-heading-section">{{ t('operation') . ' #' . $operation->operation_id }}</h2>
            <div class="row">
                <div class="col-lg-5 d-flex justify-content-between">
                    <div class="balance mb-4">
                        {{ t('backoffice_profile_page_header_body') }}
                    </div>
                </div>
                @include('cabinet.partials.notification', ['notify' => getNotification(\Illuminate\Support\Facades\Auth::id()), 'admin' => true])
            </div>
        </div>
    </div>

    <h4 class="mb-5 pb-3"> Operation {{ $operation->operation_id }}
        @if($operation->substatus)
            <button type="button" class="btn themeBtnOnlyBorder ml-3" title="{{$operation->error_message}}">
                {{ \App\Enums\OperationSubStatuses::getName($operation->substatus) }}
            </button>
        @endif
    </h4>

    @include('backoffice.partials.session-message')

    <div class="row pl-3">
        <div class="col-md-10">
            <div class="row common-shadow-theme pt-4 pb-3">
                <div class="col-md-4 px-3 ml-3">
                    <h6 class="d-block">{{ t('deposit_type') }}</h6>
                    <p class="d-block">{{ \App\Enums\OperationType::getName(\App\Enums\OperationType::SYSTEM_FEE_WITHDRAW) }}</p>
                    <h6 class="d-block">{{ t('currency') }}</h6>
                    <p class="d-block">{{ $operation->from_currency }}</p>
                    @if ($operation->getCryptoExplorerUrl())
                        <a href="{{ $operation->getCryptoExplorerUrl() }}" target="_blank">{{ t('ui_view_transaction_details') }}</a>
                    @endif
                </div>
                <div class="col-md-4 px-3 ml-3">
                    <h6 class="d-block">{{ t('amount') }}</h6>
                    <p class="d-block">{{ $operation->amount }}</p>
                    <h6 class="d-block">{{ t('credited') }}</h6>
                    <p class="d-block"> {{ $operation->status == \App\Enums\OperationStatuses::SUCCESSFUL ? generalMoneyFormat($credited, $operation->to_currency) . ' ' . $operation->getOperationCryptoCurrency() : '-' }} </p>
                </div>
            </div>
        </div>

        <div class="col-md-2">
            <p class="row">
            <div class="col-md-12">
                <h6 class="d-block">{{ t('date') }}</h6>
                <p class="d-block">{{ $operation->created_at }}</p>
                <h6 class="d-block">{{ t('status') }}</h6>
                <p class="d-block @if($operation->status == \App\Enums\OperationStatuses::SUCCESSFUL) text-success @else text-danger @endif">
                    {{ \App\Enums\OperationStatuses::getName($operation->status) }}</p>
                @if($operation->status == \App\Enums\OperationStatuses::RETURNED)
                    <h6 class="d-block">{{ t('substatus') }}</h6>
                    <p class="text-danger d-block">{{ \App\Enums\OperationSubStatuses::getName($operation->substatus) }}</p>
                @endif
                <button class="btn btn-lg btn-primary themeBtn register-buttons mb-4 mb-md-0 round-border"
                        data-toggle="modal" data-target="#changeOperationStatus"
                        type="submit">{{ t('change_status') }}
                </button>

                <!-- Button trigger modal -->
                @include('backoffice.transactions._change-status')
            </div>
        </div>
    </div>
    @if($operation->from_account)
        <div class="row mt-5">
            <h2 class="mt-1 ml-4">{{ t('transactions') }}</h2>
            @include('backoffice.partials.transactions.transaction-history')

            <h2 class="mt-1 ml-4">{{ t('collected_transactions') }}</h2>
            @include('backoffice.partials.transactions.transaction-history', ['transactions' => $collectedTransactions])
        </div>
    @endif
@endsection
@section('scripts')
    <script>
        function showTrxBankDetails($id) {
            $.ajaxSetup({
                headers: {
                    'X-CSRF-TOKEN': "{{ csrf_token() }}"
                }
            });
            $.ajax({
                type: "post",
                url: "{{ route('backoffice.system.fee.withdraw.transaction.details') }}",
                data: {
                    'transaction_id': $id,
                },
                success: function (response) {
                    let trx = response.transaction;
                    //Crypto trx
                    $('#transactionDetail .to-fee-percent').attr('hidden', 'hidden');
                    $('#transactionDetail .to-fee').attr('hidden', 'hidden');
                    $('#transactionDetail .to-fee-min').attr('hidden', 'hidden');
                    $('#transactionDetail .exchange-fee').val(trx.from_commission ? trx.from_commission.fixed_commission : '');
                    $('#transactionDetail .from-fee-min').text('Blockchain fee');
                    $('#transactionDetail .exchange-fee-min').val(trx.from_commission ? trx.from_commission.blockchain_fee : '');
                    $('#transactionDetail .exchange-fee-percent').text('From fee %');
                    $('#transactionDetail .exchange-fee').text('From fee');
                    $('#transactionDetail .from-fee-minimum').addClass('exchangeCaseStyle');
                    $('#transactionDetail .crypto-address').attr('hidden', false);
                    if (response.toCryptoAccountDetail) {
                        $('.crypto-address').val(response.toCryptoAccountDetail.address ?? '');
                    }


                    $('#transactionDetail .datepicker').val(trx.commit_date ?? '');
                    $('#transactionDetail .transaction-type').val(response.trxType ?? '');
                    $('#transactionDetail .from-type').val(response.fromType ?? '');
                    $('#transactionDetail .from-account').val(trx.from_account.name ?? '');
                    $('#transactionDetail .to-type').val(response.toType ?? '');
                    $('#transactionDetail .to-account').val(trx.to_account.name ?? '');
                    $('#transactionDetail .from-currency').val(trx.from_account.currency ?? '');
                    $('#transactionDetail .from-amount').val(trx.trans_amount ?? '');
                    if (response.cryptoToCryptoDetails) {
                        $('#transactionDetail .exchange-fee-percent').val(response.cryptoToCryptoDetails.incomingFee ?? '');
                    } else {
                        $('#transactionDetail .exchange-fee-percent').val(trx.from_commission ? trx.from_commission.percent_commission : '');
                    }
                    $('#transactionDetail .to-fee-percent').val(trx.to_commission ? trx.to_commission.percent_commission : '');
                    $('#transactionDetail .to-fee').val(trx.to_commission ? trx.to_commission.fixed_commission : '');
                    $('#transactionDetail .to-fee-min').val(trx.to_commission ? trx.to_commission.min_commission : '');

                }
                })
        }
    </script>
@endsection
