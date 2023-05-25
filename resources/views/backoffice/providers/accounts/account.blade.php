@extends('backoffice.layouts.backoffice')
@section('title', t('dashboard_account') )

@section('content')
    <div class="row mb-5 pb-2">
        <div class="col-md-12">
            <h2 class="mb-3 large-heading-section">{{ t('dashboard') }}</h2>
            <div class="row">
                <div class="col-lg-5 d-block d-md-flex justify-content-between">
                    <div class="balance mr-2">
                        <p>{{ t('dashboard_title') }}</p>
                    </div>
                </div>
                @include('cabinet.partials.notification', ['notify' => getNotification(\Illuminate\Support\Facades\Auth::id()), 'admin' => true])
            </div>
        </div>
    </div>
    @if(session()->has('success'))
        <div class="alert alert-success">
            <p>   {{ t(session()->get('success')) }} </p>
        </div>
    @endif

    <div class="row">
        <div class="col-md-12">
            <h2>{{ t('ui_accounts') }}</h2>
        </div>
        <div class="col-md-12">
            <h2 class="mb-4" style="display: inline-block">{{ t('dashboard_account') }} - {{ $account->name }}</h2>
            @if($account->provider->provider_type == \App\Enums\Providers::PROVIDER_CARD)
                <select name="status" class="ml-3" id="status" style="padding-right: 50px;" onchange='changeProviderAccountStatus("{{ $account->id }}")'>
                    @foreach(\App\Enums\PaymentProvider::getList() as $key => $status)
                        <option value="{{ $key }}" @if($account->$status == $key) selected @endif>{{ $status }}</option>
                    @endforeach
                </select>
            @endif
        </div>
    </div>

    <div class="col-md-12">
        <div class="row">
            @if($currentAdmin->hasPermissionInAnyProject([\App\Enums\BUserPermissions::TOP_UP_AND_WITHDRAW_BY_PROVIDERS_ACCOUNT]))
                @include('backoffice.providers.accounts._make-top-up-operation')
                @include('backoffice.providers.accounts._make-withdraw-operation')
            @endif

            <div class="col-md-4 ml-0 @if($showFeeTransactions) dashboardAccountInactive @else red-border @endif dashboardAccount" id="mainAccount" data-transaction-group="{{ \App\Enums\TransactionType::GROUP_TRX }}">
                <p class="textBold fs20">{{ $account->name }}</p>
                <p class="textBold textRed fs20"> {{ moneyFormatWithCurrency($account->currency, $account->getAvailableBalance()) }} </p>
                @if($currentAdmin->hasPermissionInAnyProject([\App\Enums\BUserPermissions::TOP_UP_AND_WITHDRAW_BY_PROVIDERS_ACCOUNT]))
                    <div class="row">
                        @if($account->provider->provider_type == \App\Enums\Providers::PROVIDER_LIQUIDITY ||
                            $account->provider->provider_type == \App\Enums\Providers::PROVIDER_PAYMENT)
                            <div class="col-md-6">
                                <button type="button"
                                        data-modal-type="{{ \App\Enums\TransactionType::PROVIDER_TOP_UP_TRX }}"
                                        class="btn themeBtnWithoutHover topUp" data-toggle="modal"
                                        data-target="#createProviderOperation">
                                    {{ t('topup') }}
                                </button>
                            </div>
                        @endif
                        <div class="col-md-6">
                            <button type="button"
                                    data-modal-type="{{ \App\Enums\TransactionType::PROVIDER_WITHDRAW_TRX }}"
                                    class="btn themeBtnWithoutHover topUp" data-toggle="modal"
                                    data-target="#createProviderWithdrawOperation">
                                {{ t('withdraw') }}
                            </button>
                        </div>
                    </div>
                @endif

            </div>
            <div class="col-md-4 ml-0 @if($showFeeTransactions) red-border @else dashboardAccountInactive @endif dashboardAccount" id="feeAccount"  data-transaction-group="{{ \App\Enums\TransactionType::GROUP_FEE_TRX }}">
                <p class="textBold fs20">{{ $account->childAccount->name }}</p>
                <p class="textBold textRed fs20"> {{ moneyFormatWithCurrency($account->childAccount->currency, $account->childAccount->getAvailableBalance()) }} </p>

{{--                @if($account->provider->provider_type == \App\Enums\Providers::PROVIDER_LIQUIDITY ||--}}
{{--                    $account->provider->provider_type == \App\Enums\Providers::PROVIDER_PAYMENT)--}}
{{--                    <div class="row">--}}
{{--                        <div class="col-md-6">--}}
{{--                            <button class="btn themeBtnWithoutHover topUp">{{ t('topup') }}</button>--}}
{{--                        </div>--}}
{{--                        <div class="col-md-6">--}}
{{--                            <button class="btn themeBtnWithoutHover withdraw">{{ t('withdraw') }}</button>--}}
{{--                        </div>--}}
{{--                    </div>--}}
{{--                @endif--}}
            </div>
        </div>
    </div>

    <div class="row mt-5">
        @php
            /* @var \App\Models\Account $account*/
            $isCard = $account->provider->provider_type == \App\Enums\Providers::PROVIDER_CARD
        @endphp
            <div class="col-md-1 textBold">{{ t('id') }}</div>
            <div class="col-md-2 textBold">{{ t('amount') }}</div>
        <div class="col-md-2 textBold">{{ t('transaction_type') }}</div>
        <div class="col-md-4 textBold">{{ t('date') }}</div>
        <div class="col-md-2 textBold">{{ t('status') }}</div>
        <div class="col-md-1 textBold"></div>
    </div>
    <form action="" id="transactionFilterForm">
        <div class="row mt-3 pl-1">
            <input type="hidden" name="transaction_group" value="{{ request()->has('transaction_group') ? request()->get('transaction_group') : \App\Enums\TransactionType::GROUP_TRX}}">
            <div class="col-md-1 pt-2">
                <input type="number" name="transaction_id" value="{{ request()->transaction_id }}">
            </div>
            <div class="col-md-2 pt-2">
                <input type="text" name="amount"
                       value="{{ request()->amount }}">
            </div>
            <div class="col-md-2">
                <select class="w-100" name="transaction_type">
                    <option value=""> All </option>
                    @if($account->provider->provider_type == \App\Enums\Providers::PROVIDER_CARD)
                        @foreach(\App\Enums\TransactionType::CARD_PROVIDER_FILTER_TYPES as $key => $name)
                            <option
                                value="{{ $key }}" {{ request()->transaction_type == $key ? 'selected' : '' }}>{{ t($name) }}</option>
                        @endforeach
                    @else
                        @if($account->provider->provider_type == \App\Enums\Providers::PROVIDER_LIQUIDITY)
                            <option
                                value="{{ \App\Enums\TransactionType::EXCHANGE_TRX }}" {{ request()->transaction_type == \App\Enums\TransactionType::EXCHANGE_TRX ? 'selected' : '' }}>{{ \App\Enums\TransactionType::getName(\App\Enums\TransactionType::EXCHANGE_TRX) }}</option>
                        @endif
                        @foreach(\App\Enums\TransactionType::PROVIDER_TRX_TYPES as $key => $name)
                            <option
                                value="{{ $key }}" {{ request()->transaction_type == $key ? 'selected' : '' }}>{{ t($name) }}</option>
                        @endforeach
                    @endif
                </select>
            </div>
            <div class="col-md-2 pt-2">
                <div class="form-group">
                    <input class="date-inputs display-sell w-100" name="from" id="from" value="{{ request()->from }}" placeholder="From date" autocomplete="off">
                </div>
            </div>
            <div class="col-md-2 pt-2">
                <div class="form-group">
                    <input class="date-inputs display-sell w-100" name="to" id="to" value="{{ request()->to }}" placeholder="To date">
                </div>
            </div>
            <div class="col-md-2">
                <select class="w-100" name="status">
                    <option value="-1" {{ request()->status == -1 ? 'selected' : '' }}></option>
                    @foreach(\App\Enums\TransactionStatuses::NAMES as $key => $status)
                        <option
                            value="{{ $key }}" {{ request()->status == $key ? 'selected' : '' }}>{{ $status }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-1">
                <button class="btn btn-lg btn-primary themeBtn mb-4 btn-radiused" type="submit">Find</button>
            </div>
        </div>
    </form>

    <div class="row mt-4 pl-3">
        <div class="col-md-12 pl-0">
            <h2 class="mb-4">{{ t('transactions') }}</h2>
        </div>
    </div>
    <div class="row mt-4 pl-3">
        @if ($transactions->count())
            <div class="col-md-1 activeLink">{{ t('ui_no') }}</div>
            <div class="col-md-{{ $isCard ? 1 : 2 }} activeLink">{{ t('type') }}</div>
            <div class="col-md-1 activeLink">{{ t('date') }}</div>
            <div class="col-md-2 activeLink">{{ t('from') }}</div>
            <div class="col-md-1 activeLink">{{ t('amount') }}</div>
            <div class="col-md-1 activeLink">{{ t('currency') }}</div>
{{--            <div class="col-md-1 activeLink">{{ t('id') }}</div>--}}
            @if($isCard)
                <div class="col-md-1 activeLink">{{ t('fee') }}</div>
            @endif
            <div class="col-md-1 activeLink">{{ t('balance') }}</div>
            <div class="col-md-1 activeLink">{{ t('status') }}</div>
            <div class="col-md-1 activeLink">{{ t('details') }}</div>
            <div class="col-md-12">
                @foreach($transactions as $transaction)
                    @php
                        /* @var \App\Models\Transaction $transaction */
                        $link = '';
                        if(in_array($transaction->operation->operation_type, [ \App\Enums\OperationOperationType::TYPE_WITHDRAW_CRYPTO ,  \App\Enums\OperationOperationType::TYPE_TOP_UP_CRYPTO, \App\Enums\OperationOperationType::TYPE_WITHDRAW_CRYPTO_PF, \App\Enums\OperationOperationType::TYPE_TOP_UP_CRYPTO_PF, \App\Enums\OperationOperationType::TYPE_TOP_UP_CRYPTO_EXTERNAL_PF]) ) {
                            $link = route('backoffice.withdraw.crypto.transaction', $transaction->operation->id);
                        }elseif($transaction->operation->operation_type == \App\Enums\OperationOperationType::TYPE_WITHDRAW_WIRE_SEPA
                        || $transaction->operation->operation_type == \App\Enums\OperationOperationType::TYPE_WITHDRAW_WIRE_SWIFT) {
                            $link = route('backoffice.withdraw.wire.transaction', $transaction->operation->id);
                        }elseif (in_array($transaction->operation->operation_type, [\App\Enums\OperationOperationType::TYPE_CARD,  \App\Enums\OperationOperationType::MERCHANT_PAYMENT, \App\Enums\OperationOperationType::TYPE_CARD_PF])) {
                            $link = route('backoffice.card.transaction', $transaction->operation->id);
                        }
                        else {
                            $link = route('dashboard.provider.operation.details', ['operation' => $transaction->operation->id, 'account' => $account]);
                        }
                    @endphp
                    <div class="row providersAccounts-item" data-transaction-type="{{ $transaction->type }}">
                        <div class="col-md-1 breakWord">{{ $transaction->transaction_id }}</div>
                        <div
                            class="col-md-{{ $isCard ? 1 : 2 }} breakWord">{{ \App\Enums\TransactionType::getName($transaction->type) }}</div>
                        <div class="col-md-1 breakWord">{{ $transaction->created_at->format('Y-m-d') }}</div>
                        <div
                            class="col-md-2 breakWord">{{ $transaction->fromAccount->cardAccountDetail->card_number ?? $transaction->fromAccount->name }}</div>
                        <div class="col-md-1 breakWord">
                            {{ (($transaction->trans_amount > 0) ? ($transaction->from_account == $currentAccount->id ? '-' : '+') : '') . generalMoneyFormat($transaction->trans_amount, $transaction->fromAccount->currency) }}
                        </div>
                        <div class="col-md-1 breakWord">{{ $transaction->fromAccount->currency }}</div>
{{--                        <abbr class="col-md-1"  style="display: block; overflow: hidden; white-space: nowrap; text-overflow: ellipsis;" title="{{ $transaction->tx_id }}"> {{$transaction->tx_id ?? '' }}</abbr>--}}
                        @if($isCard)
                            <div class="col-md-1 breakWord">
                                {{ generalMoneyFormat($transaction->calculateTransactionFee($account), $transaction->fromAccount->currency) . ' ' . $transaction->fromAccount->currency }}
                            </div>
                        @endif
                        <div class="col-md-1 breakWord">{{ generalMoneyFormat($currentAccount->calculateBalance($transaction), $currentAccount->currency)  }}</div>
                        <div
                            class="col-md-1 breakWord">{{ \App\Enums\TransactionStatuses::getName($transaction->status) }}</div>

                        @if($currentAdmin->hasPermissionInAnyProject([\App\Enums\BUserPermissions::VIEW_API_CLIENTS]))
                            <div class="col-md-1 ">
                                <a href="{{ $link }}">{{ 'Operation #' . $transaction->operation->operation_id }}</a>
                            </div>
                        @endif
                    </div>
                @endforeach
                {{ $transactions->links() }}
            </div>
        @else
            <h6 class="textBold">{{ t('ui_non_transaction') }}</h6>
        @endif
    </div>

    @if($account->provider->provider_type == \App\Enums\Providers::PROVIDER_CARD)
        <div class="modal fade" id="transactionDetail" tabindex="-1" aria-labelledby="exampleModalLabel">
            <div class="modal-dialog">
                <div class="modal-content">
                    <div class="modal-header">
                        <h4 class="modal-title" id="exampleModalLabel">{{ t('transaction_details') }}</h4>
                        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                            <span aria-hidden="true">×</span>
                        </button>
                    </div>
                    <div class="modal-body">
                        <div>
                            <form action="{{ route('dahboard.withdraw.card.to.payment') }}" method="post">
                                @csrf
                                <div class="row">
                                    <div class="col-md-3">
                                        <div class="form-group">
                                            <label for="date" class="font-weight-bold">{{ t('date') }}</label>
                                            <input name="date" id="date" data-provide="datepicker"
                                                   data-date-format="yyyy-mm-dd"
                                                   class="form-control grey-rounded-border" autocomplete="off">
                                            @error('date')
                                            <div class="error text-danger">{{ $message }}</div>@enderror
                                        </div>
                                    </div>
                                    <div class="col-md-3">
                                        <div class="form-group">
                                            <label for="exchange"
                                                   class="font-weight-bold">{{ t('transaction_type') }}</label>
                                            <select id="exchange"
                                                    class="form-control grey-rounded-border transaction-type"
                                                    name="transaction_type">
                                                <option selected
                                                        value="{{ \App\Enums\TransactionType::BANK_TRX }}">{{ \App\Enums\TransactionType::getName(\App\Enums\TransactionType::BANK_TRX) }}</option>
                                            </select>
                                            @error('transaction_type')
                                            <div class="error text-danger">{{ $message }}</div>@enderror
                                        </div>
                                    </div>
                                </div>
                                <div class="row">
                                    <div class="col-md-3">
                                        <div class="form-group">
                                            <label for="from_type" class="font-weight-bold">{{ t('from_type') }}</label>
                                            <select id="from_type" class="form-control grey-rounded-border from-type"
                                                    name="from_type">
                                                <option selected
                                                        value="{{ $account->provider->provider_type }}">{{ App\Enums\Providers::NAMES[$account->provider->provider_type] }}</option>
                                            </select>
                                            @error('from_type')
                                            <div class="error text-danger">{{ $message }}</div>@enderror
                                        </div>
                                    </div>
                                    <div class="col-md-3">
                                        <div class="form-group from-account">
                                            <label for="from_account"
                                                   class="font-weight-bold">{{ t('account') }}</label>
                                            <select id="from_account" class="form-control grey-rounded-border"
                                                    name="from_account">
                                                <option selected
                                                        value="{{ $account->id }}">{{ $account->name }}</option>
                                            </select>
                                            @error('from_account')
                                            <div class="error text-danger">{{ $message }}</div>@enderror
                                        </div>
                                    </div>
                                    <div class="col-md-3">
                                        <div class="form-group">
                                            <label for="to_type" class="font-weight-bold">{{ t('to_type') }}</label>
                                            <select class="form-control grey-rounded-border to-type" name="to_type"
                                                    id="to_type">
                                                <option selected
                                                        value="{{ App\Enums\Providers::PROVIDER_PAYMENT }}">{{ App\Enums\Providers::NAMES[App\Enums\Providers::PROVIDER_PAYMENT] }}</option>
                                            </select>
                                            @error('to_type')
                                            <div class="error text-danger">{{ $message }}</div>@enderror
                                        </div>
                                    </div>
                                    <div class="col-md-3">
                                        <div class="form-group">
                                            <label for="to_account" class="font-weight-bold">{{ t('account') }}</label>
                                            <select id="to_account" class="form-control grey-rounded-border"
                                                    name="to_account">
                                                <option value=""></option>
                                            </select>
                                            @error('to_account')
                                            <div class="error text-danger">{{ $message }}</div>@enderror
                                        </div>
                                    </div>
                                </div>
                                <div class="row">
                                    <div class="col-md-3 from-currency">
                                        <div class="form-group">
                                            <label for="fromFiat"
                                                   class="font-weight-bold from-currency-label">{{ t('currency') }}</label>
                                            <select class="form-control grey-rounded-border from_currency" id="fromFiat"
                                                    name="from_currency">
                                                @foreach(App\Enums\Currency::FIAT_CURRENCY_NAMES as $key => $currencyType)
                                                    @if ($account->currency === $currencyType)
                                                        <option selected
                                                                value="{{ $currencyType }}">{{ $currencyType }}</option>
                                                        @continue
                                                    @endif
                                                @endforeach
                                            </select>
                                            @error('from_currency')
                                            <div class="error text-danger">{{ $message }}</div>@enderror
                                        </div>
                                    </div>
                                    <div class="col-md-3">
                                        <div class="form-group">
                                            <label for="currency_amount"
                                                   class="font-weight-bold">{{ t('amount') }}</label>
                                            <input type="number" class="form-control grey-rounded-border"
                                                   value="{{ old('currency_amount') }}" name="currency_amount" min="0"
                                                   id="currencyAmount" step="any">
                                            @error('currency_amount')
                                            <div class="error text-danger">{{ $message }}</div>@enderror
                                        </div>
                                    </div>
                                </div>
                                <div class="row">
                                    <div class="col-md-6">
                                        <button class="btn themeBtn round-border add-trx-btn"
                                                type="submit"> {{ t('save') }}</button>
                                    </div>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    @endif


@endsection

@section('scripts')
    <script>
        $(document).ready(function () {
            $("#transactionDetail").on('shown.bs.modal', function () {
                let providerId = $('#to_type :selected').val();
                let currency = $('#fromFiat :selected').val();
                let date = new Date;
                let year = date.getFullYear();
                let month = date.getMonth() + 1;
                let day = date.getDate();
                if (month < 10) {
                    month = '0' + month;
                }
                if (day < 10) {
                    day = '0' + day;
                }
                let todayDate = year + '-' + month + '-' + day;
                $('#date').val(todayDate)
                $.ajax({
                    url: '/backoffice/to-provider-accounts/' + providerId + '/' + currency,
                    success(data) {
                        let to_account = '{{ old('to_account') }}';
                        let selected = '';
                        for (let i = 0; i < data.length; i++) {
                            if (to_account == data[i].id) {
                                selected = 'selected';
                            }
                            $('#to_account').append(`<option value="${data[i].id}" ${selected}>${data[i].name}</option>`);
                        }
                    }
                });
            });

            $('.dashboardAccount').click(function (e) {
                $('input[name="transaction_group"]').val($(this).data('transaction-group'));

                if ($(e.target).data('target') === undefined) {
                    $('#transactionFilterForm').submit();
                }
            })
        })

        //get accounts and put in select depending on chosen type -from
        function getFromAccountsByType($id, from = null) {
            $('#' + $id).empty();
            $.ajax({
                type: "POST",
                url: "{{ route('backoffice.provider.from.accounts.by.type') }}",
                data: {
                    "_token": "{{ csrf_token() }}",
                    'from_type': $('.from-type').val(),
                    'to_type': $('.to-type').val(),
                    'from': from,
                    'currency': $('#currency').val(),
                },
                success: function (response) {
                    $('#' + $id).removeAttr('disabled')
                    $('#' + $id).append('<option hidden value="">' + '' + '</option>');

                    let checkType , getOption , optionClassName;
                    if(response.from) {
                        checkType = $('.from-type');
                        getOption = $('.from-account-option');
                        optionClassName = 'from-account-option';
                    }else {
                        checkType = $('.to-type');
                        getOption = $('.to-account-option');
                        optionClassName = 'to-account-option';
                    }
                    if (checkType.val() == 5) {
                        $('#' + $id).append('<option selected class=" ' + optionClassName + '" value="">' + response.accounts.name + '</option>');
                        getOption.val(response.accounts.id);
                    } else {
                        $('#' + $id).append('<option hidden value="">' + '' + '</option>');
                        getOption.val('');
                        let selectSecond = false;
                        for (let i = 0; i < response.accounts.length; i++) {
                            if (response.accounts[i].id == "{{ $account->id }}") {
                                if (i == 0) {
                                    selectSecond = true;
                                }
                                continue;
                            }
                            let appendingHtml = '<option value="' + response.accounts[i].id + '" ';
                            if (i == 0 || selectSecond) {
                                appendingHtml += ' selected ';
                            }
                            appendingHtml += 'class="' + optionClassName +' ">' + response.accounts[i].name + '</option>';
                            $('#' + $id).append(appendingHtml);
                        }
                    }
                }
            })
        }

        function changeProviderAccountStatus($accountId)
        {
            $.ajax({
                type: "POST",
                url: "{{ route('backoffice.change.provider.account.status') }}",
                data: {
                    "_token": "{{ csrf_token() }}",
                    'accountId': $accountId,
                    'status': $('#status').val(),
                },
                success: function (response) {

                }
            })
        }

    </script>

    <script type="text/javascript">
        @if (count($errors) > 0 && $errors->has('transaction_modal_type'))
             $('[data-modal-type="{{$errors->get('transaction_modal_type')[0]}}"]').click();
        @endif
    </script>
@endsection
