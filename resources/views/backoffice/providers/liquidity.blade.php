@extends('backoffice.layouts.backoffice')
@section('title', t('title_liquidity_provider_page'))

@section('content')
    @if (isset($errors) && count($errors) > 0)
        <div id="containErrors"></div>
        <div id="accountTypeOldInput" data-type="{{ old('typeAccount') ?? old('btc_typeAccount') }}"></div>
    @endif
    <div class="row mb-4 pb-4">
        <div class="col-md-12">
            <h2 class="mb-3 large-heading-section">{{ t('title_settings_page') }}</h2>
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
        <h2 style="display: inline;margin-right: 25px;">{{ t('ui_settings_liq_providers') }}</h2>
        @if($currentAdmin->hasPermissionInAnyProject([\App\Enums\BUserPermissions::ADD_EDIT_PROVIDERS]))
            <button class="btn" style="border-radius: 25px;background-color: #000;color: #fff" data-toggle="modal"
                    id="addLiquidityProviderBtn" data-target="#provider">{{ t('add') }}</button>
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
                    <div class="providers-section-status">{{ \App\Enums\PaymentProvider::getName($provider->status)}}</div>
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
                <h3 style="display: inline;margin-right: 25px;">{{ t('ui_accounts') }}</h3>
                @if($providerId && ($currentAdmin->hasPermissionInAnyProject([\App\Enums\BUserPermissions::ADD_EDIT_PROVIDERS])))
                    <button class="btn" style="border-radius: 25px;background-color: #fe3d2b;color: #fff" data-toggle="modal" id="addAccountBtn" data-target="#addAccountSepa">{{ t('add') }}</button>
                @endif
            </div>
            <div class="col-md-1 activeLink">{{ t('ui_no') }}</div>
            <div class="col-md-2 activeLink">{{ t('ui_name') }}</div>
            <div class="col-md-1 activeLink">{{ t('type') }}</div>
            <div class="col-md-1 activeLink mr-3">{{ t('ui_currencies') }}</div>
            <div class="col-md-1 activeLink">{{ t('ui_cprofile_country') }}</div>
            <div class="col-md-2 activeLink">IBAN/Account</div>
            <div class="col-md activeLink">{{ t('ui_cprofile_created_at') }}</div>
            <div class="col-md-1 activeLink">{{ t('status') }}</div>
            <div class="col-md-1 activeLink">{{ t('details') }}</div>
            <div  id="providersAccounts" style="width: 100%">
                @foreach($accounts as $key => $account)
                    @php
                        $iban = null;
                        if($account->account_type === \App\Enums\TemplateType::TEMPLATE_TYPE_SEPA ||
                            $account->account_type === \App\Enums\TemplateType::TEMPLATE_TYPE_SWIFT ||
                            $account->account_type === \App\Enums\TemplateType::TEMPLATE_TYPE_CARD_CREDIT ||
                            $account->account_type === \App\Enums\TemplateType::TEMPLATE_TYPE_CARD_DEBIT){
                            $iban = $account->wire->iban;
                        }
                        if($account->account_type === \App\Enums\TemplateType::TEMPLATE_TYPE_CRYPTO){
                            if($account->cryptoAccountDetail){
                                $iban = $account->cryptoAccountDetail->address;
                            }
                        }
                    @endphp
                    <div class="row providersAccounts-item">
                        <div class="col-md-1">{{ ++$key }}</div>
                        <div class="col-md-2">{{ $account->name ?? '' }}</div>
                        <div class="col-md-1">{{ t(\App\Enums\TemplateType::LIQUIDY_NAMES[$account->account_type]) }}</div>
                        <div class="col-md-1 mr-3">{{ $account->cryptoAccountDetail ? $account->cryptoAccountDetail->coin : $account->currency }}</div>
                        <div class="col-md-1">{{ \App\Models\Country::getCountryNameByCode($account->country) }}</div>
                        <div class="col-md-2 breakWord">{{ $iban }}</div>
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
                        <form name="providerForm" id="providerForm"
                              action="{{ route('backoffice.payment.provider.store') }}" method="post">
                            <input type="hidden" name="_token" value="{{ csrf_token() }}" id="providerToken">
                            <h3>{{ t('provider_liquidity_new') }}</h3>
                            <button type="button" class="close" data-dismiss="modal"
                                    style="position: absolute; top: -10px;right: 0">&times;
                            </button>
                            <label for="name" class="activeLink mt-3">{{ t('ui_name') }}</label><br>
                            <input style="width: 350px;" type="text" id="name" name="name" value="{{ old('name') }}"
                                   required><br>
                            <span class="text-danger" id="providerName"></span><br>

                            <div class="d-flex flex-row justify-content-start">
                                <div class="d-flex flex-column">
                                    <label for="status" class="activeLink">{{ t('ui_status') }}</label>
                                    <select name="status" id="status" style="padding-right: 50px;" required>
                                        <option value=""></option>
                                        @foreach(\App\Enums\PaymentProvider::getList() as $value => $status)
                                            <option value="{{ $value }}">{{ $status }}</option>
                                        @endforeach
                                    </select>
                                    <span class="text-danger" id="providerStatus"></span><br>
                                </div>
                            </div>

                            <div class="d-flex flex-row justify-content-between">
                                <div class="d-flex flex-column col-6 pl-0">
                                    <label for="api" class="activeLink mt-0 mb-0">{{ t('ui_api') }}</label>
                                    <select name="api" id="api" style="padding-right: 50px;" required data-url="{{ route('backoffice.get.liquidity.provider.api.account') }}">
                                        <option value=""></option>
                                        @foreach(\App\Enums\ExchangeApiProviders::getList() as $api => $name)
                                            <option value="{{ $api }}">{{ $name }}</option>
                                        @endforeach
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
                            <br>
                            <button type="button" id="providerCreate" class="btn themeBtn"
                                    style="border-radius: 25px">{{ t('save') }}</button>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    @endif

    <div class="modal fade modal-center" id="addAccountSepa" role="dialog">
        <div class="modal-dialog modal-dialog-center">
            <!-- Modal content-->
            <div class="modal-content" style="border:none;padding: 25px;width: 900px;border-radius: 30px;">
                <div class="modal-body">
                    <h1 id="accountHeaderSepa">{{ t('provider_new_account') }}</h1><br>
                    <form method="post" name="liquidityAccountForm" id="liquidityAccountForm" action="{{ route('backoffice.add.liquidity.sepa.provider.account') }}">
                        @csrf
                        @if(old('_method'))
                            <input type="hidden" name="_method" value="put">
                        @endif
                        @if(old('account_id'))
                            <input type="hidden" name="account_id" value="{{ old('account_id') }}">
                        @endif
                        <input type="hidden" id="providerIdSepa" name="payment_provider_id" value="{{ $providerId }}">
                        <div style="position: absolute;right: 10px;top:0;cursor: pointer" class="activeLink" id="closeButton">X</div>
                        <div style="display: inline-block; margin-right: 25px;vertical-align: top;">
                            <label for="name" class="activeLink">{{ t('ui_name') }}</label><br>
                            <input style="width: 500px" type="text" id="name" value="{{ old('name') }}" name="name"><br>
                            @error('name')<p class="text-danger">{{ $message }}</p>@enderror
                        </div>
                        <div style="width: 250px;display: inline-block;vertical-align: top;">
                            <label for="name" class="activeLink">{{ t('minimum_balance_alert') }}</label><br>
                            <input style="width: 200px;" type="text" id="minimum_balance_alert" name="minimum_balance_alert" value="{{ old('minimum_balance_alert') }}"><br>
                            @error('minimum_balance_alert')<p class="text-danger">{{ $message }}</p>@enderror
                        </div>
                        <div style="display: inline-block; margin-right: 25px;vertical-align: top;">
                            <label for="statusAccount" class="activeLink">{{ t('ui_status') }}</label><br>
                            <select name="statusAccount" id="statusAccountSepa" style="width:200px;padding-right: 50px;">
                                @foreach(\App\Enums\AccountStatuses::STATUSES as $code => $name)
                                    <option value="{{ $code }}">{{ t($name) }}</option>
                                @endforeach
                            </select>
                            @error('statusAccount')
                            <p class="text-danger">{{ $message }}</p>
                            @enderror
                        </div>
                        <div>
                            <div style=" margin-right: 50px;width: 150px;display: inline-block;vertical-align: top;">
                                <p class="activeLink">{{ t('type') }}</p>
                                <input type="hidden" class="type_swift" value="{{ \App\Enums\TemplateType::TEMPLATE_TYPE_SWIFT }}" />
                                <select name="typeAccount" id="typeAccountSepa" class="account_type" style="padding-right: 45px">
                                    @foreach(\App\Enums\TemplateType::LIQUIDY_NAMES as $code => $name)
                                        <option value="{{ $code }}" {{ ($code == \App\Enums\TemplateType::TEMPLATE_TYPE_SEPA ? 'selected' : '') }}>{{ t($name) }}</option>
                                    @endforeach
                                </select>
                                @error('typeAccount')
                                <p class="text-danger">{{ $message }}</p>
                                @enderror
                            </div>
                            <div style=" margin-right: 50px;width: 300px;display: inline-block;vertical-align: top;">
                                <p class="activeLink">{{ t('ui_cprofile_country') }}</p>
                                <select name="country" id="country" style="padding-right: 45px">
                                    <option value=""></option>
                                    @foreach(\App\Models\Country::getCountries() as $code => $name)
                                        <option value="{{ $code }}" {{ ($code == old('country') ? 'selected' : '') }}>{{ $name }}</option>
                                    @endforeach
                                </select>
                                @error('country')
                                <p class="text-danger">{{ $message }}</p>
                                @enderror
                            </div>
                            <div style=" margin-right: 50px;width: 200px;display: inline-block;vertical-align: top;">
                                <p class="activeLink">{{ t('ui_currencies') }}</p>
                                <select name="currency" id="currencySepa" style="padding-right: 45px;width:200px">
                                    <option value=""></option>
                                    @foreach(\App\Enums\Currency::FIAT_CURRENCY_NAMES as $key => $currency)
                                        <option value="{{ $currency }}" {{ ($currency == old('currency') ? 'selected' : '') }}>{{ $currency }}</option>
                                    @endforeach
                                </select>
                                @error('currency')
                                <p class="text-danger">{{ $message }}</p>
                                @enderror
                            </div>
                        </div>
                        <div id="availableCountries" class="mt-5">
                            <h2>{{ t('ui_available_countries') }}</h2>
                            <select name="countries[]" id="countries" multiple="multiple" style="width: 800px;">
                                @foreach(\App\Models\Country::getCountries() as $code => $name)
                                    <option value="{{ $code }}">{{ $name }}</option>
                                @endforeach
                            </select>
                            @error('countries')<p class="text-danger">{{ $message }}</p>@enderror
                        </div>
                        <div id="rates" class="mt-5">
                            <h2>{{ t('ui_rates_list_header') }}</h2>
                            @include('backoffice.partials.provider-rate', ['prefix' => ''])
                        </div>
                        <div id="limits" class="mt-5">
                            <h2>{{ t('ui_limits') }}</h2>
                            <div style="width:130px;display: inline-block;vertical-align: top;">
                                <p class="activeLink" style="font-size: 13px;">{{ t('ui_trans_amount') }}</p>
                                <input type="text" style="border: 1px solid #c1c1c1;border-radius: 10px;" name="transaction_amount_max" value="{{ old('transaction_amount_max') }}">
                                @error('transaction_amount_max')
                                <p class="text-danger">{{ $message }}</p>
                                @enderror
                            </div>
                            <div style="width:130px;display: inline-block;vertical-align: top;">
                                <p class="activeLink" style="font-size: 13px;">{{ t('ui_month_max') }}</p>
                                <input type="text" style="border: 1px solid #c1c1c1;border-radius: 10px;" name="monthly_amount_max" value="{{ old('monthly_amount_max') }}">
                                @error('monthly_amount_max')
                                <p class="text-danger">{{ $message }}</p>
                                @enderror
                            </div>
                            <div style="width:130px;display: inline-block;vertical-align: top;">
                                <p class="activeLink" style="font-size: 13px;">{{ t('ui_trans_daily_max') }}</p>
                                <input type="text" style="border: 1px solid #c1c1c1;border-radius: 10px;" name="transaction_count_daily_max" value="{{ old('transaction_count_daily_max') }}">
                                @error('transaction_count_daily_max')
                                <p class="text-danger">{{ $message }}</p>
                                @enderror
                            </div>
                            <div style="width:130px;display: inline-block;vertical-align: top;">
                                <p class="activeLink" style="font-size: 13px;">{{ t('ui_trans_month_max') }}</p>
                                <input type="text" style="border: 1px solid #c1c1c1;border-radius: 10px;" name="transaction_count_monthly_max" value="{{ old('transaction_count_monthly_max') }}">
                                @error('transaction_count_monthly_max')
                                <p class="text-danger">{{ $message }}</p>
                                @enderror
                            </div>
                            <div style="width:130px;display: inline-block;vertical-align: top;">
                                <p class="activeLink" style="font-size: 13px;">{{ t('ui_trans_min') }}</p>
                                <input type="text" style="border: 1px solid #c1c1c1;border-radius: 10px;" name="transaction_amount_min" value="{{ old('transaction_amount_min') }}">
                                @error('transaction_amount_min')
                                <p class="text-danger">{{ $message }}</p>
                                @enderror
                            </div>
                        </div>
                        <div id="bankDetails" class="mt-5">
                            <h2>{{ t('ui_cabinet_menu_bank_details') }}</h2>
                            <div class="mt-3">
                                <div style="width:380px;display: inline-block;vertical-align: top;">
                                    <p class="activeLink" style="font-size: 13px;">{{ t('wire_transfer_account_beneficiary') }}</p>
                                    <input type="text" style="border: 1px solid #c1c1c1;border-radius: 10px;" name="account_beneficiary" value="{{ old('account_beneficiary') }}">
                                    @error('account_beneficiary')
                                    <p class="text-danger">{{ $message }}</p>
                                    @enderror
                                </div>
                                <div style="width:380px;display: inline-block;vertical-align: top;">
                                    <p class="activeLink" style="font-size: 13px;">{{ t('wire_transfer_beneficiary_address') }}</p>
                                    <input type="text" style="border: 1px solid #c1c1c1;border-radius: 10px;" name="beneficiary_address" value="{{ old('beneficiary_address') }}">
                                    @error('beneficiary_address')
                                    <p class="text-danger">{{ $message }}</p>
                                    @enderror
                                </div>
                            </div>
                            <div class="mt-3">
                                <div style="width:380px;display: inline-block;vertical-align: top;">
                                    <p class="activeLink" style="font-size: 13px;">IBAN</p>
                                    <input type="text" style="border: 1px solid #c1c1c1;border-radius: 10px;" name="iban" value="{{ old('iban') }}">
                                    @error('iban')
                                    <p class="text-danger">{{ $message }}</p>
                                    @enderror
                                </div>
                                <div style="width:380px;display: inline-block;vertical-align: top;">
                                    <p class="activeLink" style="font-size: 13px;">SWIFT/BIC</p>
                                    <input type="text" style="border: 1px solid #c1c1c1;border-radius: 10px;" name="swift" value="{{ old('swift') }}">
                                    @error('swift')
                                    <p class="text-danger">{{ $message }}</p>
                                    @enderror
                                </div>
                            </div>
                            <div class="mt-3">
                                <div style="width:380px;display: inline-block;vertical-align: top;">
                                    <p class="activeLink" style="font-size: 13px;">{{ t('withdraw_wire_bank_name') }}</p>
                                    <input type="text" style="border: 1px solid #c1c1c1;border-radius: 10px;" name="bank_name" value="{{ old('bank_name') }}">
                                    @error('bank_name')
                                    <p class="text-danger">{{ $message }}</p>
                                    @enderror
                                </div>
                                <div style="width:380px;display: inline-block;vertical-align: top;">
                                    <p class="activeLink" style="font-size: 13px;">{{ t('withdraw_wire_bank_address') }}</p>
                                    <input type="text" style="border: 1px solid #c1c1c1;border-radius: 10px;" name="bank_address" value="{{ old('bank_address') }}">
                                    @error('bank_address')
                                    <p class="text-danger">{{ $message }}</p>
                                    @enderror
                                </div>
                            </div>
                            <div class="mt-3">
                                <div style="width:380px;display: inline-block;vertical-align: top;">
                                    <p class="activeLink" style="font-size: 13px;">{{ t('ui_cabinet_deposit_time_to_fund') }}</p>
                                    <input type="text" style="border: 1px solid #c1c1c1;border-radius: 10px;" name="time_to_found" value="{{ old('time_to_found') }}">
                                    @error('time_to_found')
                                    <p class="text-danger">{{ $message }}</p>
                                    @enderror
                                </div>
                                <div style="width:380px;display: inline-block;vertical-align: top;">
                                    <p class="activeLink" style="font-size: 13px;">{{ t('ui_cabinet_bank_details_account_number') }}</p>
                                    <input type="text" style="border: 1px solid #c1c1c1;border-radius: 10px;" name="account_number" value="{{ old('account_number') }}">
                                    @error('account_number')
                                    <p class="text-danger">{{ $message }}</p>
                                    @enderror
                                </div>
                            </div>
                            <div class="mt-3 correspondent_bank_details" hidden>
                                <div style="width:380px;display: inline-block;vertical-align: top">
                                    <p class="activeLink" style="font-size: 13px;">{{ t('ui_cabinet_correspondent_bank') }}</p>
                                    <input type="text" style="border: 1px solid #c1c1c1;border-radius: 10px;" name="correspondent_bank" value="{{ old('correspondent_bank') }}">
                                    @error('correspondent_bank')<p class="text-danger">{{ $message }}</p>@enderror
                                </div>
                                <div style="width:380px;display: inline-block;vertical-align: top;">
                                    <p class="activeLink" style="font-size: 13px;">{{ t('ui_cabinet_correspondent_bank_swift') }}</p>
                                    <input type="text" style="border: 1px solid #c1c1c1;border-radius: 10px;" name="correspondent_bank_swift" value="{{ old('correspondent_bank_swift') }}">
                                    @error('correspondent_bank_swift')<p class="text-danger">{{ $message }}</p>@enderror
                                </div>
                            </div>
                            <div class="mt-3 intermediary_bank_details" hidden>
                                <div style="width:380px;display: inline-block;vertical-align: top">
                                    <p class="activeLink" style="font-size: 13px;">{{ t('ui_cabinet_intermediary_bank') }}</p>
                                    <input type="text" style="border: 1px solid #c1c1c1;border-radius: 10px;" name="intermediary_bank" value="{{ old('intermediary_bank') }}">
                                    @error('intermediary_bank')<p class="text-danger">{{ $message }}</p>@enderror
                                </div>
                                <div style="width:380px;display: inline-block;vertical-align: top;">
                                    <p class="activeLink" style="font-size: 13px;">{{ t('ui_cabinet_intermediary_bank_swift') }}</p>
                                    <input type="text" style="border: 1px solid #c1c1c1;border-radius: 10px;" name="intermediary_bank_swift" value="{{ old('intermediary_bank_swift') }}">
                                    @error('intermediary_bank_swift')<p class="text-danger">{{ $message }}</p>@enderror
                                </div>
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
    <div class="modal fade modal-center" id="addAccountBtc" role="dialog">
        <div class="modal-dialog modal-dialog-center">
            <!-- Modal content-->
            <div class="modal-content" style="border:none;padding: 25px;width: 900px;border-radius: 30px;">
                <div class="modal-body">
                    <h1 id="accountHeaderBtc">{{ t('provider_new_account') }}</h1><br>
                    <form method="post" name="accountForm" id="accountForm" action="{{ route('backoffice.add.liquidity.btc.provider.account') }}">
                        @csrf
                        @if(old('_method'))
                            <input type="hidden" name="_method" value="put">
                        @endif
                        @if(old('account_id'))
                            <input type="hidden" name="account_id" value="{{ old('account_id') }}">
                        @endif
                        <input type="hidden" id="providerIdBtc" name="btc_payment_provider_id" value="{{ $providerId }}">
                        <div style="position: absolute;right: 10px;top:0;cursor: pointer" class="activeLink" id="closeButton">X</div>
                        <div style="display: inline-block; margin-right: 25px;vertical-align: top;">
                            <label for="btcName" class="activeLink">{{ t('ui_name') }}</label><br>
                            <input style="width: 500px" type="text" id="btcName" value="{{ old('btc_name') }}" name="btc_name"><br>
                            @error('btc_name')<p class="text-danger">{{ $message }}</p>@enderror
                        </div>
                        <div style="width: 250px;display: inline-block;vertical-align: top;">
                            <label for="name" class="activeLink">{{ t('minimum_balance_alert') }}</label><br>
                            <input style="width: 200px;" type="text" id="btc_minimum_balance_alert" name="btc_minimum_balance_alert" value="{{ old('btc_minimum_balance_alert') }}"><br>
                            @error('btc_minimum_balance_alert')<p class="text-danger">{{ $message }}</p>@enderror
                        </div>
                        <div style="display: inline-block; margin-right: 25px;vertical-align: top;">
                            <label for="statusAccountBtc" class="activeLink">{{ t('ui_status') }}</label><br>
                            <select name="btc_statusAccount" id="statusAccountBtc" style="padding-right: 50px;width:200px">
                                @foreach(\App\Enums\AccountStatuses::STATUSES as $code => $name)
                                    <option value="{{ $code }}">{{ t($name) }}</option>
                                @endforeach
                            </select>
                            @error('btc_statusAccount')
                            <p class="text-danger">{{ $message }}</p>
                            @enderror
                        </div>
                        <div>
                            <div style=" margin-right: 50px;width: 150px;display: inline-block;vertical-align: top;">
                                <p class="activeLink">{{ t('type') }}</p>
                                <select name="btc_typeAccount" id="typeAccountBtc" style="padding-right: 45px;width:180px">
                                    @foreach(\App\Enums\TemplateType::LIQUIDY_NAMES as $code => $name)
                                        <option value="{{ $code }}" {{ ($code == \App\Enums\TemplateType::TEMPLATE_TYPE_CRYPTO ? 'selected' : '') }}>{{ t($name) }}</option>
                                    @endforeach
                                </select>
                                @error('btc_typeAccount')
                                <p class="text-danger">{{ $message }}</p>
                                @enderror
                            </div>
                            <div style=" margin-right: 50px;width: 200px;display: inline-block;vertical-align: top;">
                                <p class="activeLink">{{ t('currency') }}</p>
                                <select name="btc_currency" id="currencyBtc" style="padding-right: 45px;width:220px">
                                    <option value=""></option>
                                    @foreach(\App\Enums\Currency::getList() as $key => $currency)
                                        <option value="{{ $currency }}" {{ ($currency == old('btc_currency') ? 'selected' : '') }}>{{ $currency }}</option>
                                    @endforeach
                                </select>
                                @error('btc_currency')
                                <p class="text-danger">{{ $message }}</p>
                                @enderror
                            </div>
                            <div style="width:300px;display: inline-block;vertical-align: top;">
                                <p class="activeLink" style="font-size: 13px;">{{ t('wallet_address') }}</p>
                                <input type="text" style="width:300px;border: 1px solid #c1c1c1;border-radius: 10px;" name="btc_crypto_wallet" id="cryptoWallet" value="{{ old('btc_crypto_wallet') }}">
                                @error('btc_crypto_wallet')
                                <p class="text-danger">{{ $message }}</p>
                                @enderror
                            </div>
                        </div>
                        <div id="rates" class="mt-5">
                            <h2>{{ t('ui_rates_list_header') }}</h2>
                            @include('backoffice.partials.provider-rate', ['prefix' => 'btc_', 'wallet' => true])
                        </div>
                        <div id="limits" class="mt-5">
                            <h2>{{ t('ui_limits') }}</h2>
                            <div style="width:135px;display: inline-block;vertical-align: top;">
                                <p class="activeLink" style="font-size: 13px;">{{ t('ui_trans_amount') }}</p>
                                <input type="text" style="border: 1px solid #c1c1c1;border-radius: 10px;" name="btc_transaction_amount_max" value="{{ old('btc_transaction_amount_max') }}">
                                @error('btc_transaction_amount_max')
                                <p class="text-danger">{{ $message }}</p>
                                @enderror
                            </div>
                            <div style="width:135px;display: inline-block;vertical-align: top;">
                                <p class="activeLink" style="font-size: 13px;">{{ t('ui_month_max') }}</p>
                                <input type="text" style="border: 1px solid #c1c1c1;border-radius: 10px;" name="btc_monthly_amount_max" value="{{ old('btc_monthly_amount_max') }}">
                                @error('btc_monthly_amount_max')
                                <p class="text-danger">{{ $message }}</p>
                                @enderror
                            </div>
                            <div style="width:135px;display: inline-block;vertical-align: top;">
                                <p class="activeLink" style="font-size: 13px;">{{ t('ui_trans_daily_max') }}</p>
                                <input type="text" style="border: 1px solid #c1c1c1;border-radius: 10px;" name="btc_transaction_count_daily_max" value="{{ old('btc_transaction_count_daily_max') }}">
                                @error('btc_transaction_count_daily_max')
                                <p class="text-danger">{{ $message }}</p>
                                @enderror
                            </div>
                            <div style="width:135px;display: inline-block;vertical-align: top;">
                                <p class="activeLink" style="font-size: 13px;">{{ t('ui_trans_month_max') }}</p>
                                <input type="text" style="border: 1px solid #c1c1c1;border-radius: 10px;" name="btc_transaction_count_monthly_max" value="{{ old('btc_transaction_count_monthly_max') }}">
                                @error('btc_transaction_count_monthly_max')
                                <p class="text-danger">{{ $message }}</p>
                                @enderror
                            </div>
                            <div style="width:135px;display: inline-block;vertical-align: top;">
                                <p class="activeLink" style="font-size: 13px;">{{ t('ui_trans_min') }}</p>
                                <input type="text" style="border: 1px solid #c1c1c1;border-radius: 10px;" name="btc_transaction_amount_min" value="{{ old('btc_transaction_amount_min') }}">
                                @error('btc_transaction_amount_min')
                                <p class="text-danger">{{ $message }}</p>
                                @enderror
                            </div>
                            <div style="width:135px;display: inline-block;vertical-align: top;">
                                <p class="activeLink" style="font-size: 13px;">{{ t('ui_cabinet_deposit_time_to_fund') }}</p>
                                <input type="text" style="border: 1px solid #c1c1c1;border-radius: 10px;" name="btc_time_to_found" value="{{ old('btc_time_to_found') }}">
                                @error('btc_time_to_found')
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
            @if($errors->any())
                @if($errors->has('bank_detail_type'))
                    $('.correspondent_bank_details').removeAttr('hidden')
                    $('.intermediary_bank_details').removeAttr('hidden')
                @endif
            @endif
        })


    </script>
@endsection
