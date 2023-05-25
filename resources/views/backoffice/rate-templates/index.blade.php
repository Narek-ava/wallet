@extends('backoffice.layouts.backoffice')
@section('title', t('title_setting_rate_page'))

@section('content')
    @if($errors->any())
        @if($errors->has('bankCardRateErrors'))
            <div id="containBankCardRateErrors"></div>
        @elseif($errors->has('bankCardRateErrors'))
            <div id="containErrors"></div>
        @elseif($errors->has('bankCardRateErrorsUpdate'))
            <div id="containBankCardRateErrorsUpdate"></div>
        @endif
    @endif

    @if(session()->has('error'))
        <div class="alert alert-error alert-dismissible fade show" role="alert" id="errorMessageAlert">
            <h4>{{ session()->get('error') }}</h4>
            <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                <span aria-hidden="true">&times;</span>
            </button>
        </div>
    @endif

    <div class="container-fluid">
        <div class="row mb-4 pb-4">
            <div class="col-md-12">
                <h2 class="mb-3 mt-2 large-heading-section">{{ t('settings') }}</h2>
                <div class="row">
                    <div class="col-lg-5 d-block d-md-flex">
                        <p>{{ t('backoffice_profile_page_header_body') }}</p>
                    </div>
                    @include('cabinet.partials.notification', ['notify' => getNotification(\Illuminate\Support\Facades\Auth::id()), 'admin' => true])
                </div>
            </div>
        </div>
        <div class="row">
            <div class="col-md-6">
                <h1 class="activeLink" style="display: inline-block">{{ t('ui_clients_rate') }}</h1>
                @if($currentAdmin->hasPermissionInAnyProject([ \App\Enums\BUserPermissions::ADD_AND_EDIT_CLIENT_RATES ]))
                    <button class="btn" style="border-radius: 25px;background-color: #000;color: #fff"
                            data-toggle="modal" id="addRateTemplate"
                            data-target="#addRateTemplates">{{ t('add') }}</button>
                @endif
                <p>
                    <input @if(request()->part === 'all') checked @endif type="checkbox" id="clientRatesAll" data-url="{{ route('rate.templates.index') }}"><label for="providerAll" style="margin-left: 15px">{{ t('ui_view_all') }}</label>
                </p>
            </div>
        </div>

        <div class="col-md-12">
            <div class="row" id="rateTemplatesSection">
                @if(!empty($rateTemplates))
                    <div class="col-md-1 activeLink">{{ t('ui_no') }}</div>
                    <div class="col-md activeLink">{{ t('ui_name') }}</div>
                    <div class="col-md-2 activeLink">{{ t('ui_rates_created_label') }}</div>
                    <div class="col-md-2 activeLink">{{ t('ui_last_change') }}</div>
                    <div class="col-md-2 activeLink" style="max-width: 150px;">{{ t('ui_status') }}</div>
                    <div class="col-md-1 activeLink">{{ t('ui_default') }}</div>
                    <div class="col-md-2 activeLink">{{ t('details') }}</div>
                    <div class="col-md-12" id="templates">
                        @foreach($rateTemplates as $key => $rateTemplate)
                            <div class="row providersAccounts-item">
                                <div class="col-md-1">{{ ++$key }}</div>
                                <div class="col-md breakWord pl-0">{{ $rateTemplate->name }}</div>
                                <div class="col-md-2 breakWord">{{ $rateTemplate->created_at }}</div>
                                <div class="col-md-2 breakWord">{{ $rateTemplate->updated_at }}</div>
                                <div class="col-md-2 breakWord pl-4" style="max-width: 150px;">{{ \App\Enums\RateTemplatesStatuses::STATUSES[$rateTemplate->status] }}</div>
                                <div class="col-md-1 breakWord pl-4">
                                    <input type="checkbox" disabled {{ $rateTemplate->is_default ? 'checked' : '' }}>
                                </div>
                                <div style="cursor:pointer;" class="col-md-2">
                                    <button class="btn themeBtn updateRateProvider" style="border-radius: 15px;"
                                            data-rate-template-id="{{ $rateTemplate->id }}">View
                                    </button>
                                </div>
                            </div>
                        @endforeach
                    </div>
                    @if(!empty($rateTemplates))
                        {!! $rateTemplates->appends(request()->query())->links() !!}
                    @endif

                @endif
            </div>
        </div>
        <br><br>
        <div class="row">
            <div class="col-md-6">
                <h1 class="activeLink" style="display: inline-block">{{ t('ui_bank_cards_rate') }}</h1>
                @if($currentAdmin->hasPermissionInAnyProject([\App\Enums\BUserPermissions::ADD_AND_EDIT_CLIENT_RATES]))
                    <button class="btn" style="border-radius: 25px;background-color: #000;color: #fff"
                            data-toggle="modal" id="cardsTemplates"
                            data-target="#cardsRateTemplates">{{ t('add') }}</button>
                @endif
            </div>
        </div>

        <div class="col-md-12">
            <div class="row" id="rateTemplatesSection">
                @if(!empty($bankCardRateTemplates))
                    <div class="col-md-1 activeLink">{{ t('ui_no') }}</div>
                    <div class="col-md activeLink">{{ t('ui_name') }}</div>
                    <div class="col-md-2 activeLink">{{ t('ui_rates_created_label') }}</div>
                    <div class="col-md-2 activeLink">{{ t('ui_last_change') }}</div>
                    <div class="col-md-2 activeLink" style="max-width: 150px;">{{ t('ui_status') }}</div>
                    <div class="col-md-2 activeLink">{{ t('details') }}</div>
                    <div class="col-md-12" id="templates">
                        @foreach($bankCardRateTemplates as $key => $rateTemplate)
                            <div class="row providersAccounts-item">
                                <div class="col-md-1">{{ ++$key }}</div>
                                <div class="col-md breakWord pl-0">{{ $rateTemplate->name }}</div>
                                <div class="col-md-2 breakWord">{{ $rateTemplate->created_at }}</div>
                                <div class="col-md-2 breakWord">{{ $rateTemplate->updated_at }}</div>
                                <div class="col-md-2 breakWord pl-4" style="max-width: 150px;">{{ \App\Enums\RateTemplatesStatuses::STATUSES[$rateTemplate->status] }}</div>
                                <div style="cursor:pointer;" class="col-md-2"><button class="btn themeBtn updateBankCardRateProvider" style="border-radius: 15px;" data-rate-template-id="{{ $rateTemplate->id }}">View</button></div>
                            </div>
                        @endforeach
                    </div>
                @if(!empty($bankCardRateTemplates))
                    {!! $bankCardRateTemplates->appends(request()->query())->links() !!}
                    @endif
                @endif
            </div>
        </div>
        <br><br>

        <div class="modal fade modal-center" id="addRateTemplates" role="dialog">
            <div class="modal-dialog modal-dialog-center">
                <!-- Modal content-->
                <div class="modal-content" style="border:none;padding: 25px;width: 900px;border-radius: 30px;">
                    <div class="modal-body">
                        <h1 id="rateHeader">{{ t('provider_new_rate_plan') }}</h1><br>
                        <form action="{{ route('rate.templates.store') }}" method="post" name="rateTemplateForm" id="rateTemplateForm">
                            @csrf
                            <input type="hidden" id="updateDefault" name="updateDefault"/>
                            <div style="position: absolute;top: 15px;right: 25px;cursor: pointer" data-dismis="modal" class="activeLink" id="closeButton">X</div>
                            <div style="display: inline-block; margin-right: 25px;vertical-align: bottom;">
                                <label for="name" class="activeLink">{{ t('ui_name') }}</label><br>
                                <input style="width: 400px" type="text" id="nameAccount" name="name" value="{{ old('name') }}" required><br>
                                @error('name')<p class="text-danger">{{ $message }}</p>@enderror
                            </div>
                            <div style="display: inline-block; margin-right: 25px;vertical-align: bottom;">
                                <select name="status" id="status" style="padding-right: 50px;vertical-align: bottom;">
                                    @foreach(\App\Enums\RateTemplatesStatuses::STATUSES as $code => $name)
                                        <option value="{{ $code }}" {{ old('status') == $code ? 'selected' : '' }}>{{ $name }}</option>
                                    @endforeach
                                </select>
                                @error('status')<p class="text-danger">{{ $message }}</p>@enderror
                            </div>
                            <div class="row float-right">
                                <div class="error text-danger-rates projectSelectError"></div>
                            </div>

                        @if($currentAdmin->hasPermissionInAnyProject([ \App\Enums\BUserPermissions::ADD_AND_EDIT_CLIENT_RATES ]))
                                <div style="margin-top: 20px">
                                    <button type="button" id="copyRate"
                                            class="btn themeBtnDark"> {{ t('copy') }} </button>
                                </div>
                            @endif
                            <div>
                                <div class="activeLink" style="float:left; margin: 25px 25px 0 0">To</div>
                                <div style="float:left; margin: 25px 25px 25px 0">
                                    <select name="type_client" id="typeClient" style="padding-right: 45px">
                                        <option value=""></option>
                                        @foreach(\App\Models\Cabinet\CProfile::TYPES_LIST as $code => $name)
                                            <option value="{{ $code }}" {{ ($code == old('type_client') ? 'selected' : '') }}>{{ t($name) }}</option>
                                        @endforeach
                                    </select>
                                    @error('type_client')<p class="text-danger">{{ $message }}</p>@enderror
                                </div>

                                <div style="float:left; margin: 25px 25px 0 0">
                                    <input type="checkbox" id="isDefault" name="is_default" value="1" {{ old('is_default') ? 'checked' : '' }}><label for="isDefault" style="margin-left: 15px">Default</label>
                                </div>
                                @if($currentAdmin->hasPermissionInAnyProject([ \App\Enums\BUserPermissions::ADD_AND_EDIT_CLIENT_RATES ]))
                                    <div class="copyRate"
                                         style="float:left; margin-left: 50px ; margin-top: 25px; vertical-align: bottom;"
                                         @if(!$errors->has('copyName')) hidden @endif>
                                        <label for="copyName" class="activeLink">{{ t('ui_copy_name') }}</label><br>
                                        <input
                                            @if(!$errors->has('copyName')) hidden @endif
                                        style="width: 350px" type="text" id="copyNameAccount" name="copyName"
                                            value="{{ old('copyName') }}"/><br><br>
                                        @error('copyName')
                                        <p class="text-danger">{{ $message }}</p>
                                        @enderror
                                        <input hidden id="makeCopy" name="makeCopy"/>
                                        <button type="button" id="copyRateSave"
                                                class="btn themeBtn"> {{ t('save') }} </button>
                                    </div>
                                @endif
                            </div>
                            <div id="availableCountries"  style="margin-top: 100px;">
                                <h2>Available countries</h2>
                                <input type="hidden" value="{{ old('rate_template_id') ? old('rate_template_id') : '' }}" name="oldRateId" id="oldRateId">
                                <select name="countries[]" id="countries" multiple="multiple" style="width: 800px;">
                                    @foreach(\App\Models\Country::getCountries() as $code => $name)
                                        <option value="{{ $code }}">{{ $name }}</option>
                                    @endforeach
                                </select>
                                @error('countries')<p class="text-danger">{{ $message }}</p>@enderror
                            </div>
                            <div id="account" class="mt-3">
                                <h2>Account</h2>
                                <div>
                                    <div style="width:190px;display: inline-block;vertical-align: top;">
                                        <p class="activeLink" style="font-size: 13px;">{{ t('ui_opening') }}</p>
                                        <input type="text" style="border: 1px solid #c1c1c1;border-radius: 10px;" name="opening" value="{{ old('opening') }}" required>
                                        @error('opening')<p class="text-danger">{{ $message }}</p>@enderror
                                    </div>
                                    <div style="width:190px;display: inline-block;vertical-align: top;">
                                        <p class="activeLink" style="font-size: 13px;">{{ t('ui_maintenance') }}</p>
                                        <input type="text" style="border: 1px solid #c1c1c1;border-radius: 10px;" name="maintenance" value="{{ old('maintenance') }}" required>
                                        @error('maintenance')<p class="text-danger">{{ $message }}</p>@enderror
                                    </div>
                                    <div style="width:190px;display: inline-block;vertical-align: top;">
                                        <p class="activeLink" style="font-size: 13px;">{{ t('rates_title_account_closure') }}</p>
                                        <input type="text" style="border: 1px solid #c1c1c1;border-radius: 10px;" name="account_closure" value="{{ old('account_closure') }}" required>
                                        @error('account_closure')<p class="text-danger">{{ $message }}</p>@enderror
                                    </div>
                                    <div style="width:190px;display: inline-block;vertical-align: top;">
                                        <p class="activeLink" style="font-size: 13px;">{{ t('ui_referral_remuneration') }}</p>
                                        <input type="text" style="border: 1px solid #c1c1c1;border-radius: 10px;" name="referral_remuneration" value="{{ old('referral_remuneration') }}" required>
                                        @error('referral_remuneration')<p class="text-danger">{{ $message }}</p>@enderror
                                    </div>
                                </div>
                            </div>


                            <div class="mt-5">
                                <ul class="nav nav-tabs" id="rateTemplateTab" role="tablist">
                                    @php $currencies = \App\Enums\Currency::getAllCurrencies(); @endphp
                                    @foreach(\App\Enums\Currency::getAllCurrencies() as $currency)
                                        <li class="nav-item">
                                            <a class="nav-link  @if ($currency == reset($currencies)) active @endif" id="{{ strtolower($currency) }}Link" data-toggle="tab" href="#{{ strtolower($currency) }}" role="tab" aria-controls="{{ strtolower($currency) }}home" aria-selected="{{ $currency == reset($currencies) ? true : false }}">Rates {{ $currency }}</a>
                                        </li>
                                    @endforeach
                                </ul>
                            </div>
                            <div class="tab-content" id="rateTemplateTabContent">

                                @foreach(\App\Enums\Currency::FIAT_CURRENCY_NAMES as $currency)
                                    @include('backoffice.rate-templates.partials.rates-fiat',[ 'currency' => $currency ])
                                @endforeach

                                @foreach(\App\Enums\Currency::getList() as $currency)
                                    @include('backoffice.rate-templates.partials.rates-crypto',[ 'currency' => $currency ])
                                @endforeach

                                <p style="font-weight: bold;font-size: 18px">{{ t('ui_limits')   }} ({{  \App\Enums\Currency::FIAT_CURRENCY_SYMBOLS[\App\Enums\Currency::CURRENCY_EUR] }}) </p>
                                    <div>
                                        <div style="width:190px;display: inline-block;vertical-align: top;">
                                            <p style="font-weight: bold;font-size: 14px">{{ t('enum_compliance_level_level_0') }}</p>
                                        </div>
                                        <div style="width:190px;display: inline-block;vertical-align: top;">
                                            <p style="font-weight: bold;font-size: 14px">{{ t('enum_compliance_level_level_1') }}</p>
                                        </div>
                                        <div style="width:190px;display: inline-block;vertical-align: top;">
                                            <p style="font-weight: bold;font-size: 14px">{{ t('enum_compliance_level_level_2') }}</p>
                                        </div>
                                        <div style="width:190px;display: inline-block;vertical-align: top;">
                                            <p style="font-weight: bold;font-size: 14px">{{ t('enum_compliance_level_level_3') }}</p>
                                        </div>
                                    </div>
                                    <div>
                                    @foreach(\App\Enums\ComplianceLevel::getList() as $complianceLevel)
                                        <div style="width:190px;display: inline-block;vertical-align: top;">
                                            <p class="activeLink" style="font-size: 13px;">{{ t('ui_cabinet_deposit_transaction_limit') }}</p>
                                            <input type="text" style="border: 1px solid #c1c1c1;border-radius: 10px;" name="transaction_amount_max[]" value="{{ old('transaction_amount_max.' . $loop->index) }}">
                                            @error('transaction_amount_max.' . $loop->index) <p class="text-danger">{{ $message }}</p>@enderror
                                        </div>
                                    @endforeach
                                </div>
                                <div>
                                    @foreach(\App\Enums\ComplianceLevel::getList() as $complianceLevel)
                                        <div style="width:190px;display: inline-block;vertical-align: top;">
                                            <p class="activeLink" style="font-size: 13px;">{{ t('compliance_rates_limits_table_heading_monthly_limit') }}</p>
                                            <input type="text" style="border: 1px solid #c1c1c1;border-radius: 10px;" name="monthly_amount_max[]" value="{{ old('monthly_amount_max.' . $loop->index) }}">
                                            @error('monthly_amount_max.' . $loop->index)<p class="text-danger">{{ $message }}</p>@enderror
                                        </div>
                                    @endforeach
                                </div>
                            </div>
                            @if($currentAdmin->hasPermissionInAnyProject([\App\Enums\BUserPermissions::ADD_AND_EDIT_CLIENT_RATES]))

                                <button class="btn themeBtn mt-3" type="submit" style="border-radius: 25px">{{ t('save') }}</button>
                            @endif
                        </form>
                    </div>
                </div>
            </div>
        </div>

        @if($currentAdmin->hasPermissionInAnyProject([\App\Enums\BUserPermissions::ADD_AND_EDIT_CLIENT_RATES]))

            <div class="modal fade modal-center" id="cardsRateTemplates" role="dialog">
                <div class="modal-dialog modal-dialog-center">
                    <!-- Modal content-->
                    <div class="modal-content" style="border:none;padding: 25px;width: 680px;border-radius: 30px;">
                        <div class="modal-body">
                            <div class="container">
                                <h1 id="bankCardRateHeader">{{ t('provider_new_bank_card_rate_plan') }}</h1><br>
                                <form action="{{ route('card.templates.store') }}" method="post"
                                      name="bankCardRateTemplateForm" id="bankCardRateTemplateForm">
                                    @csrf
                                    <div style="position: absolute;top: 15px;right: 25px;cursor: pointer"
                                         data-dismis="modal" class="activeLink" id="closeButton">X
                                    </div>

                                    <div style="display: inline-block; margin-right: 25px;vertical-align: bottom;">
                                        <label for="bankCardRateName" class="activeLink">{{ t('ui_name') }}</label><br>
                                        <input style="width: 275px" type="text" id="bankCardRateName"
                                               name="bankCardRateName" value="{{ old('bankCardRateName') }}"
                                               required><br>
                                        @error('bankCardRateName')<p class="text-danger">{{ $message }}</p>@enderror
                                    </div>
                                    <div style="display: inline-block; margin-right: 25px;vertical-align: bottom;">
                                        <select name="status" id="status"
                                                style="padding-right: 50px;vertical-align: bottom;">
                                            @foreach(\App\Enums\RateTemplatesStatuses::STATUSES as $code => $name)
                                                <option
                                                    value="{{ $code }}" {{ old('status') == $code ? 'selected' : '' }}>{{ $name }}</option>
                                            @endforeach
                                        </select>
                                        @error('status')<p class="text-danger">{{ $message }}</p>@enderror
                                    </div>

                                    <div class="mt-5">
                                        <h4>{{ t('cards_conditions_overview') }}</h4>

                                        <div class="row col-12">
                                            <p class="col-6 activeLink"> {{ t('type') }}</p>
                                            <p class="col-6 activeLink"> {{ t('fee') }} </p>
                                        </div>

                                        <div class="row col-12">
                                            <div class="col-6">
                                                <input type="text" class="wallesterConditionsInputs"
                                                       name="bankCardOverviewType"
                                                       value="{{ old('bankCardOverviewType') }}">
                                                @error('bankCardOverviewType')<p
                                                    class="text-danger">{{ $message }}</p>@enderror
                                            </div>
                                            <div class="col-6">
                                                <input type="text" class="wallesterConditionsInputs"
                                                       name="bankCardOverviewFee"
                                                       value="{{ old('bankCardOverviewFee') }}">
                                                @error('bankCardOverviewFee')<p
                                                    class="text-danger">{{ $message }}</p>@enderror
                                            </div>
                                        </div>

                                    </div>
                                    <div class="mt-5">
                                        <h4>{{ t('cards_conditions_transactions') }}</h4>

                                        <div class="row col-12">
                                            <p class="col-6 activeLink"> {{ t('type') }}</p>
                                            <p class="col-6 activeLink"> {{ t('fee') }} </p>
                                        </div>

                                        <div class="row col-12">
                                            <div class="col-6">
                                                <input type="text" class="wallesterConditionsInputs"
                                                       name="bankCardTransactionsType"
                                                       value="{{ old('bankCardTransactionsType') }}">
                                                @error('bankCardTransactionsType')<p
                                                    class="text-danger">{{ $message }}</p>@enderror
                                            </div>
                                            <div class="col-6">
                                                <input type="text" class="wallesterConditionsInputs"
                                                       name="bankCardTransactionsFee"
                                                       value="{{ old('bankCardTransactionsFee') }}">
                                                @error('bankCardTransactionsFee')<p
                                                    class="text-danger">{{ $message }}</p>@enderror
                                            </div>
                                        </div>
                                    </div>

                                    <div class="mt-5">
                                        <h4>{{ t('cards_conditions_fees') }}</h4>

                                        <div class="row col-12">
                                            <p class="col-6 activeLink"> {{ t('type') }}</p>
                                            <p class="col-6 activeLink"> {{ t('fee') }} </p>
                                        </div>

                                        <div class="row col-12">
                                            <div class="col-6">
                                                <input type="text" class="wallesterConditionsInputs"
                                                       name="bankCardFeesType" value="{{ old('bankCardFeesType') }}">
                                                @error('bankCardFeesType')<p
                                                    class="text-danger">{{ $message }}</p>@enderror
                                            </div>
                                            <div class="col-6">
                                                <input type="text" class="wallesterConditionsInputs"
                                                       name="bankCardFeesFee" value="{{ old('bankCardFeesFee') }}">
                                                @error('bankCardFeesFee')<p
                                                    class="text-danger">{{ $message }}</p>@enderror
                                            </div>
                                        </div>
                                    </div>

                                    <div class="mt-5">
                                        <div class="row col-12">
                                            <button
                                                class="btn btn-lg btn-primary themeBtn register-buttons mb-4 mb-md-0 mr-3"
                                                type="submit">Save
                                            </button>
                                        </div>
                                    </div>
                                </form>

                            </div>
                        </div>
                    </div>
                </div>
            </div>

        @endif


        <div class="modal fade modal-center" id="cardsRateTemplatesUpdate" role="dialog">
            <div class="modal-dialog modal-dialog-center">
                <!-- Modal content-->
                <div class="modal-content" style="border:none;padding: 25px;width: 680px;border-radius: 30px;">
                    <div class="modal-body">
                        <div class="container">
                            <h1 id="bankCardRateHeaderUpdate">{{ t('provider_edit_bank_card_rate_plan') }}</h1><br>
                            <form action="{{ route('card.templates.update') }}" method="post"
                                  name="bankCardRateTemplateForm" id="bankCardRateTemplateForm">
                                @csrf
                                <div style="position: absolute;top: 15px;right: 25px;cursor: pointer"
                                     data-dismis="modal" class="activeLink" id="closeButton">X
                                </div>

                                <div style="display: inline-block; margin-right: 25px;vertical-align: bottom;">
                                    <label for="bankCardRateName" class="activeLink">{{ t('ui_name') }}</label><br>
                                    <input style="width: 275px" type="text" id="bankCardRateNameUpdate"
                                           name="bankCardRateName" value="{{ old('bankCardRateName') }}" readonly><br>
                                    @error('bankCardRateName')<p class="text-danger">{{ $message }}</p>@enderror
                                </div>
                                <div style="display: inline-block; margin-right: 25px;vertical-align: bottom;">
                                    <select name="status" id="statusUpdate"
                                            style="padding-right: 50px;vertical-align: bottom;">
                                        @foreach(\App\Enums\RateTemplatesStatuses::STATUSES as $code => $name)
                                            <option
                                                value="{{ $code }}" {{ old('status') == $code ? 'selected' : '' }}>{{ $name }}</option>
                                        @endforeach
                                    </select>
                                    @error('status')<p class="text-danger">{{ $message }}</p>@enderror
                                </div>

                                <div class="mt-5">
                                    <h4>{{ t('cards_conditions_overview') }}</h4>

                                    <div class="row col-12">
                                        <p class="col-6 activeLink"> {{ t('type') }}</p>
                                        <p class="col-6 activeLink"> {{ t('fee') }} </p>
                                    </div>

                                    <div class="row col-12">
                                        <div class="col-6">
                                            <input type="text" class="wallesterConditionsInputs"
                                                   name="bankCardOverviewType"
                                                   value="{{ old('bankCardOverviewType') }}">
                                            @error('bankCardOverviewType')<p
                                                class="text-danger">{{ $message }}</p>@enderror
                                        </div>
                                        <div class="col-6">
                                            <input type="text" class="wallesterConditionsInputs"
                                                   name="bankCardOverviewFee" value="{{ old('bankCardOverviewFee') }}">
                                            @error('bankCardOverviewFee')<p
                                                class="text-danger">{{ $message }}</p>@enderror
                                        </div>
                                    </div>

                                </div>
                                <div class="mt-5">
                                    <h4>{{ t('cards_conditions_transactions') }}</h4>

                                    <div class="row col-12">
                                        <p class="col-6 activeLink"> {{ t('type') }}</p>
                                        <p class="col-6 activeLink"> {{ t('fee') }} </p>
                                    </div>

                                    <div class="row col-12">
                                        <div class="col-6">
                                            <input type="text" class="wallesterConditionsInputs"
                                                   name="bankCardTransactionsType"
                                                   value="{{ old('bankCardTransactionsType') }}">
                                            @error('bankCardTransactionsType')<p
                                                class="text-danger">{{ $message }}</p>@enderror
                                        </div>
                                        <div class="col-6">
                                            <input type="text" class="wallesterConditionsInputs"
                                                   name="bankCardTransactionsFee"
                                                   value="{{ old('bankCardTransactionsFee') }}">
                                            @error('bankCardTransactionsFee')<p
                                                class="text-danger">{{ $message }}</p>@enderror
                                        </div>
                                    </div>
                                </div>

                                <div class="mt-5">
                                    <h4>{{ t('cards_conditions_fees') }}</h4>

                                    <div class="row col-12">
                                        <p class="col-6 activeLink"> {{ t('type') }}</p>
                                        <p class="col-6 activeLink"> {{ t('fee') }} </p>
                                    </div>

                                    <div class="row col-12">
                                        <div class="col-6">
                                            <input type="text" class="wallesterConditionsInputs" name="bankCardFeesType"
                                                   value="{{ old('bankCardFeesType') }}">
                                            @error('bankCardFeesType')<p class="text-danger">{{ $message }}</p>@enderror
                                        </div>
                                        <div class="col-6">
                                            <input type="text" class="wallesterConditionsInputs" name="bankCardFeesFee"
                                                   value="{{ old('bankCardFeesFee') }}">
                                            @error('bankCardFeesFee')<p class="text-danger">{{ $message }}</p>@enderror
                                        </div>
                                    </div>
                                </div>

                                @if($currentAdmin->hasPermissionInAnyProject([\App\Enums\BUserPermissions::ADD_AND_EDIT_CLIENT_RATES]))
                                    <div class="mt-5">
                                        <div class="row col-12">
                                            <button
                                                class="btn btn-lg btn-primary themeBtn register-buttons mb-4 mb-md-0 mr-3"
                                                type="submit">Save
                                            </button>
                                        </div>
                                    </div>
                                @endif
                            </form>

                        </div>
                    </div>
                </div>
            </div>
        </div>

    </div>
@endsection
