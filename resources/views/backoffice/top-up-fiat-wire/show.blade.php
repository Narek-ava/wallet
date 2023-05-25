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
        - Operation {{ $operation->operation_id }}

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
                    <h6 class="d-block">{{ t('deposit_type') }}</h6>
                    <p class="d-block">
                        {{ $operation->getOperationType() }}
                    </p>
                    <h6 class="d-block">{{ t('deposit_method') }}</h6>
                    <p class="d-block">
                        {{ $operation->getOperationMethodName() }}
                    </p>
                    <h6 class="d-block">{{ t('currency') }}</h6>
                    <p class="d-block">{{ $operation->from_currency ?? '-' }}</p>
                    <h6 class="d-block">{{ t('bank_country') }}</h6>
                    <p class="d-block">{{ ($operation->fromAccount && $operation->fromAccount->country) ? \App\Models\Country::getCountryNameByCode($operation->fromAccount->country) : '-' }}</p>

                </div>
                <div class="col-md-2 px-3 ml-3">
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
                    <h6 class="d-block">{{ t('deposit_fee') }}</h6>
                    <p class="d-block">{{ $commissions ? ($commissions->percent_commission ?? '-') . '% ' . '(Min. ' . ($commissions->min_commission ?? '-') . ' ' . $operation->from_currency . ')': '-' }}</p>
                    <h6 class="d-block">{{ t('trx_limit') }}</h6>
                    <p class="d-block">{{ $limits ? $limits->transaction_amount_max : '-' }}</p>
                </div>
                <div class="col-md px-3 ml-3">
                    <h6 class="d-block">{{ t('available_limit') }}</h6>
                    <p class="d-block">{{ number_format($availableMonthlyAmount, 2)  ?? '-' }}</p>
                    <h6 class="d-block">{{ t('cratos_bank') }}</h6>
                    <p class="d-block">{{ ($paymentProviderAccount->provider->name ?? ' '). ' - '. ($paymentProviderAccount->name ?? '-') }}</p>
                    <h6 class="d-block">{{ t('unique_reference') }}</h6>
                    <p class="d-block">{{ $operation->id  ?? '-'}}</p>
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
                    <button class="btn btn-lg btn-primary themeBtn register-buttons mb-4 mb-md-0 round-border"
                            data-toggle="modal" data-target="#changeOperationStatus"
                            type="submit">{{ t('change_status') }}
                    </button>

                    <!-- Button trigger modal -->
                    @include('backoffice.transactions._change-status')
                </div>
            </div>
        </div>
    </div>


    <div class="row">
        <div class="col-md-4">
            <h2 class="mt-5">{{ t('bank_details') }}</h2>
            <div class="common-shadow-theme pt-3 pb-3">
                <h5 class="d-block ml-4 mt-2">{{ t('create_new') }}</h5>
                <!-- Button triggers modal - add bank detail-->
                <button type="button" class="btn themeBtn ml-4 round-border" data-toggle="modal"
                        data-target="#exampleModal">
                    {{ t('create') }}
                </button>
                @include('backoffice.top-up-fiat-wire._add-bank-detail')
            </div>
        </div>
        {{--        Show compliance section when transaction amount is more than allowed for current level--}}
        @include('backoffice.transactions._compliance')
    </div>

    @if($operation->from_account)
        <div class="row mt-5">
            <h2 class="mt-1 ml-4">{{ t('transactions') }}</h2>
            <!-- Button triggers modal - add transaction-->
            <button type="button" class="btn themeBtn ml-5 h-25 round-border" data-toggle="modal"
                    data-target="#exampleModal1" onclick="setDefaultData()">
                {{ t('add') }}
            </button>

            @include('backoffice.top-up-fiat-wire._add-transaction')
        </div>
    @endif
    @include('backoffice.partials.transactions.transaction-history')
    @include('backoffice.top-up-fiat-wire.operation-steps')
    @include('backoffice.partials.transactions.transaction-fees')
    @include('backoffice.partials.operation_logs',['logs' => $operationLogs ])

    <script src="/js/backoffice/transactions.js"></script>
    <script>
        //get accounts and put in select depending on chosen type -from
        function getFromAccountsByType($id) {
            $('#' + $id).empty();
            $.ajax({
                type: "POST",
                url: '{{ route('backoffice.from.accounts.by.type') }}',
                data: {
                    "_token": "{{ csrf_token() }}",
                    'from_type': $('.from-type').val(),
                    'from': 1,
                    'fromCurrency': $('#fromFiat').val(),
                    'toCurrency': $('#toCryptocurrency').val(),
                    'operation_id': "{{ $operation->id }}",
                    'c_profile_id': "{{ $operation->c_profile_id }}",
                    'account': $('#from_account').val(),
                    'trx_type': $('#exchange').val(),
                },
                success: function (response) {
                    $('#' + $id).append('<option hidden value="">' + 'Select...' + '</option>');

                    if ($('.from-type').val() == 5) {
                        $('#' + $id).append('<option class="from-account-option" value="">' + response.accounts.name + '</option>');
                        $('.from-account-option').val(response.accounts.id);
                    } else {
                        $('#' + $id).append('<option hidden value="">' + 'Select...' + '</option>');
                        $('.from-account-option').val('');
                        for (let i = 0; i < response.accounts.length; i++) {
                            $('#' + $id).append('<option value="' + response.accounts[i].id + '" class="from-account-option">' + response.accounts[i].name + '</option>');
                        }
                    }
                }
            })
        }

        //get accounts and put in select depending on chosen type - to
        function getToAccountsByType($id) {
            $('#' + $id).empty();
            $.ajax({
                type: "POST",
                url: '{{route('backoffice.to.accounts.by.type')}}',
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

                        for (let i = 0; i < response.accounts.length; i++) {
                            let isPaymentProvider = response.paymentProvider && response.paymentProvider.id && response.paymentProvider.id == response.accounts[i].id;
                            let hidden = '';
                            if (isPaymentProvider) {
                                hidden = 'hidden';
                            }

                            $('#' + $id).append('<option value="' + response.accounts[i].id + '"  class="to-account-option" ' + hidden + ' >' + response.accounts[i].name + '</option>');
                        }
                        getCommissionsToAccount('to_account', 0);
                    } else {
                        $('#' + $id).append('<option hidden value="">' + 'Select...' + '</option>');
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
                        $('#exchange_fee').val(response.commission.fixed_commission)
                        $('#exchange_fee_percent').val(response.commission.percent_commission)
                        $('#exchange_fee_min').val(response.commission.min_commission)
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
                    if (response.commission && $('#exchange').val() == 4) {
                        $('#to_fee_percent').val(response.commission.refund_transfer_percent)
                        $('#to_fee').val(response.commission.refund_transfer)
                        $('#to_fee_min').val(response.commission.refund_transfer_percent)
                        if (response.refundAmount) {
                            $('#currencyAmount').val(response.refundAmount)
                            $('#currencyAmount').prop('max', response.refundAmount)
                        }
                    } else if (response.commission && $('#exchange').val() != 4) {
                        $('#to_fee').val(response.commission.fixed_commission)
                        $('#to_fee_percent').val(response.commission.percent_commission)
                        $('#to_fee_min').val(response.commission.min_commission)
                    } else {
                        $('#to_fee').val('')
                        $('#to_fee_percent').val('')
                        $('#to_fee_min').val('')
                    }

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
            getToAccountsByType('to_account');
        }

        //enable editing of commissions fields
        function toggleCommissionsEdit() {
            $('.commission').prop('readonly', function (i, v) {
                return !v;
            });
        }

        //display right fields according transactions typr
        function selectForm($id, $step = null) {
            let trxType = $('#' + $id).val();

            $('#' + $id).append('<option hidden value="">' + 'Select...' + '</option>');
            $('#from_account').attr('disabled', 'disabled');
            $('#to_account').attr('disabled', 'disabled');
            $('.commissionFee').show();

            if (trxType == 1) {
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
                $('.exchange-fee-min').text('From fee minimum');
                $('.add-trx-btn').attr('disabled', false);
                $('.from-fee-minimum').css({'margin-left': '0', 'margin-top': '0'});
            } else if (trxType == 2) {
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
            } else if (trxType == 4) {
                if ($step != 0) {
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

            function getTicker() {
                let crypto = $('#toCryptocurrency').val();
                let fiat = $('#fromFiat').val();
                let ex = $('#exchange').val();
                let amountTo = $('#amountTo');
                let currencyAmount = $('#currencyAmount').val();
                if (parseInt(ex) === 3 && crypto && fiat) {
                    crypto = $('#toCryptocurrency').val();
                    fiat = $('#fromFiat').val();
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

            $('#type').on('change', function () {
                let swiftType = $('.type_swift').val();
                if($(this).val() === swiftType) {
                    $('.correspondent_bank_details').removeAttr('hidden')
                    $('.intermediary_bank_details').removeAttr('hidden')
                }else {
                    $('.correspondent_bank_details').attr('hidden', true)
                    $('.intermediary_bank_details').attr('hidden', true)
                }
            })

            @if($errors->any())
            @if($errors->has('bank_detail_type'))
            $('.correspondent_bank_details').removeAttr('hidden')
            $('.intermediary_bank_details').removeAttr('hidden')
            @endif
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
            $('#exchange').val(1);
            $('#from_type').val(5);
            $('#to_type').val(1);
            $('.exchange-rate').attr('hidden', 'hidden');
            $('.exchange-api').attr('hidden', 'hidden');
            $('.to-cryptocurrency').attr('hidden', 'hidden');
            $('.cryptocurrency-amount').attr('hidden', 'hidden');
            $('.crypto-address').attr('hidden', 'hidden');
            $('#refund-form').attr('hidden', false);
            $('.to-fee').attr('hidden', false);
            $('.exchange-fee').text('From fee');
            $('.exchange-fee-percent').text('From fee %');
            $('.exchange-fee-min').text('From fee minimum');

            getFromAccountsByType('from_account');
            getToAccountsByType('to_account');
            appendFiat();
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
    </script>
@endsection
