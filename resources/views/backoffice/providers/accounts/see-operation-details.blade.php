@extends('backoffice.layouts.backoffice')
@section('title', t('operation') . '#' .$operation->operation_id)

@section('content')
    <div class="row mb-5 pb-5">
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
    <h4>
        @if(!empty($account))
            <a href="{{ route('dashboard.account', ['account' => $account->id]) }}" class="text-dark">Provider
                ID {{ $account->account_id }}</a> - {{ $account->provider->name }}
        @else
            <p>{{ t('provider_trx') . ' #' . $operation->operation_id }} </p>
        @endif
    </h4>


    @include('backoffice.partials.session-message')

    <div class="row">
        <div class="col-md-6">
            <div class="row pink-border pt-3">
                <div class="col-md-3 text-center px-3 ml-3">
                    <h6 class="d-block"> {{ t('type') }} </h6>
                    <p class="d-block">
                        {{ $operation->getOperationMethodName() }}
                    </p>
                    <h6 class="d-block">{{ t('amount') }}</h6>
                    <p class="d-block">{{ moneyFormatWithCurrency($operation->from_currency,$operation->amount)  ?? '-'}}</p>
                    <h6 class="d-block">{{ t('amount_euro') }}</h6>
                    <p class="d-block">{{ eur_format($operation->amount_in_euro)  ?? '-'}}</p>
                </div>

                <div class="col-md-3 text-center px-3 ml-3" >
                    <h6 class="d-block">{{ t('from') }}</h6>
                    <p class="d-block">{{ $operation->fromAccount->name ?? '-' }}</p>
                    <h6 class="d-block">{{ t('from_currency') }}</h6>
                    <p class="d-block">{{ $operation->from_currency ?? '-' }}</p>
                </div>

                <div class="col-md-3 text-center px-3 ml-3">
                    <h6 class="d-block">{{ t('to') }}</h6>
                    <p class="d-block" >{{ $operation->toAccount->name  ?? '-'}}</p>
                    <h6 class="d-block">{{ t('to_currency') }}</h6>
                    <p class="d-block">{{ strtoupper($operation->to_currency)  ?? '-' }}</p>
                </div>
            </div>
        </div>
        <div class="col-md-2">
            <div class="row">
                <div class="col-md-12">
                    <h6 class="d-block">{{ t('date') }}</h6>
                    <p class="d-block">{{ $operation->created_at }}</p>
                    <h4 class="d-block">{{ t('status') }}</h4>
                    <p class=" @if($operation->status == \App\Enums\OperationStatuses::SUCCESSFUL) text-success @else text-danger @endif d-block">{{ \App\Enums\OperationStatuses::getName($operation->status) }}</p>
                    @if($currentAdmin->can(\App\Enums\BUserPermissions::CHANGE_OPERATION_STATUS) || $currentAdmin->is_super_admin)

                        <button class="btn btn-lg btn-primary themeBtn register-buttons mb-4 mb-md-0 round-border"
                                data-toggle="modal" data-target="#changeOperationStatus"
                                type="submit">{{ t('change_status') }}
                        </button>
                        @include('backoffice.transactions._change-status')
                    @endif
                </div>
            </div>
        </div>
    </div> <br><br><br>

    @include('backoffice.partials.transactions.transaction-history')

    <script>
        function showTrxBankDetails($id) {
            $('.commission-row').attr('hidden', true)
            $('.commission-name').attr('hidden', true)
            $.ajax({
                type: "get",
                url: "{{ route('backoffice.transactions.details') }}",
                data: {
                    'transaction_id': $id,
                },
                success: function (response) {

                    let trx = response.transaction;
                    $('#transactionDetail .exchange-rate').attr('hidden', 'hidden');
                    $('#transactionDetail .to-currency').attr('hidden', 'hidden');
                    $('#transactionDetail .exchange-api').attr('hidden', 'hidden');
                    $('#transactionDetail .to-fee-percent').attr('hidden', false);
                    $('#transactionDetail .to-fee').attr('hidden', false);
                    $('#transactionDetail .to-fee-min').attr('hidden', false);
                    $('#transactionDetail .exchange-fee-min').val(trx.from_commission ? trx.from_commission.min_commission : '');
                    $('#transactionDetail .from-fee-min').text('From fee  minimum');
                    $('#transactionDetail .exchange-fee-percent').text('From fee %');
                    $('#transactionDetail .exchange-fee').text('From fee');
                    $('#transactionDetail .from-fee-minimum').removeClass('exchangeCaseStyle');
                    $('#transactionDetail .exchange-fee').val(trx.from_commission ? trx.from_commission.fixed_commission : '');

                    $('#transactionDetail .datepicker').val(trx.commit_date ?? '');
                    $('#transactionDetail .transaction-type').val(response.trxType ?? '');
                    $('#transactionDetail .from-type').val(response.fromType ?? '');
                    $('#transactionDetail .from-account').val(trx.from_account.name ?? '');
                    $('#transactionDetail .to-type').val(response.toType ?? '');
                    $('#transactionDetail .to-account').val(trx.to_account.name ?? '');
                    $('#transactionDetail .from-currency').val(trx.from_account.currency ?? '');
                    $('#transactionDetail .from-amount').val(trx.trans_amount ?? '');
                    $('#transactionDetail .exchange-fee-percent').val(trx.from_commission ? trx.from_commission.percent_commission : '');
                    $('#transactionDetail .to-fee-percent').val(trx.to_commission ? trx.to_commission.percent_commission : '');
                    $('#transactionDetail .to-fee').val(trx.to_commission ? trx.to_commission.fixed_commission : '');
                    $('#transactionDetail .to-fee-min').val(trx.to_commission ? trx.to_commission.min_commission : '');
                }
            })
        }
    </script>
@endsection
