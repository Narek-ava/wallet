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

    <h4 class="mb-5 pb-3"><a href="{{ route('backoffice.profile', $operation->cProfile->id) }}" class="text-dark">Client
            ID {{ $operation->cProfile->profile_id }}</a> -
        <a  href="{{ route('backoffice.profile.wallet', $operation->toAccount->cryptoAccountDetail->id) }}" class="text-dark">
            {{ strtoupper($operation->to_currency) }} Wallet</a>
        - Operation {{ $operation->operation_id }}
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
                <div class="col-md-3 px-3 ml-3">
                    <h6 class="d-block">{{ t('deposit_type') }}</h6>
                    <p class="d-block">{{ \App\Enums\OperationOperationType::getName($operation->operation_type) }}</p>
                    <h6 class="d-block">{{ t('currency') }}</h6>
                    <p class="d-block">{{ $operation->from_currency }}</p>
                    <p class="d-none get-from-currency" hidden>{{ $fromCurrency }}</p>
                    <p class="d-none get-operation-from-currency" hidden>{{ $operation->from_currency }}</p>
                    <p class="d-none get-operation-to-currency" hidden>{{ $operation->to_currency }}</p>
                    <h6 class="d-block">{{ t('card') }}</h6>
                    <p class="d-block">{{ ($operation->fromAccount && $operation->fromAccount->cardAccountDetail) ? $operation->fromAccount->cardAccountDetail->card_number : '-'}}</p>
                    <h6 class="d-block">Operation step</h6>
                    <p class="operation-step">{{ $operation->step }}</p>
                    @if($operation->operation_type == \App\Enums\OperationOperationType::TYPE_CARD_PF)
                        <h6 class="d-block">{{ t('payment_form_name') }}</h6>
                        <p class="d-block">{{  $operation->paymentForm->name }}</p>
                    @endif
                </div>
                <div class="col-md-2 px-3 ml-3">
                    <h6 class="d-block">{{ t('exchange_to') }}</h6>
                    <p class="d-block exchange-to-currency">{{ $operation->to_currency }}</p>
                    <h6 class="d-block">{{ t('amount') }}</h6>
                    <p class="d-block">{{ $operation->amount }}</p>
                    <h6 class="d-block">{{ t('trx_limit') }}</h6>
                    <p class="d-block">{{  moneyFormatWithCurrency(\App\Enums\Currency::CURRENCY_EUR,$limits ? $limits->transaction_amount_max : 0) }}</p>
                </div>
                <div class="col-md-3 px-3 ml-3">
                    <h6 class="d-block">{{ t('deposit_fee') }}</h6>
                    <p class="d-block"> {{ $topUpFee ?? '-' }} %</p>
                    <h6 class="d-block">{{ t('withdrawal_blockchain_fee') }}</h6>
                    <p class="d-block">{{ $blockChainFee }}</p>
                    <h6 class="d-block">{{ t('available_limit') }}</h6>
                    <p class="d-block">{{ moneyFormatWithCurrency(\App\Enums\Currency::CURRENCY_EUR ,$availableMonthlyAmount) }}</p>
                </div>
                <div class="col-md-3 px-3 ml-3">
                    <h6 class="d-block">{{ t('liquidity_provider') }}</h6>
                    <p class="d-block">{{ $liquidityProviderAccount->name ?? '-' }}</p>
                    <h6 class="d-block">{{ t('exchange_rate') }}</h6>
                    <p class="d-block">{{ $operation->exchange_rate ?? '-' }}</p>
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
                @if($currentAdmin->can(\App\Enums\BUserPermissions::CHANGE_OPERATION_STATUS) || $currentAdmin->is_super_admin)
                    <button class="btn btn-lg btn-primary themeBtn register-buttons mb-4 mb-md-0 round-border"
                            data-toggle="modal" data-target="#changeOperationStatus"
                            type="submit">{{ t('change_status') }}
                    </button>

                    <!-- Button trigger modal -->
                    @include('backoffice.transactions._change-status')
                @endif
            </div>
        </div>
    </div>


    <div class="row pl-3 pt-5">
        @if(isset($operation->fromAccount->cardAccountDetail))
            <div class="col-md-3 d-flex">
                <div class="row common-shadow-theme pt-4 pb-3 w-100">
                    <div class="col-md-12">
                        <h6 class="d-block">{{ t('card_checking') }}</h6>
                        <p class="d-block
                            @if($operation->fromAccount->cardAccountDetail->is_verified)
                                text-success">
                                {{ t('card_check_success') }}
                            @else
                                text-danger">
                                {{ t('card_check_not_verified') }}
                            @endif
                        </p>
                            <h6 class="d-block">{{ t('matching') }}</h6>
                            <p class="d-block">{{ $operation->fromAccount->cProfile->getFullName() }}</p>
                            <h6 class="d-block">{{ t('ui_card_number') }}</h6>
                            <p class="d-block"> {{ $operation->fromAccount->cardAccountDetail->card_number }}</p>
                    </div>
                </div>
            </div>
        @endif
        @if(isset($cardTransaction))
            <div class="col-md-3 d-flex">
                <div class="row common-shadow-theme pt-4 pb-3 w-100">
                    <div class="col-md-12">
                        <h6 class="d-block">{{ t('card_processing') }}</h6>
                        <p class="d-block
                        @if($cardTransaction->status == \App\Enums\TransactionStatuses::SUCCESSFUL) text-success @else text-danger @endif ">
                            {{ \App\Enums\TransactionStatuses::NAMES[$cardTransaction->status]}}
                        </p>
                        <h6 class="d-block">{{ t('amount') }}</h6>
                        <p class="d-block">{{ moneyFormatWithCurrency($operation->from_currency, $cardTransaction->trans_amount) }}</p>
                        <h6 class="d-block">{{ t('transaction_id') }}</h6>
                        <p class="d-block">{{ $cardTransaction->tx_id ?? '' }}</p>
                    </div>
                </div>
            </div>
        @endif
        @if( in_array( $operation->operation_type, [\App\Enums\OperationOperationType::TYPE_CARD_PF, \App\Enums\OperationOperationType::TYPE_TOP_UP_CRYPTO_PF, \App\Enums\OperationOperationType::TYPE_TOP_UP_CRYPTO_EXTERNAL_PF]))
            <div class="col-md-3 d-flex">
                <div class="row common-shadow-theme pt-4 pb-3 w-100">
                    <div class="col-md-12">
                        <h6 class="d-block">{{ t('payer_name') }}</h6>
                        <p class="d-block">{{ $operation->toAccount->cProfile->getFullName() }}</p>
                        <h6 class="d-block">{{ t('payer_phone_number_backoffice') }}</h6>
                        <p class="d-block fs14">{{ $operation->toAccount->cProfile->cUser->phone }}</p>
                        <h6 class="d-block">{{ t('email') }}</h6>
                        <p class="d-block fs14">{{ $operation->toAccount->cProfile->cUser->email }}</p>
                    </div>
                </div>
            </div>
        @endif
        @if($isCardTransactionDeclined && ($currentAdmin->isAllowed([\App\Enums\BUserPermissions::CHANGE_OPERATION_STATUS])))
            <div class="col-md-5 d-flex">
                <div class="row common-shadow-theme pt-4 pb-3 w-100">
                    <div class="col-md-10">
                        <h6 class="d-block">{{ t('actions') }}</h6>
                        <p class="d-block">{{ t('choose_one_action') }}</p>

                        <form class="register-buttons p-0 mt-1 mb-1" action="{{ route('backoffice.approve.operation.status', $operation->id) }}" method="POST">
                            @csrf
                            <button class="btn btn-lg btn-primary themeBtn register-buttons round-border  mt-1 mb-1"
                                    type="submit">{{ t('approve_transaction') }}
                            </button>
                        </form>
                        @if(($currentAdmin->isAllowed([\App\Enums\BUserPermissions::ADD_TRANSACTION])))

                            <button class="btn btn-lg btn-secondary register-buttons round-border"
                                    id="refundBtn"
                                    data-toggle="modal" data-target="#exampleModal1"
                                    type="submit">{{ t('refund') }}
                            </button>

                        @endif

                        <button
                            class="btn btn-lg btn-dark register-buttons round-border blockUnblockButton mt-1 mb-1"
                            type="submit"
                            data-toggle="modal"
                            data-crypto-account-detail-id="{{ $operation->toAccount->cryptoAccountDetail->id }}"
                            data-target="#blockUnblockWalletModal">
                            @if($operation->toAccount->cryptoAccountDetail->blocked)
                                {{ t('unblock_wallet') }}
                            @else
                                {{ t('block_wallet') }}
                            @endif
                        </button>
                        @include('backoffice.partials.transactions._block-unblock-wallet')

                        <button class="btn btn-lg btn-dark register-buttons round-border mt-1 mb-1"
                                type="button" data-toggle="modal" data-target="#complianceModal"> {{ t('aml') }}
                        </button>
                        @include('backoffice.partials.transactions._aml')
                    </div>
                    <div class="text-danger p-3">{{ $cardTransaction->decline_reason }}</div>
                </div>
            </div>
        @endif
    </div>
    @if($operation->from_account)
        <div class="row mt-5">
            <h2 class="mt-1 ml-4">{{ t('transactions') }}</h2>
            @if(($currentAdmin->isAllowed([\App\Enums\BUserPermissions::ADD_TRANSACTION])))

                <button type="button" class="btn themeBtn ml-5 h-25 round-border" data-toggle="modal"
                        id="addTransactionBtn"
                        data-trx-type="{{ $transactionType }}"
                        data-target="#exampleModal1">
                    {{ t('add') }}
                </button>
                @include('backoffice.topup-card-transactions._add-transaction')
            @endif
            @include('backoffice.partials.transactions.transaction-history')

        </div>
    @endif

    @include('backoffice.topup-card-transactions.operation-steps')
    @include('backoffice.partials.transactions.transaction-fees')
    @include('backoffice.partials.operation_logs',['logs' => $operationLogs])
    <script src="/js/backoffice/transactions.js"></script>
    <script>
        //get accounts and put in select depending on chosen type -from
        function getFromAccountsByType($id) {
            let fromAccounts =  $('#from_account')
            let toAccounts =  $('#to_account')
            let trxtype =  $('#exchange').val()
            toAccounts.empty();
            fromAccounts.empty();

            let toCurrency = $('#toCryptocurrency').val();
            let fromCurrency = $('#fromFiat').val();

            if (trxtype === "{{ \App\Enums\TransactionType::CARD_TRX }}") {
                toCurrency = fromCurrency;
            }

            $('#' + $id).empty();
            $.ajax({
                type: "POST",
                url: "{{ route('test') }}",
                data: {
                    "_token": "{{ csrf_token() }}",
                    'from_type': $('.from-type').val(),
                    'to_type': $('.to-type').val(),
                    'from': 1,
                    'from_currency': fromCurrency,
                    'to_currency': toCurrency,
                    'operation_id': "{{ $operation->id }}",
                    'c_profile_id': "{{ $operation->c_profile_id }}",
                    'account': fromAccounts.val(),
                    'trx_type': trxtype,
                },
                success: (response) => {
                    toAccounts.append('<option hidden value="">' + 'Select...' + '</option>');
                    fromAccounts.append('<option hidden value="">' + 'Select...' + '</option>');

                    if (response.toAccounts && response.toAccounts.length > 0) {
                        for (let i = 0; i < response.toAccounts.length; i++) {
                            $('#to_account').append('<option value="' + response.toAccounts[i].id + '" class="to-account-option">' + response.toAccounts[i].name + '</option>');
                        }
                    }
                    if (response.fromAccounts && response.fromAccounts.length > 0) {
                        for (let i = 0; i < response.fromAccounts.length; i++) {
                            $('#from_account').append('<option value="' + response.fromAccounts[i].id + '" class="to-account-option">' + response.fromAccounts[i].name + '</option>');
                        }
                    }
                    $('#exchange_api').val(response.api)
                  }
            })
        }

        //get accounts and put in select depending on chosen type - to
        function getToAccountsByType($id) {
            $('#' + $id).empty();
            let fromAccounts =  $('#from_account')
            let toAccounts =  $('#to_account')
            let trxtype =  $('#exchange').val()
            toAccounts.empty();
            fromAccounts.empty();

            let toCurrency = $('#toCryptocurrency').val();
            let fromCurrency = $('#fromFiat').val();


            if (trxtype === "{{ \App\Enums\TransactionType::CARD_TRX }}") {
                toCurrency = fromCurrency;
            }


            $.ajax({
                type: "POST",
                url: "{{ route('test') }}",
                data: {
                    "_token": "{{ csrf_token() }}",
                    'from_type': $('.from-type').val(),
                    'to_type': $('.to-type').val(),
                    'operation_id': "{{ $operation->id }}",
                    'c_profile_id': "{{ $operation->c_profile_id }}",
                    'account': $('#to_account').val(),
                    'from': 0,
                    'trx_type': trxtype,
                    'from_currency': fromCurrency,
                    'to_currency': toCurrency,
                },
                success: (response) => {
                    toAccounts.append('<option hidden value="">' + 'Select...' + '</option>');
                    fromAccounts.append('<option hidden value="">' + 'Select...' + '</option>');

                    if (response.toAccounts && response.toAccounts.length > 0) {
                        for (let i = 0; i < response.toAccounts.length; i++) {
                            $('#to_account').append('<option value="' + response.toAccounts[i].id + '" class="to-account-option">' + response.toAccounts[i].name + '</option>');
                        }
                    }
                    if (response.fromAccounts && response.fromAccounts.length > 0) {
                        for (let i = 0; i < response.fromAccounts.length; i++) {
                            $('#from_account').append('<option value="' + response.fromAccounts[i].id + '" class="to-account-option">' + response.fromAccounts[i].name + '</option>');
                        }
                    }
                    $('#exchange_api').val(response.api)
                }
            })
        }

        //get commissions and show in form, depending on chosen account
        function getCommissionsFromAccount($id, $from) {
            $.ajax({
                type: "POST",
                url: "{{route('backoffice.transaction.get.from.commissions')}}",
                data: {
                    "_token": "{{ csrf_token() }}",
                    'operation_id': "{{ $operation->id }}",
                    'account': $('#' + $id).val(),
                    'from': $from,
                    'from_type': $('#from_type').val(),
                    'trx_type': $('#exchange').val(),
                },
                success: function (response) {
                    if (response.commission) {
                        $('#exchange_fee').val(response.commission.fixed_commission)
                        $('#exchange_fee_percent').val(response.commission.percent_commission)
                        let count = 0;
                        if (response.isIndividualCardPayment) {
                            count = {{ \App\Enums\OperationOperationType::BLOCKCHAIN_FEE_COUNT_TOP_UP_CARD }}
                        } else {
                            count = {{ \App\Enums\OperationOperationType::BLOCKCHAIN_FEE_COUNT_MERCHANT_PAYMENT }}
                        }
                        if (response.transactionType == {{ \App\Enums\TransactionType::CRYPTO_TRX }}) {
                            $('#exchange_fee_min').val(response.commission.blockchain_fee * count)
                        } else {
                            $('#exchange_fee_min').val(response.commission.min_commission * count)
                        }
                    } else {
                        $('#exchange_fee').val('')
                        $('#exchange_fee_percent').val('')
                        $('#exchange_fee_min').val('')
                    }

                }
            })
        }

        //get commissions and show in form, depending on chosen account
        function getCommissionsToAccount($id, $from) {
            $.ajax({
                type: "POST",
                url: "{{ route('backoffice.transaction.to.from.commissions') }}",
                data: {
                    "_token": "{{ csrf_token() }}",
                    'operation_id': "{{ $operation->id }}",
                    'account': $('#' + $id).val(),
                    'from': $from,
                    'from_type': $('#to_type').val(),
                    'trx_type': $('#exchange').val(),
                },
                success: function (response) {
                    if (response.commission && $('#exchange').val() == 4) {
                        $('#to_fee_percent').val(response.commission.refund_transfer_percent)
                        $('#to_fee').val(response.commission.refund_transfer)
                        $('#to_fee_min').val(response.commission.refund_transfer_percent)
                        if (response.refundAmount) {
                            $('#currencyAmount').val(response.refundAmount)
                            $('#currencyAmount').prop('max', response.refundAmount)
                        }
                    }else if (response.commission && $('#exchange').val() != 4) {
                        $('#to_fee').val(response.commission.fixed_commission)
                        $('#to_fee_percent').val(response.commission.percent_commission)
                        $('#to_fee_min').val(response.commission.min_commission)
                        if ($('#exchange').val() == 11) {
                            $('#currencyAmount').val(response.chargebackAmount)
                            $('#currencyAmount').prop('max', response.chargebackAmount)
                        }else if($('#exchange').val() != 2){
                            $('#currencyAmount').val({{ $allowedMaxAmount }})
                            $('#currencyAmount').prop({{ $allowedMaxAmount }})
                        }else {
                            $('#currencyAmount').val('')
                        }
                    } else {
                        $('#to_fee').val('')
                        $('#to_fee_percent').val('')
                        $('#to_fee_min').val('')
                    }
                    $('#to_address').val(response.toAddress)
                }
            })
        }

        //get accounts according currency
        function getAccountsByCurrency($from) {
            if (!($('#exchange').val() == 3 && ($('#toCryptocurrency').val() == '' || $('#fromFiat').val() == ''))) {
                $('#from_account').prop('disabled', false);
                $('#to_account').prop('disabled', false);
            }

            getFromAccountsByType('from_account');
            // getToAccountsByType('to_account');
        }

        //enable editing of commissions fields
        function toggleCommissionsEdit() {
            $('.commission').prop('readonly', function (i, v) {
                return !v;
            });
        }

        //display right fields according transactions type
        function selectForm($id, $step = null) {
            let trxType = $('#' + $id).val();

            $('#' + $id).append('<option hidden value="">' + 'Select...' + '</option>');
            $('#from_account').attr('disabled', 'disabled');
            $('#to_account').attr('disabled', 'disabled');
            $('#from_account').val('')
            $('#to_account').val('')
            $('#exchange_fee').val('')
            $('#exchange_fee_min').val('')
            $('#exchange_fee_percent').val('')
            $('#to_fee_percent').val('')
            $('#to_fee').val('')
            $('#to_fee_min').val('')
            $('.commissionFee').show();
            $('.exchange-fee-min').text('');


            if (trxType == 8) {
                appendFiat()
                $('.exchange-rate').attr('hidden', 'hidden');
                $('.exchange-api').attr('hidden', 'hidden');
                $('.to-cryptocurrency').attr('hidden', 'hidden');
                $('.cryptocurrency-amount').attr('hidden', 'hidden');
                $('.crypto-address').attr('hidden', 'hidden');
                $('#refund-form').attr('hidden', false);
                $('.to-fee').attr('hidden', false);
                $('.exchange-fee').text('From fee');
                $('.exchange-fee-percent').text('From fee %');
                $('.add-trx-btn').attr('disabled', false);
                $('.exchange-fee-min').text('From fee minimum');
                $('.from-fee-minimum').css({'margin-left': '0', 'margin-top': '0'});

                /*$('.from-fee-minimum').css({'margin-left': '0', 'margin-top': '0'});

                if ($(window).width() > 768) {
                    $('.from-fee-minimum').css({'margin-left': '50%', 'margin-top': '-80px'});
                }*/


            } else if (trxType == 2) {
                appendCrypto();
                $('#crypto-form').attr('hidden', false);
                $('.exchange-rate').attr('hidden', 'hidden');
                $('.exchange-api').attr('hidden', 'hidden');
                $('.to-cryptocurrency').attr('hidden', 'hidden');
                $('.crypto-address').attr('hidden', false);
                $('.cryptocurrency-amount').attr('hidden', 'hidden');

                $('.exchange-fee').attr('hidden', false);
                $('.exchange-fee').text('From fee');

                $('.exchange-fee-percent').attr('hidden', false);
                $('.exchange-fee-percent').text('From fee %');

                $('.exchange-fee-min').attr('hidden', false);
                $('.exchange-fee-min').text('Blockchain fee');

                $('.to-fee').attr('hidden', 'hidden');
                $('.add-trx-btn').attr('disabled', false);
                if ($(window).width() > 768) {
                    $('.from-fee-minimum').css({'margin-left': '50%', 'margin-top': '-90px'});
                }

            } else if (trxType == 3) {
                appendFiat()
                $('#exchange-form').attr('hidden', false);
                $('.exchange-rate').attr('hidden', false);
                $('.to-cryptocurrency').attr('hidden', false);
                $('.crypto-address').attr('hidden', 'hidden');
                $('.cryptocurrency-amount').attr('hidden', false);
                $('.exchange-fee').text('Exchange fee');
                $('.exchange-fee-percent').text('Exchange fee %');
                $('.exchange-fee-min').text('Exchange fee minimum');
                if ($(window).width() > 768) {
                    $('.from-fee-minimum').css({'margin-left': '50%', 'margin-top': '-90px'});
                }
                $('.to-fee').attr('hidden', 'hidden');
                $('.exchange-api').attr('hidden', false);
                $('.add-trx-btn').attr('disabled', false);
                $('.commissionFee').hide();
            } else if (trxType == 4 || trxType == 11) {
                if ($step != 1 && trxType == 4) {
                    $('.add-trx-btn').attr('disabled', 'disabled')
                }
                appendFiat()
                $('.exchange-api').attr('hidden', 'hidden');
                $('.exchange-rate').attr('hidden', 'hidden');
                $('.to-cryptocurrency').attr('hidden', 'hidden');
                $('.cryptocurrency-amount').attr('hidden', 'hidden');
                $('.crypto-address').attr('hidden', 'hidden');
                $('#refund-form').attr('hidden', false);
                $('.to-fee').attr('hidden', false);
                $('.exchange-fee').text('From fee');
                $('.exchange-fee-percent').text('From fee %');
                $('.exchange-fee-min').text('From fee minimum');
                $('.from-fee-minimum').css({'margin-left': '0', 'margin-top': '0'});
            }
        }

        $(document).ready(function () {
            $('body').on('change', '#toCryptocurrency', function () {
                getTicker();
            });
            $('body').on('change', '#fromFiat', function () {
                getTicker();
            });



            @if (count($errors) > 0 &&  ($errors->has('from_account') || $errors->has('to_account') || $errors->has('exchange_api')|| $errors->has('exchange_rate') || $errors->has('to_address') ||
             $errors->has('date') || $errors->has('from_currency') || $errors->has('exchange_fee_percent') || $errors->has('exchange_fee')
             || $errors->has('to_fee_min') || $errors->has('exchange_fee_min') || $errors->has('to_fee')|| $errors->has('to_fee_percent')))
            $('#exampleModal1').modal('show');
            @endif

            @if (count($errors) > 0 &&  ($errors->has('template_name') || $errors->has('country') ||
             $errors->has('currency') || $errors->has('iban') || $errors->has('swift') || $errors->has('account_holder')
             || $errors->has('account_number') || $errors->has('bank_name') || $errors->has('bank_address')))
            $('#exampleModal').modal('show');
            @endif
        });
        function getTicker() {
            let crypto = $('#toCryptocurrency').val();
            let fiat = $('#fromFiat').val();
            let ex = $('#exchange').val();
            let amountTo = $('#amountTo');
            let currencyAmount = $('#currencyAmount').val();
            let projectId = "{{ $operation->cProfile->cUser->project_id }}";

            if (parseInt(ex) === 3 && crypto && fiat) {
                crypto = $('#toCryptocurrency').val();
                fiat = $('#fromFiat').val();
                $.ajax({
                    url: '{{ route('get.ticker') }}',
                    type: 'post',
                    data: {"_token": "{{ csrf_token() }}", crypto, fiat, projectId},
                    success: function (data) {
                        $('#exchangeRate').val(data);
                        amountTo.val(currencyAmount / data);
                    }
                })
            }
        }

        $('body').on('change', '#currencyAmount', function () {
            let amount = $(this).val();
            let rate = $('#exchangeRate').val();
            if (amount && rate) {
                $('#amountTo').val(parseFloat(rate) * parseFloat(amount));
            }
        });

        $('#exchange').on('change', function () {
            if ($(this).val() == {{ \App\Enums\TransactionType::CARD_TRX }}) {
                $('#from_type').val(5);
                $('#to_type').val(4);
            }else if ($(this).val() == {{ \App\Enums\TransactionType::EXCHANGE_TRX }}) {
                $('#from_type').val(2);
                $('#to_type').val(2);
            }else if ($(this).val() == {{ \App\Enums\TransactionType::CRYPTO_TRX }}) {
                if($('.operation-step').val() > 2) {
                    $('#from_type').val(3);
                    $('#to_type').val(5);
                }else{
                    $('#from_type').val(2);
                    $('#to_type').val(3);
                }
                appendCrypto()
                let toCrypto = $('.get-operation-to-currency').text();
                $('.get-from-currency').text(toCrypto)
            }else if ($(this).val() == {{ \App\Enums\TransactionType::REFUND }} || $(this).val() == {{ \App\Enums\TransactionType::CHARGEBACK }}) {
                $('#from_type').val(4);
                $('#to_type').val(5);
            }

            if ($(this).val() != {{ \App\Enums\TransactionType::CRYPTO_TRX }}){
                appendFiat();
                let fromFiat = $('.get-operation-from-currency').text();
                $('.get-from-currency').text(fromFiat)
            }

            setDefaultData('exchange');
        })

        $('#refundBtn').on('click', function () {
            $('#exchange').val(4);
            $('#from_type').val(4);
            $('#to_type').val(5);
            setDefaultData('exchange')
        })

        $('#addTransactionBtn').on('click', function () {
            let trxType = $(this).data("trx-type");
            $('#exchange').val(trxType);
            $('#from_type').val({{ $fromType }});
            $('#to_type').val({{ $toType }});
            setDefaultData('exchange');
        })

        function setDefaultData() {
            $('.exchange-rate').attr('hidden', 'hidden');
            $('.exchange-api').attr('hidden', 'hidden');
            $('#exchangeApi').val(1);
            $('.to-cryptocurrency').attr('hidden', 'hidden');
            $('.cryptocurrency-amount').attr('hidden', 'hidden');
            $('.crypto-address').attr('hidden', 'hidden');
            $('#refund-form').attr('hidden', false);
            $('.to-fee').attr('hidden', false);
            let fromFiat = $('.get-from-currency').text();
            let toCrypto = $('.exchange-to-currency').text();
            let operationStep = $('.operation-step').text();
            selectForm('exchange', operationStep)

            $('#fromFiat').val(fromFiat);
            $('#toCryptocurrency').val(toCrypto);
            getTicker();

            getAccountsByCurrency('from');
        }

        $('body').on('change', '#currencyAmount', function () {
            let amount = $(this).val();
            let rate = $('#exchangeRate').val();
            if (amount && rate) {
                $('#amountTo').val(parseFloat(amount) / parseFloat(rate));
            }
        });

        function appendFiat(){
            $('#fromFiat').empty();
            $('#fromFiat').append('<option hidden value="">' + 'Select...' + '</option>');
            let fiatCurrencies = @json(array_keys(\App\Enums\Currency::FIAT_CURRENCY_NAMES), JSON_UNESCAPED_UNICODE);
            fiatCurrencies.forEach((currency) => {
                $('#fromFiat').append('<option  value="' + currency + '">' + currency + '</option>');
            })
        }

        function appendCrypto(){
            $('#fromFiat').empty();
            $('#fromFiat').append('<option hidden value="">' + 'Select...' + '</option>');
            let currencies = @json(array_keys(\App\Enums\Currency::getList()), JSON_UNESCAPED_UNICODE);
            currencies.forEach((currency) => {
                $('#fromFiat').append('<option value="' + currency + '" class="to-account-option">' + currency + '</option>');
            })

        }


            function showTrxBankDetails($id) {
            $.ajax({
                type: "get",
                url: "{{ route('backoffice.transactions.details') }}",
                data: {
                    'transaction_id': $id,
                },
                success: function (response) {
                    let trx = response.transaction;
                    let exchangeFee = null;

                    if(trx.operation.operation_type == 1 || trx.operation.operation_type == 2 || trx.operation.operation_type == 8 || trx.operation.operation_type == 9){
                        exchangeFee = response.exchangeFee ?? exchangeFee;
                    }

                    //Exchange trx
                    if (response.transaction.type == 3) {
                        $('#transactionDetail .exchange-rate').attr('hidden', false).val(trx.exchange_rate ?? '');
                        $('#transactionDetail .to-currency').attr('hidden', false).val(trx.to_account.currency ?? '');
                        $('#transactionDetail .to-fee-percent').attr('hidden', 'hidden');
                        $('#transactionDetail .to-fee').attr('hidden', 'hidden');
                        $('#transactionDetail .to-fee-min').attr('hidden', 'hidden');
                        $('#transactionDetail .exchange-fee-percent').text('Exchange fee %');
                        $('#transactionDetail .exchange-fee').text('Exchange fee');
                        $('#transactionDetail .from-fee-min').text('Exchange fee minimum');
                        $('#transactionDetail .exchange-fee').val(exchangeFee ?? '');
                        $('#transactionDetail .from-fee-minimum').addClass('exchangeCaseStyle');
                        $('#transactionDetail .cryptocurrency-amount').attr('hidden', false);
                        $('.to-amount').val(trx.recipient_amount)
                    }
                    //Crypto trx
                    else if(response.transaction.type == 2){
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
                        if(response.toCryptoAccountDetail) {
                            $('.crypto-address').val(response.toCryptoAccountDetail.address ?? '');
                        }
                    }
                    else{
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
                    }

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

    <script>
        $(document).ready(function () {
            $('body').on('click', '.blockUnblockButton', function () {
                let cryptoAccountDetailId = $(this).data('crypto-account-detail-id');
                $('#cryptoAccountDetailIdInput').val(cryptoAccountDetailId);
            });
            $('#uploadDocumentField').on("change", function(){
                var filename = $(this).val().split('\\').pop();
                $('#uploadFileName').html(filename);
            });

            if ('{{ $errors->any() }}') {
                if ('{{ $errors->has('crypto_account_detail_id') || $errors->has('operation_id') || $errors->has('file') || $errors->has('reason')}}') {
                    $('#blockUnblockWalletModal').modal('show');
                }
            }
        });

    </script>
@endsection
