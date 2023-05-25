@extends('backoffice.layouts.backoffice')
@section('title', t('title_wallet_provider_page'))

@section('content')
    @if (isset($errors) && count($errors) > 0)
        <div id="containErrors"></div>
    @endif
    <div class="row mb-4 pb-4">
        <div class="col-md-12">
            <h2 class="mb-3 large-heading-section">{{ t('settings') }}</h2>
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
    <div class="col-md-12">
        <h2 style="display: inline;margin-right: 25px;">{{ t('title_wallet_provider_page') }}</h2>
        @if($currentAdmin->hasPermissionInAnyProject([\App\Enums\BUserPermissions::ADD_EDIT_PROVIDERS]))
            <button class="btn" style="border-radius: 25px;background-color: #000;color: #fff" data-toggle="modal"
                    id="addProviderBtn" data-target="#provider">{{ t('add') }}</button>
        @endif
        <p>
            <input type="checkbox" id="providerAll"><label for="providerAll" style="margin-left: 15px">{{ t('ui_view_all') }}</label>
        </p>
    </div>
    <div class="col-md-12">
        <div class="row" id="providersSection">
            @foreach($providers as $provider)
                <div class="@if(\Illuminate\Support\Facades\Session::has('success') &&
                   \Illuminate\Support\Facades\Session::get('payment_provider_id') ==  $provider->id )
                    red-border
                @elseif(! \Illuminate\Support\Facades\Session::has('success')) {{ $provider->id === $providerId ? 'red-border' : '' }}
                    @endif col-md-3 providers-section" data-provider-id="{{$provider->id}}" style="cursor:pointer;">
                    <p class="activeLink provider-name">{{ $provider->name }}</p>
                    <p class="providers-section-dates">Created: {{ $provider->created_at }}</p>
                    <p class="providers-section-dates">Last change: {{ $provider->updated_at }}</p>
                    <div class="providers-section-status">{{ \App\Enums\PaymentProvider::getName($provider->status) }}</div>
                    @if($currentAdmin->hasPermissionInAnyProject([\App\Enums\BUserPermissions::ADD_EDIT_PROVIDERS]))
                        <div class="editProvider" data-provider-id="{{ $provider->id }}">Edit</div>
                    @endif
                </div>
            @endforeach
        </div>
    </div>

    <div class="col-md-12 mt-5">
        @if($message = \Illuminate\Support\Facades\Session::get('success'))
        <div class="alert alert-success alert-dismissible">
            <h4>{{ $message }}</h4>
            <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                <span aria-hidden="true">&times;</span>
            </button>
        </div>
        @endif
        <div class="row">
            <div class="col-md-12 mb-3" id="accountsHeaderSection">
                <h3 style="display: inline;margin-right: 25px;">Accounts</h3>
                @if($providerId && ($currentAdmin->hasPermissionInAnyProject([\App\Enums\BUserPermissions::ADD_EDIT_PROVIDERS])))
                    <button class="btn" style="border-radius: 25px;background-color: #fe3d2b;color: #fff" data-toggle="modal" id="addAccountBtn" data-target="#addAccount">Add</button>
                @endif
            </div>
            <div class="col-md-1 activeLink">{{ t('ui_no') }}</div>
            <div class="col-md-2 activeLink">{{ t('ui_name') }}</div>
            <div class="col-md-1 activeLink">{{ t('type') }}</div>
            <div class="col-md-1 activeLink mr-3">{{ t('ui_currencies') }}</div>
            <div class="col-md-2 activeLink">{{ t('wallet_address') }}</div>
            <div class="col-md activeLink">{{ t('created_on') }}</div>
            <div class="col-md-1 activeLink">{{ t('ui_status') }}</div>
            <div class="col-md-1 activeLink">{{ t('ui_details') }}</div>
            <div id="providersAccounts" style="width: 100%">
                @foreach($accounts as $key => $account)
                    <div class="row providersAccounts-item">
                        <div class="col-md-1">{{ ++$key }}</div>
                        <div class="col-md-2">{{ $account->name ?? '' }}</div>
                        <div class="col-md-1">{{ $account->account_type ? \App\Enums\AccountType::getName($account->account_type) : '' }}</div>
                        <div class="col-md-1 mr-3">{{ $account->currency ?? null }}</div>
                        <div class="col-md-2 breakWord">{{ $account->cryptoAccountDetail->address ?? null }}</div>
                        <div class="col-md">{{ $account->created_at }}</div>
                        <div class="col-md-1">{{ t(\App\Enums\AccountStatuses::STATUSES[$account->status]) }}</div>
                        <div style="cursor:pointer;" class="col-md-1" data-account-id="{{ $account->id }}" id="accountView">View</div>
                    </div>
                @endforeach
            </div>
        </div>
    </div>
    @if($currentAdmin->hasPermissionInAnyProject([\App\Enums\BUserPermissions::ADD_EDIT_PROVIDERS]))
        <div class="modal fade modal-center" id="provider" role="dialog">
        <div class="modal-dialog modal-dialog-center">
            <!-- Modal content-->
            <div class="modal-content" style="border:none;border-radius: 5px;padding: 25px;width: 500px">
                <div class="modal-body">
                    <form name="providerForm" id="providerForm" action="{{ route('backoffice.payment.provider.store') }}" method="post">
                        <input type="hidden" name="_token" value="{{ csrf_token() }}" id="providerToken">
                        <h3>{{ t('provider_wallet_new') }}</h3>
                        <button type="button" class="close" data-dismiss="modal" style="position: absolute; top: -10px;right: 0">&times;</button>
                        <label for="name" class="activeLink">{{ t('ui_name') }}</label><br>
                        <input style="width: 350px;" type="text" id="name" name="name" value="{{ old('name') }}" required><br>
                        <span class="text-danger" id="providerName"></span><br>
                        <label for="status" class="activeLink">{{ t('ui_status') }}</label><br>
                        <select name="status" id="status" style="padding-right: 50px;" required>
                            <option value=""></option>
                            @foreach(\App\Enums\PaymentProvider::getList() as $value => $status)
                                <option value="{{ $value }}">{{ $status }}</option>
                            @endforeach
                        </select><br>


                        <div class="d-flex flex-row justify-content-between">
                            <div class="d-flex flex-column col-6 pl-0">
                                <label for="api" class="activeLink mt-0 mb-0">{{ t('ui_api') }}</label>
                                <select name="api" id="api" style="padding-right: 50px;" required data-url="{{ route('backoffice.get.wallet.provider.api.account') }}">
                                    <option value=""></option>
                                    <option value="bitgo">Bitgo</option>
                                </select><br>
                                <span class="text-danger apiError" id="providerApi"></span>
                            </div>

                            <div class="apiAccount d-none col-6 pl-0">
                                <div class="d-flex flex-column">
                                    <label for="api_account" class="activeLink mt-0 mb-0">{{ t('ui_api_accounts') }}</label>
                                    <select name="api_account" id="api_account"  style="padding-right: 50px;" required>
                                        <option value=""></option>
                                    </select><br>
                                    <span class="text-danger apiAccountError"></span><br>
                                </div>
                            </div>

                        </div>
                        <span class="text-danger" id="providerStatus"></span><br>
                        <button type="button" id="providerCreate" class="btn themeBtn" style="border-radius: 25px">{{ t('save') }}</button>
                    </form>
                </div>
            </div>
        </div>
    </div>
    @endif
    <div class="modal fade modal-center" id="addAccount" role="dialog">
        <div class="modal-dialog modal-dialog-center">
            <!-- Modal content-->
            <div class="modal-content" style="border:none;padding: 25px;width: 900px;border-radius: 30px;">
                <div class="modal-body">
                    <h1 id="accountHeader">{{ t('provider_new_account') }}</h1><br>
                    <form method="post" name="accountForm" id="accountForm" action="{{ route('backoffice.add.wallet.provider.account') }}">
                        @csrf
                        @if(old('_method'))
                            <input type="hidden" name="_method" value="put">
                        @endif
                        @if(old('account_id'))
                            <input type="hidden" name="account_id" value="{{ old('account_id') }}">
                        @endif
                        <input type="hidden" id="providerId" name="payment_provider_id" value="{{ $providerId }}">
                        <div style="position: absolute;right: 10px;top: 0;cursor: pointer" class="activeLink" id="closeButton">X</div>
                        <div style="display: inline-block; margin-right: 25px;vertical-align: top;">
                            <label for="name" class="activeLink">{{ t('ui_name') }}</label><br>
                            <input style="width: 530px" type="text" id="name" name="name" value="{{ old('name') }}"><br>
                            @error('name')<p class="text-danger">{{ $message }}</p>@enderror
                        </div>
                        <div style="display: inline-block; margin-right: 25px;vertical-align: top;">
                            <label for="statusAccount" class="activeLink">{{ t('ui_status') }}</label><br>
                            <select name="statusAccount" id="statusAccount" style="width:200px;padding-right: 50px;">
                                @foreach(\App\Enums\AccountStatuses::STATUSES as $code => $name)
                                    <option value="{{ $code }}">{{ t($name) }}</option>
                                @endforeach
                            </select>
                            @error('statusAccount')
                            <p class="text-danger">{{ $message }}</p>
                            @enderror
                        </div>
                        <div>
                            <div style=" margin-right: 50px;width: 100px;display: inline-block;vertical-align: top;">
                                <p class="activeLink">{{ t('withdraw_wire_currency') }}</p>
                                <select name="currency" id="currency" style="width:100px;padding-right: 45px">
                                    <option value=""></option>
                                    @foreach(\App\Enums\Currency::getList() as $code => $name)
                                        <option value="{{ $name }}" {{ ($name == old('currency') ? 'selected' : '') }}>{{ $name }}</option>
                                    @endforeach
                                </select>
                                @error('currency')
                                <p class="text-danger">{{ $message }}</p>
                                @enderror
                            </div>
                            <div style="width:300px;display: inline-block;vertical-align: top;">
                                <p class="activeLink" style="font-size: 13px;">{{ t('wallet_address') }}</p>
                                <input type="text" style="width:280px;border: 1px solid #c1c1c1;border-radius: 10px;" name="crypto_wallet" id="cryptoWallet" value="{{ old('crypto_wallet') }}">
                                @error('crypto_wallet')
                                <p class="text-danger">{{ $message }}</p>
                                @enderror
                            </div>
                            <div style="width:300px;display: inline-block;vertical-align: top;">
                                <p class="activeLink" style="font-size: 13px;">{{ t('ui_wallet_id') }}</p>
                                <input type="text" style="width:300px;border: 1px solid #c1c1c1;border-radius: 10px;" name="wallet_id" id="walletId" value="{{ old('wallet_id') }}">
                                @error('wallet_id')
                                <p class="text-danger">{{ $message }}</p>
                                @enderror
                            </div>
                            <div style="width:400px;display: inline-block;vertical-align: top;" class="mt-4">
                                <p class="activeLink" style="font-size: 13px;">{{ t('ui_label_kraken') }}</p>
                                <input type="text" style="width:350px;border: 1px solid #c1c1c1;border-radius: 10px;" name="label_in_kraken" id="labelKraken" value="{{ old('label_in_kraken') }}">
                                @error('label_in_kraken')
                                <p class="text-danger">{{ $message }}</p>
                                @enderror
                            </div>
                            <div style="width:400px;display: inline-block;vertical-align: top;" class="mt-4">
                                <p class="activeLink" style="font-size: 13px;">{{ t('ui_passphrase') }}</p>
                                <input type="password" style="width:350px;border: 1px solid #c1c1c1;border-radius: 10px;" name="passphrase" id="passphrase" autocomplete="false" value="{{ old('passphrase') }}">
                                @error('passphrase')
                                <p class="text-danger">{{ $message }}</p>
                                @enderror
                            </div>
                        </div>
                        <div id="rates" class="mt-5">
                            <h2>{{ t('ui_rates_list_header') }}</h2>
                            @include('backoffice.partials.provider-rate', ['prefix' => '', 'wallet' => true])
                        </div>
                        <div id="limits" class="mt-5">
                            <h2>{{ t('ui_limits') }}</h2>
                            <div style="width:135px;display: inline-block;vertical-align: top;">
                                <p class="activeLink" style="font-size: 13px;">{{ t('ui_trans_amount') }}</p>
                                <input type="text" style="border: 1px solid #c1c1c1;border-radius: 10px;" name="transaction_amount_max" value="{{ old('transaction_amount_max') }}">
                                @error('transaction_amount_max')
                                <p class="text-danger">{{ $message }}</p>
                                @enderror
                            </div>
                            <div style="width:135px;display: inline-block;vertical-align: top;">
                                <p class="activeLink" style="font-size: 13px;">{{ t('ui_month_max') }}</p>
                                <input type="text" style="border: 1px solid #c1c1c1;border-radius: 10px;" name="monthly_amount_max" value="{{ old('monthly_amount_max') }}">
                                @error('monthly_amount_max')
                                <p class="text-danger">{{ $message }}</p>
                                @enderror
                            </div>
                            <div style="width:135px;display: inline-block;vertical-align: top;">
                                <p class="activeLink" style="font-size: 13px;">{{ t('ui_trans_daily_max') }}</p>
                                <input type="text" style="border: 1px solid #c1c1c1;border-radius: 10px;" name="transaction_count_daily_max" value="{{ old('transaction_count_daily_max') }}">
                                @error('transaction_count_daily_max')
                                <p class="text-danger">{{ $message }}</p>
                                @enderror
                            </div>
                            <div style="width:135px;display: inline-block;vertical-align: top;">
                                <p class="activeLink" style="font-size: 13px;">{{ t('ui_trans_month_max') }}</p>
                                <input type="text" style="border: 1px solid #c1c1c1;border-radius: 10px;" name="transaction_count_monthly_max" value="{{ old('transaction_count_monthly_max') }}">
                                @error('transaction_count_monthly_max')
                                <p class="text-danger">{{ $message }}</p>
                                @enderror
                            </div>
                            <div style="width:135px;display: inline-block;vertical-align: top;">
                                <p class="activeLink" style="font-size: 13px;">{{ t('ui_trans_min') }}</p>
                                <input type="text" style="border: 1px solid #c1c1c1;border-radius: 10px;" name="transaction_amount_min" value="{{ old('transaction_amount_min') }}">
                                @error('transaction_amount_min')
                                <p class="text-danger">{{ $message }}</p>
                                @enderror
                            </div>
                            <div style="width:135px;display: inline-block;vertical-align: top;">
                                <p class="activeLink" style="font-size: 13px;">{{ t('ui_cabinet_deposit_time_to_fund') }}</p>
                                <input type="text" style="border: 1px solid #c1c1c1;border-radius: 10px;" name="time_to_found" value="{{ old('time_to_found') }}">
                                @error('time_to_found')
                                <p class="text-danger">{{ $message }}</p>
                                @enderror
                            </div>
                        </div>
                        @if($currentAdmin->hasPermissionInAnyProject([\App\Enums\BUserPermissions::ADD_EDIT_PROVIDERS]))
                            <button class="btn themeBtn mt-3" style="border-radius: 25px">{{ t('save') }}</button>
                        @endif
                    </form>
                </div>
            </div>
        </div>
    </div>
@endsection

@section('scripts')
    <script>
        $(document).ready(function () {
            if ($('#containErrors').length) {
                $('#addAccount').modal('show');
            }
        });
    </script>
@endsection
