@extends('backoffice.layouts.backoffice')
@section('title', t('operation') . ' #' .$operation->operation_id)
@section('title', t('operation'))

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

    <h4><a href="{{ route('backoffice.profile', $cProfile->id) }}" class="text-dark">Client
            ID {{ $cProfile->profile_id }}</a>
        @if($operation->toAccount->cryptoAccountDetail) -
        <a href="{{ route('backoffice.profile.wallet', $operation->toAccount->cryptoAccountDetail->id) }}"
           class="text-dark">{{ strtoupper($operation->to_currency) }} Wallet</a>
        - Operation {{ $operation->operation_id }}
        @endif

        @if($operation->substatus)
            <button type="button" class="btn themeBtnOnlyBorder ml-3" title="{{$operation->error_message}}">
                {{ \App\Enums\OperationSubStatuses::getName($operation->substatus) }}
            </button>
        @endif
    </h4>
    <br>
    @include('backoffice.partials.session-message')

    <div class="row pl-3">
        <div class="col-md-10">
            <div class="row common-shadow-theme pt-4 pb-3">
                <div class="col-md-2 px-3 ml-3">
                    <h6 class="d-block">{{ t('transaction_type') }}</h6>
                    <p class="d-block">
                        {{ $operation->getOperationType() }}
                    </p>
                    <h6 class="d-block">{{ t('currency') }}</h6>
                    <p class="d-block">{{ $operation->from_currency ?? '-' }}</p>
                </div>
                <div class="col-md-2 px-3 ml-3">
                    <h6 class="d-block">{{ t('exchange_to') }}</h6>
                    <p class="d-block">{{ strtoupper($operation->to_currency)  ?? '-' }}</p>
                    <h6 class="d-block">{{ t('amount') }}</h6>
                    <p class="d-block">
                        @if(in_array($operation->from_currency, \App\Enums\Currency::FIAT_CURRENCY_NAMES))
                            {{ number_format($operation->amount, 2) }}
                        @else
                            {{ $operation->amount }}
                        @endif</p>
                    <h6 class="d-block">{{ t('amount_euro') }}</h6>
                    <p class="d-block"> {{ eur_format($operation->amount_in_euro) }}</p>
                    @if ($link)
                        <a href="{{ $link }}" target="_blank">{{ t('ui_view_transaction_details') }}</a>
                    @endif
                </div>
                <div class="col-md-2 px-3 ml-3">
                    <h6 class="d-block">{{ t('withdrawal_fee') }}</h6>
                    <p class="d-block">{{ $commissions ? ($commissions->percent_commission ?? '-') . '% ' . '(Min. ' . ($commissions->min_commission ?? '-') . ' ' . $operation->from_currency . ')': '-' }}</p>
                    <h6 class="d-block">{{ t('exchange_fee') }}</h6>
                    <p class="d-block">{{ $exchangeFee ?? '-' }}</p>
                    <h6 class="d-block">{{ t('trx_limit') }}</h6>
                    <p class="d-block">{{ $limits ? $limits->transaction_amount_max : '-' }}</p>
                </div>
                <div class="col-md px-3 ml-3">
                    <h6 class="d-block">{{ t('available_limit') }}</h6>
                    <p class="d-block">{{ number_format($availableMonthlyAmount, 2)  ?? '-' }}</p>
                    <h6 class="d-block">{{ t('unique_reference') }}</h6>
                    <p class="d-block">{{ $operation->id  ?? '-'}}</p>
                </div>
                <div class="col-md-2 px-3 ml-3">
                    <h6 class="d-block">{{ t('liquidity_provider') }}</h6>
                    <p class="d-block">{{ $liquidityAccount ? $liquidityAccount->name : '-'}}</p>
                    <h6 class="d-block">{{ t('exchange_rate') }}</h6>
                    <p class="d-block">{{ $operation->exchange_rate ?? '-' }}</p>
                    <h6 class="d-block">{{ t('credited') }}</h6>
                    <p class="d-block">{{ $credited ?? '-' }}</p>
                </div>
            </div>
        </div>
        <div class="col-md-2">
            <div class="row">
                <div class="col-md-12">
                    <h6 class="d-block">{{ t('date') }}</h6>
                    <p class="d-block">{{ $operation->created_at }}</p>
                    <h6 class="d-block">{{ t('status') }}</h6>
                    <p class=" @if($operation->status != \App\Enums\OperationStatuses::SUCCESSFUL) text-danger @else text-success @endif d-block">{{ \App\Enums\OperationStatuses::getName($operation->status) }}</p>

                    @if($operation->status == \App\Enums\OperationStatuses::RETURNED)
                        <h6 class="d-block">{{ t('substatus') }}</h6>
                        <p class="text-danger d-block">{{ \App\Enums\OperationSubStatuses::getName($operation->substatus) }}</p>
                    @endif
                    @if(($currentAdmin->isAllowed([\App\Enums\BUserPermissions::CHANGE_OPERATION_STATUS], $operation->cProfile->cUser->project_id ?? null)))

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
    </div>


    @if($operation->from_account)
        <div class="row mt-5">
            <h2 class="mt-1 ml-4">{{ t('transactions') }}</h2>
            <!-- Button triggers modal - add transaction-->
            @if(($currentAdmin->isAllowed([\App\Enums\BUserPermissions::ADD_TRANSACTION], $operation->cProfile->cUser->project_id ?? null)))
                <button type="button" class="btn themeBtn ml-5 h-25 round-border" data-toggle="modal"
                        data-target="#exampleModal1" onclick="setDefaultData()">
                    {{ t('add') }}
                </button>
            @endif

            @include('backoffice.cards.card-order-crypto._add-transaction')
        </div>
    @endif
    @include('backoffice.partials.transactions.transaction-history')
    @include('backoffice.withdraw-wire-transactions.operation-steps')
    @include('backoffice.partials.transactions.transaction-fees')
    @include('backoffice.partials.operation_logs',['logs' => $operationLogs ])

    <script src="/js/backoffice/transactions.js"></script>
    <script>
        //get accounts and put in select depending on chosen type -from
        function getFromAccountsByType($id) {
            $('#to_account').empty();
            $('#from_account').empty();
            $.ajax({
                type: "POST",
                url: '{{route('backoffice.withdraw.wire.from.accounts.by.type')}}',
                data: {
                    "_token": "{{ csrf_token() }}",
                    'from_type': $('.from-type').val(),
                    'to_type': $('.to-type').val(),
                    'from': 1,
                    'fromCurrency': $('#fromFiat').val(),
                    'toCurrency': $('#toCryptocurrency').val(),
                    'operation_id': "{{ $operation->id }}",
                    'c_profile_id': "{{ $operation->c_profile_id }}",
                    'account': $('#from_account').val(),
                    'trx_type': $('#exchange').val(),
                },
                success: function (response) {
                    let trxType = $('#exchange').val();
                    let fromType = $('.from-type').val();
                    let toType = $('.to-type').val();

                    $('#to_account').append('<option hidden value="">' + 'Select...' + '</option>');
                    $('#from_account').append('<option hidden value="">' + 'Select...' + '</option>');

                    if (trxType == "{{ \App\Enums\TransactionType::CRYPTO_TRX }}") {
                        if (response.liquidityAccounts && response.liquidityAccounts.length > 0) {
                            for (let i = 0; i < response.liquidityAccounts.length; i++) {
                                $('#to_account').append('<option value="' + response.liquidityAccounts[i].id + '" class="to-account-option">' + response.liquidityAccounts[i].name + '</option>');
                            }

                        }
                        $('#from_account').append('<option class="from-account-option" value="' + response.account.id + '">' + response.account.name + '</option>');
                    } else if (trxType == "{{ \App\Enums\TransactionType::EXCHANGE_TRX }}") {
                        if (response.liquidityCryptoAccounts && response.liquidityCryptoAccounts.length > 0) {
                            for (let i = 0; i < response.liquidityCryptoAccounts.length; i++) {
                                $('#from_account').append('<option value="' + response.liquidityCryptoAccounts[i].id + '" class="from-account-option">' + response.liquidityCryptoAccounts[i].name + '</option>');
                            }
                        }

                        if (response.liquidityFiatAccounts && response.liquidityFiatAccounts.length > 0) {
                            for (let i = 0; i < response.liquidityFiatAccounts.length; i++) {
                                $('#to_account').append('<option value="' + response.liquidityFiatAccounts[i].id + '" class="to-account-option">' + response.liquidityFiatAccounts[i].name + '</option>');
                            }
                        }
                    } else if (trxType == "{{ \App\Enums\TransactionType::BANK_TRX }}" && fromType == "{{ \App\Enums\Providers::PROVIDER_LIQUIDITY }}") {
                        if (response.liquidityFiatAccounts && response.liquidityFiatAccounts.length > 0) {
                            for (let i = 0; i < response.liquidityFiatAccounts.length; i++) {
                                $('#from_account').append('<option value="' + response.liquidityFiatAccounts[i].id + '" class="from-account-option">' + response.liquidityFiatAccounts[i].name + '</option>');
                            }
                        }

                        if (response.paymentProviders && response.paymentProviders.length > 0) {
                            for (let i = 0; i < response.paymentProviders.length; i++) {
                                $('#to_account').append('<option value="' + response.paymentProviders[i].id + '" class="to-account-option">' + response.paymentProviders[i].name + '</option>');
                            }
                        }
                    } else if (trxType == "{{ \App\Enums\TransactionType::BANK_TRX }}" && fromType == "{{ \App\Enums\Providers::PROVIDER_PAYMENT }}") {
                        if (response.paymentProviders && response.paymentProviders.length > 0) {
                            for (let i = 0; i < response.paymentProviders.length; i++) {
                                $('#from_account').append('<option value="' + response.paymentProviders[i].id + '" class="from-account-option">' + response.paymentProviders[i].name + '</option>');
                            }
                        } else {
                            $('#from_account').append('<option value="" class="from-account-option">' + 'NO account' + '</option>');
                        }
                        $('#to_account').append('<option class="to-account-option" value="' + response.account.id + '">' + response.account.name + '</option>');

                    } else if (trxType == "{{ \App\Enums\TransactionType::REFUND }}" && fromType == "{{ \App\Enums\Providers::CLIENT }}") {
                        if (response.paymentProviders && response.paymentProviders.length > 0) {
                            for (let i = 0; i < response.paymentProviders.length; i++) {
                                $('#to_account').append('<option value="' + response.paymentProviders[i].id + '" class="to-account-option">' + response.paymentProviders[i].name + '</option>');
                            }
                        } else {
                            $('#to_account').append('<option value="" class="to-account-option">' + 'NO account' + '</option>');
                        }
                        $('#from_account').append('<option class="from-account-option" value="' + response.account.id + '">' + response.account.name + '</option>');
                    }
                    else {
                        $('#to_account').append('<option class="to-account-option" value="" selected>' + 'No account' + '</option>');
                        $('#from_account').append('<option value="" class="from-account-option" selected>' + 'No account' + '</option>');
                    }
                }
            })
        }

        //get accounts and put in select depending on chosen type - to
        function getToAccountsByType($id) {
            $('#' + $id).empty();
            $.ajax({
                type: "POST",
                url: '{{route('backoffice.withdraw.wire.from.accounts.by.type')}}',
                data: {
                    "_token": "{{ csrf_token() }}",
                    'from_type': $('.to-type').val(),
                    'operation_id': "{{ $operation->id }}",
                    'c_profile_id': "{{ $operation->c_profile_id }}",
                    'account': $('#to_account').val(),
                    'from': 0,
                    'trx_type': $('#exchange').val(),
                    'fromCurrency': $('#fromFiat').val(),
                    'toCurrency': $('#toCryptocurrency').val(),
                },
                success: function (response) {
                    $('#' + $id).append('<option hidden value="">' + 'Select...' + '</option>');

                    if ($('.to-type').val() == 5) {
                        $('#' + $id).append('<option class="from-account-option" value="' + response.accounts.id + '">' + response.accounts.name + '</option>');
                        $('.crypto-address').val(response.walletAddress);
                    } else if ($('.to-type').val() == "{{ \App\Enums\Providers::PROVIDER_PAYMENT }}" && $('.from-type').val() == "{{ \App\Enums\Providers::CLIENT }}") {
                        if (response.paymentProvider) {
                            $('#' + $id).append('<option selected value="' + response.paymentProvider.id + '">' + response.paymentProvider.name + '</option>');
                        }
                        $('.to-account-option').val('');
                        for (let i = 0; i < response.accounts.length; i++) {
                            $('#' + $id).append('<option value="' + response.accounts[i].id + '" class="to-account-option">' + response.accounts[i].name + '</option>');
                        }
                        getCommissionsToAccount('to_account', 0);
                    } else {
                        $('#' + $id).append('<option hidden value="">' + 'Select...' + '</option>');
                        $('.to-account-option').val('');
                        for (let i = 0; i < response.accounts.length; i++) {
                            $('#' + $id).append('<option value="' + response.accounts[i].id + '" class="to-account-option">' + response.accounts[i].name + '</option>');
                        }
                    }

                }
            })
        }

        //get commissions and show in form, depending on chosen account
        function getCommissionsFromAccount($id, $from) {
            $.ajax({
                type: "POST",
                url: '{{route('backoffice.transaction.get.from.commissions')}}',
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
                        if ($('#exchange').val() == "{{\App\Enums\TransactionType::REFUND}}") {
                            $('#exchange_fee').val(response.commission.refund_transfer)
                            $('#exchange_fee_percent').val(response.commission.refund_transfer_percent)
                        } else {
                            $('#exchange_fee').val(response.commission.fixed_commission)
                            $('#exchange_fee_percent').val(response.commission.percent_commission)
                        }
                        if($('#exchange').val() == "{{ \App\Enums\TransactionType::CRYPTO_TRX }}") {
                            if(response.walletProviderCommission) {
                                $('#blockchain_fee').val(response.walletProviderCommission.blockchain_fee)
                            }else {
                                $('#blockchain_fee').val(response.commission.blockchain_fee)
                            }
                        }else{
                            $('#exchange_fee_min').val(response.commission.min_commission)
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
                url: '{{route('backoffice.transaction.to.from.commissions')}}',
                data: {
                    "_token": "{{ csrf_token() }}",
                    'operation_id': "{{ $operation->id }}",
                    'account': $('#' + $id).val(),
                    'from': $from,
                    'from_type': $('#to_type').val(),
                    'trx_type': $('#exchange').val(),
                },
                success: function (response) {
                    console.log(response)
                    if (response.commission && $('#exchange').val() == 4) {
                        $('#to_fee_percent').val(response.commission.refund_transfer_percent)
                    } else if (response.commission && $('#exchange').val() != 4) {
                        $('#to_fee').val(response.commission.fixed_commission)
                        $('#to_fee_percent').val(response.commission.percent_commission)
                        $('#to_fee_min').val(response.commission.min_commission)
                    } else {
                        $('#to_fee').val('')
                        $('#to_fee_percent').val('')
                        $('#to_fee_min').val('')
                    }
                    if (response.toAddress) {
                        $('#to_address').val(response.toAddress)
                    }else{
                        $('#to_address').val('')
                    }

                }
            })
        }

        //get accounts according currency
        function getAccountsByCurrency($from) {
            $('#from_account').prop('disabled', false);
            $('#to_account').prop('disabled', false);
            getFromAccountsByType('from_account');
            // getToAccountsByType('to_account');
        }

        //enable editing of commissions fields
        function toggleCommissionsEdit() {
            $('.commission').prop('readonly', function (i, v) {
                return !v;
            });
        }

        //display right fields according transactions typr
        function selectForm($id, $step = null) {
            $('#to_address').val(' ');
            $('#from_account').val('');
            $('#to_account').val('');
            $('#currencyAmount').val(0);
            let trxType = $('#' + $id).val();

            $('#' + $id).append('<option hidden value="">' + 'Select...' + '</option>');
            $('#from_account').attr('disabled', 'disabled');
            $('#to_account').attr('disabled', 'disabled');

            if (trxType == "{{ \App\Enums\TransactionType::BANK_TRX }}") {
                appendFiat();
                $('.exchange-rate').attr('hidden', 'hidden');
                $('.exchange-api').attr('hidden', 'hidden');
                $('.to-cryptocurrency').attr('hidden', 'hidden');
                $('.from-currency').attr('hidden', false);
                $('.cryptocurrency-amount').attr('hidden', 'hidden');
                $('.crypto-address').attr('hidden', 'hidden');
                $('#refund-form').attr('hidden', false);
                $('.to-fee').attr('hidden', false);
                $('.exchange-fee').text('From fee');
                $('.exchange-fee-percent').text('From fee %');
                $('.exchange-fee-min').text('From fee minimum');
                $('.add-trx-btn').attr('disabled', false);
                $('.from-fee-minimum').css({'margin-left': '0', 'margin-top': '0'});
                $('.from-fee-minimum').attr('hidden', false);
                $('.blockchain-fee').attr('hidden', 'hidden');
            } else if (trxType == "{{ \App\Enums\TransactionType::CRYPTO_TRX }}") {
                appendCrypto();
                $('#crypto-form').attr('hidden', false);
                $('.exchange-rate').attr('hidden', 'hidden');
                $('.exchange-api').attr('hidden', 'hidden');
                $('.to-cryptocurrency').attr('hidden', 'hidden');
                $('.crypto-address').attr('hidden', false);
                $('.cryptocurrency-amount').attr('hidden', 'hidden');
                $('.exchange-fee').text('From fee');
                $('.exchange-fee-percent').text('From fee %');
                $('.exchange-fee-min').text('Blockchain fee');
                $('.blockchain-fee').attr('hidden', false);
                $('.blockchain-fee-label').text('Blockchain fee');
                $('.to-fee').attr('hidden', 'hidden');
                $('.add-trx-btn').attr('disabled', false);
                $('.from-currency').attr('hidden', false);
                $('.from-fee-minimum').attr('hidden', 'hidden');

                if ($(window).width() > 768) {
                    $('.from-fee-minimum').css({'margin-left': '50%', 'margin-top': '-90px'});
                }

            } else if (trxType == "{{ \App\Enums\TransactionType::EXCHANGE_TRX }}") {
                $('.from-currency').attr('hidden', false);
                $('.blockchain-fee-label').text('Exchange fee minimum');

                $('#exchange-form').attr('hidden', false);
                $('.exchange-rate').attr('hidden', false);
                $('.to-cryptocurrency').attr('hidden', false);
                $('.crypto-address').attr('hidden', 'hidden');
                $('.cryptocurrency-amount').attr('hidden', false);
                $('.exchange-fee').text('Exchange fee');
                $('.exchange-fee-percent').text('Exchange fee %');
                $('.exchange-fee-min').text('Exchange fee minimum');
                $('.blockchain-fee').attr('hidden', false);
                if ($(window).width() > 768) {
                    $('.from-fee-minimum').css({'margin-left': '50%', 'margin-top': '-90px'});
                }
                $('.to-fee').attr('hidden', 'hidden');
                $('.exchange-api').attr('hidden', false);
                $('.add-trx-btn').attr('disabled', false);
                $('.from-currency-label').text('Currency');
                $('.to_currency').text('To currency');
                appendCrypto();
            } else if (trxType == "{{ \App\Enums\TransactionType::REFUND }}") {
                $('.from-currency').attr('hidden', false);
                if ($step != 0) {
                    $('.add-trx-btn').attr('disabled', 'disabled')
                }
                $('.exchange-api').attr('hidden', 'hidden');
                $('.exchange-rate').attr('hidden', 'hidden');
                $('.to-cryptocurrency').attr('hidden', 'hidden');
                $('.cryptocurrency-amount').attr('hidden', 'hidden');
                $('.crypto-address').attr('hidden', 'hidden');
                $('#refund-form').attr('hidden', false);
                $('.to-fee').attr('hidden', false);
                $('.from-fee-minimum').attr('hidden', false);
                $('.blockchain-fee').attr('hidden', 'hidden');
                $('.exchange-fee').text('From fee');
                $('.exchange-fee-percent').text('From fee %');
                $('.exchange-fee-min').text('From fee minimum');
                $('.from-fee-minimum').css({'margin-left': '0', 'margin-top': '0'});
                $('.add-trx-btn').attr('disabled', false);
                appendFiat();
            }
        }

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

        $(document).ready(function () {
            $('body').on('change', '#toCryptocurrency', function () {
                getTicker();
            });
            $('body').on('change', '#fromFiat', function () {
                getTicker();
            });

            function getTicker() {
                let fiat = $('#toCryptocurrency').val();
                let crypto = $('#fromFiat').val();
                let ex = $('#exchange').val();
                let amountTo = $('#amountTo');
                let currencyAmount = $('#currencyAmount').val();
                if (parseInt(ex) === 3 && crypto && fiat) {
                    fiat = $('#toCryptocurrency').val();
                    crypto = $('#fromFiat').val();
                    $.ajax({
                        url: '{{ route('get.ticker') }}',
                        type: 'post',
                        data: {"_token": "{{ csrf_token() }}", crypto, fiat},
                        success: function (data) {
                            $('#exchangeRate').val(data);
                            amountTo.val(currencyAmount / data);
                        }
                    })
                }
            }

            @if (count($errors) > 0)
            $('#exampleModal1').modal('show');
            @endif

            @if(session()->has('showBankDetail'))
            $("#showBankDetail").modal("toggle");
            @endif
        });

        $('body').on('change', '#currencyAmount', function () {
            let amount = $(this).val();
            let rate = $('#exchangeRate').val();
            if (amount && rate) {
                $('#amountTo').val(parseFloat(rate) * parseFloat(amount));
            }
        });

        function setDefaultData() {
            switch ({{ $operation->step }}) {
                case 0:
                    $('#exchange').val({{ \App\Enums\TransactionType::CRYPTO_TRX }});
                    $('#from_type').val( {{App\Enums\Providers::CLIENT}} );
                    $('#to_type').val( {{App\Enums\Providers::PROVIDER_LIQUIDITY}} );
                    $('#crypto-form').attr('hidden', false);
                    $('#refund-form').attr('hidden', false);
                    $('.exchange-rate').attr('hidden', 'hidden');
                    $('.exchange-api').attr('hidden', 'hidden');
                    $('.to-cryptocurrency').attr('hidden', 'hidden');
                    $('.cryptocurrency-amount').attr('hidden', 'hidden');
                    $('.crypto-address').attr('hidden', false);
                    $('#to_address').attr('readonly',true);
                    $('.to-fee').attr('hidden', 'hidden');
                    $('.exchange-fee').text('From fee');
                    $('.exchange-fee-percent').text('From fee %');
                    $('.from-fee-minimum').attr('hidden', 'hidden');
                    $('.blockchain-fee').attr('hidden', false);
                    $('.add-trx-btn').attr('disabled', false);
                    $('.from-currency').attr('hidden', false);
                    if ($(window).width() > 768) {
                        $('.from-fee-minimum').css({'margin-left': '50%', 'margin-top': '-90px'});
                    }
                    getCommissionsFromAccount('from_account', 1)
                    getCommissionsToAccount('to_account', 0)
                    break;
                case 1:
                    $('#exchange-form').attr('hidden', false);
                    $('#exchange').val({{ \App\Enums\TransactionType::EXCHANGE_TRX }});
                    $('#from_type').val({{App\Enums\Providers::PROVIDER_LIQUIDITY}});
                    $('#to_type').val({{App\Enums\Providers::PROVIDER_LIQUIDITY}});
                    $('.exchange-rate').attr('hidden', false);
                    $('.exchange-api').attr('hidden', false);
                    $('.to-cryptocurrency').attr('hidden', false);
                    $('.crypto-address').attr('hidden', 'hidden');
                    $('.cryptocurrency-amount').attr('hidden', false);
                    $('.exchange-fee').text('Exchange fee');
                    $('.exchange-fee-percent').text('Exchange fee %');
                    $('.exchange-fee-min').text('Exchange fee minimum');
                    if ($(window).width() > 768) {
                        $('.from-fee-minimum').css({'margin-left': '50%', 'margin-top': '-90px'});
                    }
                    $('.add-trx-btn').attr('disabled', false);
                    $('.to-fee').attr('hidden', 'hidden');
                    $('.from-currency').attr('hidden', false);
                    $('.from-currency-label').text('Currency');
                    $('.to_currency').text('To currency');
                    $('.from-fee-minimum').attr('hidden', false);
                    $('.blockchain-fee').attr('hidden', 'hidden');
                    getCommissionsFromAccount('from_account', 1)
                    getCommissionsToAccount('to_account', 0)
                    break;
                default:
                    $('#exchange').val({{ \App\Enums\TransactionType::BANK_TRX }});
                    $('#from_type').val( {{App\Enums\Providers::PROVIDER_PAYMENT}} );
                    $('#to_type').val( {{App\Enums\Providers::CLIENT}} );
                    $('.exchange-rate').attr('hidden', 'hidden');
                    $('.exchange-api').attr('hidden', 'hidden');
                    $('.to-cryptocurrency').attr('hidden', 'hidden');
                    $('.cryptocurrency-amount').attr('hidden', 'hidden');
                    $('.crypto-address').attr('hidden', 'hidden');
                    $('#currencyAmount').val(0);
                    $('#fromFiat').val('');
            }
            $('#toCryptocurrency').trigger('change');
        }

        $('body').on('change', '#currencyAmount', function () {
            let amount = $(this).val();
            let rate = $('#exchangeRate').val();
            if (amount && rate) {
                $('#amountTo').val(parseFloat(rate) * parseFloat(amount));
            }
        });


        function declineOperation() {
            $("#changeOperationStatus").modal("toggle");
            $("#showBankDetail").modal("hide");
        }
    </script>
@endsection
