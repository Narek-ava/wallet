@extends('backoffice.layouts.backoffice')
@section('title', t('operation') . '#' .$operation->operation_id)

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

    <h4><a href="{{ route('backoffice.profile', $cProfile->id) }}" class="text-dark">{{t('ui_cprofile_profile_id')}} {{ $cProfile->profile_id }}</a> -
        <a href="{{ route('backoffice.profile.wallet',  $operation->operation_type == \App\Enums\OperationOperationType::TYPE_TOP_UP_CRYPTO ? $operation->toAccount->cryptoAccountDetail->id : $operation->fromAccount->cryptoAccountDetail->id) }}" class="text-dark">{{ strtoupper($operation->to_currency) }} {{t('title_wallet_page')}}</a>
        - Operation {{ $operation->operation_id }}
        @if($operation->substatus)
            <button type="button" class="btn themeBtnOnlyBorder ml-3" title="{{$operation->error_message}}">
                {{ \App\Enums\OperationSubStatuses::getName($operation->substatus) }}
            </button>
        @endif
    </h4>

    <br>
    @include('backoffice.partials.session-message')

    <div class="row mt-5">
        <div class="col-md-4">
            <div class="row common-shadow-theme pt-3 pb-3 ml-1">
                <div class="col-md-5 text-left px-3 ml-3">
                    <h6 class="d-block">{{ in_array($operation->operation_type, [\App\Enums\OperationOperationType::TYPE_WITHDRAW_CRYPTO, \App\Enums\OperationOperationType::TYPE_WITHDRAW_CRYPTO_PF])  ? t('withdrawal_crypto_withdrawal_type') : t('top_up_deposit_type')}}</h6>
                    <p class="d-block">{{ t('crypto') }}</p>
                    <h6 class="d-block">{{ t('currency') }}</h6>
                    <p class="d-block">{{ $operation->to_currency ?? '-' }}</p>
                    <h6 class="d-block">{{ t('amount') }}</h6>
                    <p class="d-block">{{ $operation->amount  ?? '-'}}</p>
                    <h6 class="d-block">{{ t('amount_euro') }}</h6>
                    <p class="d-block">{{ eur_format($operation->amount_in_euro)  ?? '-'}}</p>
                    @if ($link)
                        <a href="{{ $link }}" class="text-nowrap" target="_blank">{{ t('ui_view_transaction_details') }}</a>
                    @endif
                    @if($operation->operation_type == \App\Enums\OperationOperationType::TYPE_WITHDRAW_CRYPTO_PF && $operation->parent)
                        <a href="{{ route('backoffice.card.transaction', $operation->parent) }}" class="text-nowrap" target="_blank">
                            {{ t('parent_operation') }}
                        </a>
                    @elseif(($operation->operation_type == \App\Enums\OperationOperationType::TYPE_TOP_UP_CRYPTO_PF || $operation->operation_type == \App\Enums\OperationOperationType::TYPE_TOP_UP_CRYPTO_EXTERNAL_PF) && $operation->parent)
                        <a href="{{ route('backoffice.withdraw.crypto.transaction', $operation->parent->id) }}" class="text-nowrap" target="_blank">
                            {{ t('parent_operation') }}
                        </a>
                    @endif
                </div>
                <div class="col-md-5 text-left px-3 ml-3">
                    <h6 class="d-block">{{ t('trx_limit') }}</h6>
                    <p class="d-block">{{ $limits ? eur_format($limits->transaction_amount_max) : '-' }}</p>
                    <h6 class="d-block">{{ t('available_limit') }}</h6>
                    <p class="d-block">{{ eur_format($availableMonthlyAmount) }}</p>
                    @if($operation->operation_type == \App\Enums\OperationOperationType::TYPE_WITHDRAW_CRYPTO)
                    <h6 class="d-block">{{ t('withdrawal_crypto_withdrawal_fee') }}</h6>
                    <p class="d-block">{{ $withdrawFee ?? '-' }}</p>
                        @endif
                </div>
            </div>
        </div>
        <div class="col-md-4 pl-5 d-flex">
            <div class="row common-shadow-theme pt-3 w-100">
                <div class="col-md-12">
                    <div class="row float-right">
                        <span
                            class="font-weight-bold {{ $toWallet->isAllowedRisk() ? 'text-success' : 'text-danger'}} mr-3">{{ $toWallet->risk_score * 100 ?? '-'}} %</span>
                    </div>
                    <div class="row">
                        <div class="col-md-12 text-left">
                            <h6 class="d-block">KYT</h6>
                            <p class="d-block {{ $toWallet->isAllowedRisk() ? 'text-success' : 'text-danger'}}">
                                @if (!$toWallet->isAllowedRisk())
                                    {{ t('withdrawal_crypto_very_high_risk') }}
                                @else
                                    {{ t('withdrawal_crypto_successful') }}
                                @endif
                            </p>
                            <h6 class="d-block">KYT Status</h6>
                            <p class="d-block {{ $toWallet->isAllowedRisk() ? 'text-success' : 'text-danger'}}" style="font-size: 14px;word-break: break-all;">{{ $toWallet->chainalysis_alert_severity ?:'-' }}</p>
                            <h6 class="d-block">KYT Risk Score</h6>
                            <p class="d-block {{ $toWallet->isAllowedRisk() ? 'text-success' : 'text-danger'}}" style="font-size: 14px;word-break: break-all;">{{ $toWallet->risk_score ?:'-' }}</p>
                            <h6 class="d-block">{{ t('withdrawal_crypto_address') }}</h6>
                            <p class="d-block" style="font-size: 14px;word-break: break-all;">{{ $toWallet->account_id ?? '-' }}</p>
                            <h6 class="d-block">{{ t('withdrawal_crypto_verification_id') }}</h6>
                            <p class="d-block">{{ '-'}}</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="row">
                <div class="col-md-12 p-3">
                    <h6 class="d-block">{{ t('date') }}</h6>
                    <p class="d-block">{{ $operation->created_at }}</p>
                    <h6 class="d-block">{{ t('status') }}</h6>
                    <p class="@if($operation->status != \App\Enums\OperationStatuses::SUCCESSFUL) text-danger @else text-success @endif d-block">{{ \App\Enums\OperationStatuses::getName($operation->status) }}</p>
                    @if($operation->status == \App\Enums\OperationStatuses::RETURNED)
                        <h6 class="d-block">{{ t('substatus') }}</h6>
                        <p class="text-danger d-block">{{ \App\Enums\OperationSubStatuses::getName($operation->substatus) }}</p>
                    @endif

                    @if($currentAdmin->isAllowed([\App\Enums\BUserPermissions::CHANGE_OPERATION_STATUS], $operation->cProfile->cUser->project_id))

                        <button class="btn btn-lg btn-primary themeBtn register-buttons mb-4 mb-md-0 round-border"
                                data-toggle="modal" data-target="#changeStatus"
                                type="submit">{{ t('change_status') }}
                        </button>

                        <!-- Button trigger modal -->
                        @include('backoffice.withdraw-crypto-transactions._change-status')
                    @endif
                </div>
            </div>
        </div>
    </div>

    @if($payerDetails)
        <div class="mt-5">
            <div class="col-md-3 d-flex">
                <div class="row common-shadow-theme pt-4 pb-3 w-100">
                    <div class="col-md-12">
                        <h6 class="d-block">{{ t('payer_name') }}</h6>
                        <p class="d-block">{{ $payerDetails->full_name }}</p>
                        <h6 class="d-block">{{ t('payer_phone_number_backoffice') }}</h6>
                        <p class="d-block fs14">{{ $payerDetails->formatted_phone_number }}</p>
                        <h6 class="d-block">{{ t('email') }}</h6>
                        <p class="d-block fs14">{{ $payerDetails->email }}</p>
                    </div>
                </div>
            </div>
        </div>
    @endif

    <div class="row mt-5">
        @if(!$operation->isLimitsVerified() ||
            !$toWallet->isAllowedRisk() &&
            $operation->status == \App\Enums\OperationStatuses::PENDING &&
             (!$pendingCryptoTransaction || $operation->operation_type != \App\Enums\OperationOperationType::TYPE_WITHDRAW_CRYPTO)
             && ($currentAdmin->isAllowed([\App\Enums\BUserPermissions::CHANGE_OPERATION_STATUS], $operation->cProfile->cUser->project_id))
        )
            <div class="col-md-4">
            <div class="row common-shadow-theme pt-3 pb-4">
                <div class="col-md-12 text-left">
                    <h6 class="d-block">{{ t('withdrawal_crypto_actions') }}</h6>
                    <p class="d-block">{{ t('withdrawal_crypto_choose_action') }}</p>

                    <form action="{{ route('backoffice.approve.operation.status', $operation->id) }}" method="POST">
                        @csrf
                        <button class="btn btn-lg btn-primary themeBtn register-buttons mt-2 p-2 round-border" type="submit">
                            {{ t('withdrawal_crypto_approve') }}
                        </button>
                    </form>

                    <button class="btn btn-lg btn-dark register-buttons mt-2 p-2 round-border"
                            type="button" data-toggle="modal" data-target="#complianceModal">AML
                    </button>
                    @include('backoffice.partials.transactions._aml')

                    <button class="btn btn-lg btn-dark register-buttons mt-2 p-2 round-border"
                            data-toggle="modal" data-target="#blockWallet"
                            type="button">{{ t('withdrawal_crypto_block_wallet') }}
                    </button>


                    @include('backoffice.withdraw-crypto-transactions._block-wallet')

                    <form action="{{ route('backoffice.withdraw.change.status', $operation->id) }}" method="POST">
                        @csrf
                        <input type="hidden" name="status" value="{{ \App\Enums\OperationStatuses::DECLINED }}">
                        <button class="btn btn-lg btn-light register-buttons mt-2  p-2 round-border"
                                type="submit">{{ t('withdrawal_crypto_decline') }}
                        </button>
                    </form>
                </div>
            </div>
        </div>
        @endif
    </div>

    <div class="row mt-5">
        <h2 class="mt-1 ml-4">{{ t('transactions') }}</h2>
    @if(($currentAdmin->isAllowed([\App\Enums\BUserPermissions::ADD_TRANSACTION], $operation->cProfile->cUser->project_id)))

        <!-- Button triggers modal - add transaction-->
            <button type="button" class="btn themeBtn ml-5 h-25 round-border" data-toggle="modal"
                    data-target="#exampleModal1">
                {{ t('add') }}
            </button>

            @include('backoffice.withdraw-crypto-transactions._add-transaction', ['address' => $toWallet->address ?? ''])
        @endif
    </div>

    @include('backoffice.partials.transactions.transaction-history')
    @include('backoffice.withdraw-crypto-transactions.operation-steps')
    @include('backoffice.partials.transactions.transaction-fees')
    @include('backoffice.partials.operation_logs',['logs' => $operationLogs])

    <script src="/js/backoffice/transactions.js"></script>
    <script>
        //enable editing of commissions fields
        function toggleCommissionsEdit() {
            $('.commission').prop('readonly', function (i, v) {
                return !v;
            });
        }

        $(document).ready(function () {
            @if (count($errors) > 0)
            $('#exampleModal1').modal('show');
            @endif
        });

        //display right fields according transactions type
        function selectForm($id, $step = null) {
            let trxType = $('#' + $id).val();
            let clientWallet = "{{ $operation->toAccount->id }}";
            let clientWalletName = "{{ $operation->toAccount->name }}";
            let externalWallet = "{{ $operation->fromAccount->id }}";
            let externalWalletName = "{{ $operation->fromAccount->name }}";

            if (trxType == "{{ \App\Enums\TransactionType::CRYPTO_TRX }}") {
                $('#crypto-form').attr('hidden', false);
                $('.crypto-address').attr('hidden', false);
                $('.add-trx-btn').attr('disabled', false);
                $('#to_account').empty();
                $('#from_account').empty();
                $('#to_account').append('<option  value="' + clientWallet + '" class="from-account-option">' + clientWalletName + '</option>');
                $('#from_account').append('<option  value="' + externalWallet + '" class="from-account-option">' + externalWalletName + '</option>');
            } else if (trxType == "{{ \App\Enums\TransactionType::REFUND }}") {
                if ($step != 0) {
                    $('.add-trx-btn').attr('disabled', 'disabled')
                }
                $('#refund-form').attr('hidden', false);
                $('.crypto-address').attr('hidden', 'hidden');
                $('#to_account').empty();
                $('#from_account').empty();
                $('#from_account').append('<option  value="' + clientWallet + '" class="from-account-option">' + clientWalletName + '</option>');
                $('#to_account').append('<option  value="' + externalWallet + '" class="from-account-option">' + externalWalletName + '</option>');
            }
        }
    </script>


@endsection
