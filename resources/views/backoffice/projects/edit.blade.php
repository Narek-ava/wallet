@extends('backoffice.layouts.backoffice')
@section('title', t('projects'))

@section('content')
    <div class="row mb-3 pb-3">
        <div class="col-md-12">
            <h2 class="mb-3 large-heading-section">{{ t('projects') }}</h2>
            <div class="row">
                <div class="col-lg-5 d-flex justify-content-between">
                    <div class="balance mb-4">
                        Platform is operated by {{ config('cratos.company_details.name') }} Registry
                        code {{config('cratos.company_details.registry')}}, registered at
                        {{config('cratos.company_details.address')}}, {{config('cratos.company_details.city')}}
                        , {{ config('cratos.company_details.zip_code') }}.
                    </div>
                </div>
                @include('cabinet.partials.notification', ['notify' => getNotification(\Illuminate\Support\Facades\Auth::id()), 'admin' => true])
            </div>
        </div>
    </div>
    <form action="{{ $url }}" method="POST" enctype="multipart/form-data">
        <div class="row mt-5">
            <div class="col-lg-10 col-md-10">
                @csrf
                @if(!$newProject)
                    @method('PUT')
                @endif
                <div class="row">
                    <div class="col-10">
                        <div class="form-group mt-5 align-items-start col-lg-12 col-md-12">
                            <div class="form-label-group">
                                <h3> {{ $newProject ? t('create_project') : t('update_project') }} </h3>
                            </div>
                        </div>
                    </div>
                </div>
                @if(session()->has('success'))
                    <div class="alert alert-success alert-dismissible fade show" role="alert" id="successMessageAlert">
                        <h4>{{ session()->get('success') }}</h4>
                        <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                            <span aria-hidden="true">&times;</span>
                        </button>
                    </div>
                @endif
                <div class="row">
                    <div data-setting="generalSettings"
                         class=" ticket-status-buttons pointerClass ticket-active"
                         style="width: 200px;max-width: 190px!important;">
                        General Settings
                    </div>
                    <div data-setting="colorSetting" class="col-md-2 ticket-status-buttons pointerClass ticket-inactive"
                         style="max-width: 125px">
                        Colors
                    </div>
                    <div data-setting="rateSetting" class="col-md-2 ticket-status-buttons pointerClass ticket-inactive"
                         style="max-width: 125px">
                        Rates
                    </div>
                    <div data-setting="providerSetting"
                         class="col-md-2 ticket-status-buttons pointerClass ticket-inactive" style="max-width: 125px">
                        Providers
                    </div>
                    <div data-setting="managerSetting"
                         class="col-md-2 ticket-status-buttons pointerClass ticket-inactive" style="max-width: 125px">
                        Managers
                    </div>
                    <div data-setting="clientWallets"
                         class="col-md-3 ticket-status-buttons pointerClass ticket-inactive" style="max-width: 162px">
                        {{ t('ui_permission_client_wallets') }}
                    </div>
                </div>

                <div class="projectSettings" id="generalSettings">
                    <div class="row mt-5">

                        <div class="col-6">
                            <div class="form-group mt-5 align-items-start col-lg-8 col-md-8">
                                <div class="form-label-group">
                                    <h5><label for="status">{{ t('status') }}</label></h5>

                                    <select id="status" class="form-control grey-rounded-border" name="status">
                                        @foreach(\App\Enums\ProjectStatuses::getList() as $value => $status)
                                            <option
                                                @if(old('status') === $value || (!old('status') && isset($project->status) && $project->status === $value)) selected
                                                @endif  value="{{ $value }}">
                                                {{ $status }}
                                            </option>
                                        @endforeach
                                    </select>
                                </div>
                                @error('project_name')
                                <p class="error-text">{{ $message }}</p>
                                @enderror
                            </div>
                            <div class="form-group mt-5 col-lg-8 col-md-8">
                                <div class="form-label-group">
                                    <h5><label for="projectName">{{ t('project_name') }}</label></h5>
                                    <input id="projectName" name="name" type="text"
                                           value="{{ old('name') ?? ($project->name ?? null) }}"
                                           class="form-control{{ $errors->has('name') ? ' is-invalid' : '' }}" required>
                                </div>
                                @error('name')
                                <p class="error-text">{{ $message }}</p>
                                @enderror
                            </div>
                            <div class="form-group mt-5 col-lg-12 col-md-12">
                                <div class="d-flex flex-row">
                                    <div class="form-label-group">
                                        <h5><label for="projectDomain">{{ t('domain') }}</label></h5>
                                        <input id="projectDomain" name="domain" type="text"
                                               value="{{ old('domain') ?? ($project->domain ?? null) }}"
                                               class="form-control{{ $errors->has('domain') ? ' is-invalid' : '' }}"
                                               required>
                                    </div>
                                </div>
                                <small>Example: dev.example.net</small>
                                @error('domain')
                                <p class="error-text">{{ $message }}</p>
                                @enderror
                            </div>
                        </div>

                        <div class="col-6">
                            <div class="form-group mt-5 d-flex flex-column align-items-start">
                                <h5><label for="projectName">{{ t('project_logo') }}</label></h5>
                                <img src="{{ $project->logoPng ?? '' }}" alt="" id="updateProjectLogo"
                                     style="width: auto; height: 100px;">
                                <label id="labelFile" for="projectLogo" @if(!$newProject) class="mt-4" @endif
                                style="border: 1px solid; padding: 6px 37px 3px 37px; border-radius: 10px;"> {{ t('upload') }} </label>
                                <input type="file" id="projectLogo" style="display: none;" class="hidden" name="logo">
                                <p id="updateProjectLogoStatus" class="text-success"></p>
                                @error('logo')
                                <p class="error-text">{{ $message }}</p>
                                @enderror
                            </div>

                        </div>
                    </div>
                    <div class="row mt-5">

                        <div class="form-group mt-5 col-lg-3 col-md-3">
                            <div class="form-label-group">
                                <h5><label for="termsAndConditions">{{ t('ui_terms_and_conditions') }}</label></h5>
                                <input id="terms_and_conditions" name="termsAndConditions" type="text"
                                       placeholder="https://example.com"
                                       value="{{ old('termsAndConditions') ?? ($project->companyDetails->terms_and_conditions ?? null) }}"
                                       class="form-control{{ $errors->has('terms_and_conditions') ? ' is-invalid' : '' }}">
                            </div>
                            @error('termsAndConditions')
                            <p class="error-text">{{ $message }}</p>
                            @enderror
                        </div>


                        <div class="form-group mt-5 col-lg-3 col-md-3">
                            <div class="form-label-group">
                                <h5><label for="amlPolicy">{{ t('aml_policy') }}</label></h5>
                                <input id="aml_policy" name="amlPolicy" type="text" placeholder="https://example.com"
                                       value="{{ old('amlPolicy') ?? ($project->companyDetails->aml_policy ?? null) }}"
                                       class="form-control{{ $errors->has('amlPolicy') ? ' is-invalid' : '' }}">
                            </div>
                            @error('amlPolicy')
                            <p class="error-text">{{ $message }}</p>
                            @enderror
                        </div>

                        <div class="form-group mt-5 col-lg-3 col-md-3">
                            <div class="form-label-group">
                                <h5><label for="privacyPolicy">{{ t('privacy_policy') }}</label></h5>
                                <input id="privacy_policy" name="privacyPolicy" type="text"
                                       placeholder="https://example.com"
                                       value="{{ old('privacyPolicy') ?? ($project->companyDetails->privacy_policy ?? null) }}"
                                       class="form-control{{ $errors->has('privacyPolicy') ? ' is-invalid' : '' }}">
                            </div>
                            @error('privacyPolicy')
                            <p class="error-text">{{ $message }}</p>
                            @enderror
                        </div>

                        <div class="form-group mt-5 col-lg-3 col-md-3">
                            <div class="form-label-group">
                                <h5><label for="frequentlyAskedQuestion">{{ t('ui_cabinet_menu_faq') }}</label></h5>
                                <input id="frequently_asked_question" name="frequentlyAskedQuestion" type="text"
                                       placeholder="https://example.com"
                                       value="{{ old('frequentlyAskedQuestion') ?? ($project->companyDetails->frequently_asked_question ?? null) }}"
                                       class="form-control{{ $errors->has('frequentlyAskedQuestion') ? ' is-invalid' : '' }}">
                            </div>
                            @error('frequentlyAskedQuestion')
                            <p class="error-text">{{ $message }}</p>
                            @enderror
                        </div>

                        <div class="form-group mt-5 col-lg-3 col-md-3">
                            <div class="form-label-group">
                                <h5><label for="companyName">{{ t('company_name') }}</label></h5>
                                <input id="companyName" name="companyName" type="text"
                                       value="{{ old('companyName') ?? ($project->companyDetails->name ?? null) }}"
                                       class="form-control{{ $errors->has('companyName') ? ' is-invalid' : '' }}"
                                       required>
                            </div>
                            @error('companyName')
                            <p class="error-text">{{ $message }}</p>
                            @enderror
                        </div>

                        <div class="form-group mt-5 col-lg-3 col-md-3">
                            <div class="form-label-group">
                                <h5><label for="companyCountry">{{ t('companyCountry') }}</label></h5>
                                <input id="companyCountry" name="companyCountry" type="text"
                                       value="{{ old('companyCountry') ?? ($project->companyDetails->country ?? null) }}"
                                       class="form-control{{ $errors->has('companyCountry') ? ' is-invalid' : '' }}"
                                       required>
                            </div>
                            @error('companyCountry')
                            <p class="error-text">{{ $message }}</p>
                            @enderror
                        </div>

                        <div class="form-group mt-5 col-lg-3 col-md-3">
                            <div class="form-label-group">
                                <h5><label for="companyCity">{{ t('companyCity') }}</label></h5>
                                <input id="companyCity" name="companyCity" type="text"
                                       value="{{ old('companyCity') ?? ($project->companyDetails->city ?? null) }}"
                                       class="form-control{{ $errors->has('companyCity') ? ' is-invalid' : '' }}">
                            </div>
                            @error('companyCity')
                            <p class="error-text">{{ $message }}</p>
                            @enderror
                        </div>

                        <div class="form-group mt-5 col-lg-3 col-md-3">
                            <div class="form-label-group">
                                <h5><label for="companyZipCode">{{ t('companyZipCode') }}</label></h5>
                                <input id="companyZipCode" name="companyZipCode" type="text"
                                       value="{{ old('companyZipCode') ?? ($project->companyDetails->zip_code ?? null) }}"
                                       class="form-control{{ $errors->has('companyZipCode') ? ' is-invalid' : '' }}">
                            </div>
                            @error('companyZipCode')
                            <p class="error-text">{{ $message }}</p>
                            @enderror
                        </div>

                        <div class="form-group mt-5 col-lg-3 col-md-3">
                            <div class="form-label-group">
                                <h5><label for="companyAddress">{{ t('companyAddress') }}</label></h5>
                                <input id="companyAddress" name="companyAddress" type="text"
                                       value="{{ old('companyAddress') ?? ($project->companyDetails->address ?? null) }}"
                                       class="form-control{{ $errors->has('companyAddress') ? ' is-invalid' : '' }}">
                            </div>
                            @error('companyAddress')
                            <p class="error-text">{{ $message }}</p>
                            @enderror
                        </div>

                        <div class="form-group mt-5 col-lg-3 col-md-3">
                            <div class="form-label-group">
                                <h5><label for="companyLicense">{{ t('companyLicense') }}</label></h5>
                                <input id="companyLicense" name="companyLicense" type="text"
                                       value="{{ old('companyLicense') ?? ($project->companyDetails->license ?? null) }}"
                                       class="form-control{{ $errors->has('companyLicense') ? ' is-invalid' : '' }}">
                            </div>
                            @error('companyLicense')
                            <p class="error-text">{{ $message }}</p>
                            @enderror
                        </div>

                        <div class="form-group mt-5 col-lg-3 col-md-3">
                            <div class="form-label-group">
                                <h5><label for="companyRegistry">{{ t('companyRegistry') }}</label></h5>
                                <input id="companyRegistry" name="companyRegistry" type="text"
                                       value="{{ old('companyRegistry') ?? ($project->companyDetails->registry ?? null) }}"
                                       class="form-control{{ $errors->has('companyRegistry') ? ' is-invalid' : '' }}">
                            </div>
                            @error('companyRegistry')
                            <p class="error-text">{{ $message }}</p>
                            @enderror
                        </div>

                    </div>
                </div>

                <div class="projectSettings" id="colorSetting" hidden>
                    <div class="row">
                        <div class="col-6">
                            <div class="form-group align-items-start col-lg-10 col-md-10">
                                <div class="form-label-group">
                                    <h3>Colors</h3>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="row pl-3">
                        <div class="col-md-4">
                            <p class="activeLink"><label for="mainColor">Main color</label></p>
                            <input style=" margin:2px 5px;width: 50px;border-radius: 2px" type="color" name="mainColor"
                                   value="{{ $project->colors->mainColor ?? '#fe3d2b' }}">

                            @error('mainColor')
                            <p class="error-text">{{ $message }}</p>
                            @enderror
                        </div>

                        <div class="col-md-4">
                            <p class="activeLink"><label for="buttonColor">Buttons color</label></p>
                            <input style=" margin:2px 5px;width: 50px;border-radius: 2px" type="color"
                                   name="buttonColor"
                                   value="{{ old('buttonColor') ?? ($project->colors->buttonColor ?? '#fe3d2b') }}">

                            @error('buttonColor')
                            <p class="error-text">{{ $message }}</p>
                            @enderror
                        </div>

                        <div class="col-md-4">
                            <p class="activeLink">
                                <label for="borderColor">Border color</label>
                            </p>
                            <input style=" margin:2px 5px;width: 50px;border-radius: 2px" type="color"
                                   name="borderColor"
                                   value="{{ old('borderColor') ?? ($project->colors->borderColor ?? '#fe3d2b') }}">
                            @error('borderColor')
                            <p class="error-text">{{ $message }}</p>
                            @enderror
                        </div>

                        <div class="col-md-4">
                            <p class="activeLink">
                                <label for="notifyFromColor">Notify color(from)</label>
                            </p>
                            <input style=" margin:2px 5px;width: 50px;border-radius: 2px" type="color"
                                   name="notifyFromColor"
                                   value="{{ old('notifyFromColor') ?? ($project->colors->notifyFromColor ?? '#f96283') }}">
                            @error('notifyFromColor')
                            <p class="error-text">{{ $message }}</p>
                            @enderror
                        </div>

                        <div class="col-md-4">
                            <p class="activeLink">
                                <label for="notifyToColor">Notify color(to)</label>
                            </p>
                            <input style=" margin:2px 5px;width: 50px;border-radius: 2px" type="color"
                                   name="notifyToColor"
                                   value="{{ old('notifyToColor') ?? ($project->colors->notifyToColor ?? '#ffc052') }}">
                            @error('notifyToColor')
                            <p class="error-text">{{ $message }}</p>
                            @enderror
                        </div>
                    </div>
                </div>

                <div class="projectSettings" id="rateSetting" hidden>
                    <div class="row">
                        <div class="col-6">
                            <div class="form-group mt-2 align-items-start col-lg-10 m-0 p-0 col-md-10">
                                <div class="form-label-group">
                                    <h3>{{ t('ui_rates_list_header') }}</h3>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="row mb-5">
                        <div class="form-group mt-2 align-items-start col-lg-6 col-md-6">
                            <div class="form-label-group">
                                <label for="individualRate"
                                       class="activeLink">{{ t('individual_rate_name') }}</label><br>

                                <select name="individualRate" id="" style="padding-right: 50px;" required>
                                    @foreach($rates as $rate)
                                        <option
                                            @if(old('individualRate') == $rate->id || (isset($project->individualRate) && $project->individualRate->id == $rate->id )) selected
                                            @endif
                                            value="{{ $rate->id }}">{{ $rate->name }}</option>
                                    @endforeach
                                </select><br>
                                <span class="text-danger"></span><br>
                                @error('individualRate')
                                <p class="text-danger">{{ $message }}</p>
                                @enderror
                            </div>
                        </div>
                        <div class="form-group mt-2 align-items-start col-lg-6 col-md-6">
                            <div class="form-label-group">
                                <label for="corporateRate" class="activeLink">{{ t('corporate_rate_name') }}</label><br>
                                <select name="corporateRate" id="" style="padding-right: 50px;" required>
                                    @foreach($rates as $key => $rate)
                                        <option
                                            @if(old('corporateRate') == $rate->id || (isset($project->corporateRate) && $project->corporateRate->id == $rate->id )) selected
                                            @endif
                                            value="{{ $rate->id }}">{{ $rate->name }}</option>
                                    @endforeach
                                </select><br>
                                <span class="text-danger" id=""></span><br>
                                @error('corporateRate')
                                <p class="text-danger">{{ $message }}</p>
                                @enderror
                            </div>
                        </div>
                        <div class="form-group mt-2 align-items-start col-lg-6 col-md-6">
                            <div class="form-label-group">
                                <label for="bankCardRate"
                                       class="activeLink">{{ t('compliance_rates_bank_rates') }}</label><br>
                                <select name="bankCardRate" id="" style="padding-right: 50px;" required>
                                    @foreach($bankCardRates as $key => $rate)
                                        <option
                                            @if(old('bankCardRate') == $rate->id || (isset($project->bankCardRate) && $project->bankCardRate->id == $rate->id )) selected
                                            @endif
                                            value="{{ $rate->id }}">{{ $rate->name }}</option>
                                    @endforeach
                                </select><br>
                                <span class="text-danger" id=""></span><br>
                                @error('bankCardRate')
                                <p class="text-danger">{{ $message }}</p>
                                @enderror
                            </div>
                        </div>
                    </div>
                </div>

                <div class="projectSettings" id="providerSetting" hidden>

                    <div class="row">
                        <div class="col-4">
                            <div class="form-group mt-2 align-items-start col-lg-8 m-0 p-0  col-md-8">
                                <div class="form-label-group">
                                    <h3>Providers</h3>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="row mb-3 mt-3">
                        <div class="col-3">
                            <div class="form-group align-items-start m-0 p-0 col-lg-12 col-md-12">
                                <div class="form-label-group">
                                    <h6>{{ t('wallet_provider') }}</h6>
                                    <select name="walletProvider" id="walletProvider" style="width: 100%" required>
                                        <option></option>
                                        @foreach($allWalletProviders as $walletProvider)
                                            <option
                                                @if(old('walletProvider') == $walletProvider->id || (!old('walletProvider') && isset($projectWalletProvider) && $projectWalletProvider->id === $walletProvider->id)) selected
                                                @endif value="{{ $walletProvider->id }}">{{ $walletProvider->name }}</option>
                                        @endforeach
                                    </select>
                                </div>
                                @error('walletProvider')
                                <p class="error-text mt-3">{{ $message }}</p>
                                @enderror
                            </div>
                        </div>
                        <div class="col-3">
                            <div class="form-group align-items-start col-lg-12 col-md-12">
                                <div class="form-label-group">
                                    <h6>{{ t('card_provider') }}</h6>
                                    <select name="cardProvider" id="cardProvider" style="width: 100%" required>
                                        <option></option>

                                        @foreach($allCardProviders as $cardProvider)
                                            <option
                                                @if(old('cardProvider') == $cardProvider->id || (!old('cardProvider') && isset($projectCardProvider) && $projectCardProvider->id === $cardProvider->id)) selected
                                                @endif value="{{ $cardProvider->id }}">{{ $cardProvider->name }}</option>
                                        @endforeach
                                    </select>
                                </div>
                                @error('cardProvider')
                                <p class="error-text mt-3">{{ $message }}</p>
                                @enderror
                            </div>
                        </div>
                        <div class="col-3">
                            <div class="form-group align-items-start col-lg-12 col-md-12">
                                <div class="form-label-group">
                                    <h6>{{ t('default_card_issuing_provider') }}</h6>
                                    <select name="issuingProvider" id="issuingProvider" style="width: 100%" required>
                                        <option></option>
                                        @foreach($allCardIssuingProviders as $cardIssuingProvider)
                                            <option
                                                @if(old('issuingProvider') || (!old('issuingProvider') && isset($projectDefaultIssuingProvider) && $projectDefaultIssuingProvider->id === $cardIssuingProvider->id)) selected
                                                @endif value="{{ $cardIssuingProvider->id }}">{{ $cardIssuingProvider->name }}</option>
                                        @endforeach
                                    </select>
                                </div>
                                @error('issuingProvider')
                                <p class="error-text mt-3">{{ $message }}</p>
                                @enderror
                            </div>
                        </div>


                    </div>
                    <div class="row mb-3">
                        <div class="row col-12">
                            <div class="form-group align-items-end col-lg-6 col-md-6 pr-0">
                                <div class="form-label-group">
                                    <label for="paymentProviders"
                                           class="activeLink">{{ t('payment_providers') }}</label><br>

                                    <select name="paymentProviders[]" multiple="multiple" id="paymentProviders"
                                            style="padding-right: 50px;">
                                        @foreach($allPaymentProviders as $p_key => $paymentProvider)
                                            <option
                                                @if( (is_array(old('paymentProviders') ) && in_array( $paymentProvider->id, old('paymentProviders') )) || (isset($projectPaymentProviders) && array_search($paymentProvider->id, $projectPaymentProviders) !== false)) selected
                                                @endif
                                                value="{{ $paymentProvider->id }}">{{ $paymentProvider->name }}</option>
                                        @endforeach
                                    </select><br>
                                    <span class="text-danger" id="paymentProviders"></span><br>
                                    @error('paymentProviders')
                                    <p class="error-text mt-3">{{ $message }}</p>
                                    @enderror
                                </div>
                            </div>

                        </div>
                    </div>

                    <div class="row mb-3">
                        <div class="col-12 row d-flex m-0 p-0 justify-content-between">
                            <div class="form-group align-items-end col-lg-6 col-md-6 pr-0">
                                <div class="form-label-group">
                                    <label for="liquidityProviders"
                                           class="activeLink">{{ t('liquidity_providers') }}</label><br>

                                    <select name="liquidityProviders[]" multiple="multiple" id="liquidityProviders"
                                            style="padding-right: 50px;">
                                        @foreach($allLiquidityProviders as $l_key => $liquidityProvider)
                                            <option
                                                @if( (is_array(old('liquidityProviders')) && in_array($liquidityProvider->id, old('liquidityProviders') ) ) ||  (!$newProject && isset($projectLiqProviders) && array_search($liquidityProvider->id, array_keys($projectLiqProviders)) !== false)) selected
                                                @endif
                                                value="{{ $liquidityProvider->id }}">{{ $liquidityProvider->name }}</option>
                                        @endforeach
                                    </select><br>
                                    <span class="text-danger" id="liquidityProviders"></span><br>
                                    @error('liquidityProviders')
                                    <p class="error-text mt-3">{{ $message }}</p>
                                    @enderror
                                    @error('liqProvider')
                                    <p class="error-text mt-3">{{ $message }}</p>
                                    @enderror
                                </div>
                            </div>

                            <div class="col-4 ml-4 p-0 m-0">
                                <h6>{{ t('choose_default_provider') }}</h6>
                                <div class="liq-providers d-flex flex-column justify-content-end">
                                    @if(isset($projectLiqProviders) && isset($projectDefaultLiqProvider))
                                        @foreach($projectLiqProviders as $id => $name)
                                            <div class="d-flex flex-row mt-2 {{ $id }}">
                                                <small>
                                                    <input type="radio" class="text-right mr-3 mt-2" id="liq{{ $id }}"
                                                           name="liqProvider"
                                                           @if(old('liqProvider') == $id || (!old('liqProvider') && $id == $projectDefaultLiqProvider->id)) checked
                                                           @endif value="{{ $id }}"/>
                                                </small>
                                                <label for="liq{{ $id }}"> {{ $name }} </label>
                                            </div>
                                        @endforeach
                                    @elseif(old('liquidityProviders') && $newProject)
                                        @foreach($projectLiqProviders as $id => $name)
                                            @continue(!in_array($id, old('liquidityProviders')))
                                            <div class="d-flex flex-row mt-2 {{ $id }}">
                                                <small>
                                                    <input type="radio" class="text-right mr-3 mt-2" id="liq{{ $id }}"
                                                           name="liqProvider"
                                                           @if(old('liqProvider') == $id) checked
                                                           @endif value="{{ $id }}"/>
                                                </small>
                                                <label for="liq{{ $id }}"> {{ $name }} </label>
                                            </div>
                                        @endforeach
                                    @endif
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="row mb-3">
                        <div class="col-3">
                            <div class="form-label-group">
                                <label for="smsProviders" class="activeLink">{{ t('sms_providers') }}</label><br>

                                <select name="smsProviders[]" multiple="multiple" id="smsProviders"
                                        style="padding-right: 50px;" required>
                                    @foreach($allSmsProviders as $key => $smsProvider)
                                        <option
                                            @if(isset($projectSmsProviders[$key]) || (is_array(old('smsProviders')) && in_array($key, old('smsProviders'))) ) selected
                                            @endif
                                            value="{{ $key }}">{{ $smsProvider }}</option>
                                    @endforeach
                                </select><br>
                                <span class="text-danger" id="smsProviders"></span><br>
                                @error('smsProviders')
                                <p class="text-danger">{{ $message }}</p>
                                @enderror
                            </div>
                        </div>
                        <div class="col-3">
                            <div class="form-label-group">
                                <label for="emailProvider" class="activeLink">{{ t('email_providers') }}</label><br>
                                <select name="emailProvider" id="emailProvider" style="padding-right: 50px;" required>
                                    <option></option>
                                    @foreach($allEmailProviders as $key => $eProvider)
                                        <option
                                            @if(old('emailProvider') == $key || (!old('emailProvider') && isset($project->emailProvider) &&  $project->emailProvider->key == $key)) selected
                                            @endif
                                            value="{{ $key }}">{{ $eProvider['name'] . ' - ' . $eProvider['address'] }}</option>
                                    @endforeach
                                </select><br>
                                <span class="text-danger" id="emailProviders"></span><br>
                                @error('emailProvider')
                                <p class="text-danger">{{ $message }}</p>
                                @enderror
                            </div>
                        </div>
                    </div>
                    <div class="row mb-3">
                        <div class="col-3">
                            <div class="form-group">
                                <div class="form-label-group">
                                    <h6>{{ t('compliance_provider') }}</h6>
                                    <select name="complianceProvider" id="complianceProvider" style="width: 100%">
                                        <option></option>
                                        @foreach($allComplianceProviders as $complianceProvider)
                                            <option
                                                @if( old('complianceProvider') == $complianceProvider->id ||  (isset($projectComplianceProvider) && $projectComplianceProvider->id === $complianceProvider->id )) selected
                                                @endif
                                                value="{{ $complianceProvider->id }}">{{ $complianceProvider->name }}</option>
                                        @endforeach
                                    </select>
                                </div>
                                @error('complianceProvider')
                                <p class="error-text mt-3">{{ $message }}</p>
                                @enderror
                            </div>
                        </div>

                        <div class="col-4">
                            <div class="form-group">
                                <div class="form-label-group">
                                    <h6>{{ t('renewalInterval') }}</h6>
                                    <select name="renewalInterval" id="renewalInterval" style="width: 100%">
                                        <option value="">No send</option>
                                        @for ($i = 1; $i < 13; $i++)
                                            <option
                                                @if( old('renewalInterval') == $i ||  (isset($projectComplianceProvider) && isset($projectComplianceProvider->pivot->renewal_interval) && $projectComplianceProvider->pivot->renewal_interval === $i )) selected
                                                @endif
                                                value="{{ $i }}">{{ $i }} {{ t('month') }}</option>
                                        @endfor
                                    </select>
                                </div>
                                @error('renewalInterval')
                                <p class="error-text mt-3">{{ $message }}</p>
                                @enderror
                            </div>
                        </div>
                    </div>

                    <div class="row mb-3">
                        <div class="col-3">
                            <div class="form-group">
                                <div class="form-label-group">
                                    <h6>{{ t('kyt_provider') }}</h6>
                                    <select name="kytProvider" id="kytProvider" style="width: 100%">
                                        <option></option>
                                        @foreach($allKYTProviders as $kytProvider)
                                            <option
                                                @if( old('kytProvider') == $kytProvider ||  (isset($projectKytProvider)))
                                                    @if($projectKytProvider->id === $kytProvider->id)
                                                        selected="selected"
                                                    @endif
                                                @endif
                                                value="{{ $kytProvider->id }}">
                                                {{ $kytProvider->name }}

                                            </option>
                                        @endforeach
                                    </select>
                                </div>
                                @error('kytProvider')
                                <p class="error-text mt-3">{{ $message }}</p>
                                @enderror
                            </div>
                        </div>
                    </div>
                </div>

                <div class="projectSettings" id="managerSetting" hidden>

                    <div class="row">
                        <div class="col-4">
                            <div class="form-group mt-2 align-items-start col-lg-8 col-md-8">
                                <div class="form-label-group">
                                    <h3>Managers</h3>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="form-group row col-12">
                        <div class="col-6">
                            <div id="availableBUsers" class="mt-5 m-0 p-0">
                                <h5>{{ t('ui_available_managers') }}</h5>
                                <select name="bUsers[]" id="bUsers" data-projects="{{ json_encode($bUsers) }}"
                                        multiple="multiple" style="width: 800px;">
                                    @foreach($bUsers as $id => $bUserEmail)
                                        <option @if((old('bUsers') && in_array( $id, old('bUsers')) )||  isset($usersWithRoles[$id])) selected
                                                @endif value="{{ $id }}">{{ $bUserEmail }}</option>
                                    @endforeach
                                </select>
                                @error('bUsers')<p class="text-danger">{{ $message }}</p>@enderror
                            </div>
                        </div>
                        <div class="col-12 mt-2">
                            <div class="manager-roles d-flex flex-column justify-content-start"
                                 data-roles="{{ json_encode($availableRoles) }}">
                                @if(!$newProject)
                                    @foreach($usersWithRoles as $bUserId => $roles)
                                        @continue(!isset($bUsers[$bUserId]))
                                        <div class="d-flex flex-row mt-5 {{$bUserId}}">
                                            <div class="col-4"><h5> {{ $bUsers[$bUserId] }} </h5></div>
                                            <div class="col-8 ml-4">
                                                <select name="{{ 'roles[' . $bUserId . '][]' }}" class="roles"
                                                        multiple="multiple" style="width: 800px;">
                                                    @foreach($availableRoles as $role)
                                                        <option value="{{ $role }}"
                                                                @if(in_array($role, $usersWithRoles[$bUserId])) selected @endif>{{ $role }}</option>
                                                    @endforeach
                                                </select>
                                                @error('roles.' . $bUserId)<p
                                                    class="text-danger">{{ $message }}</p>@enderror
                                                @error('mainRoles')<p class="text-danger">{{ $message }}</p>@enderror
                                            </div>
                                        </div>
                                    @endforeach
                                @elseif(!empty(old('bUsers')))
                                    @foreach( old('bUsers') as $bUserId)
                                        @continue(!isset($bUsers[$bUserId]))
                                        <div class="d-flex flex-row mt-5 {{$bUserId}}">
                                            <div class="col-4"><h5> {{ $bUsers[$bUserId] }} </h5></div>
                                            <div class="col-8 ml-4">
                                                <select name="{{ 'roles[' . $bUserId . '][]' }}" class="roles"
                                                        multiple="multiple" style="width: 800px;">
                                                    @foreach($availableRoles as $role)
                                                        <option value="{{ $role }}"
                                                                @if( (old('roles') && in_array($role, old('roles')[$bUserId]))) selected @endif
                                                        >{{ $role }}</option>
                                                    @endforeach
                                                </select>
                                                @error('roles.' . $bUserId)<p
                                                    class="text-danger">{{ $message }}</p>@enderror
                                                @error('mainRoles')<p class="text-danger">{{ $message }}</p>@enderror
                                            </div>
                                        </div>
                                    @endforeach
                                @endif
                            </div>
                        </div>
                    </div>
                </div>

                <div class="projectSettings" id="clientWallets" hidden>
                    <div class="row">
                        <div class="col-4">
                            <div class="form-group mt-5 align-items-start col-lg-8 col-md-8">
                                <div class="form-label-group">
                                    <h3>{{ t('ui_permission_client_wallets') }}</h3>
                                </div>
                            </div>
                        </div>
                    </div>
                    @if(isset($project))
                        <div class="row">
                            <div class=" col-2 form-group m-0 p-0 pl-0">
                                <div class="form-label-group">
                                    <a href="{{ route('backoffice.client.wallet.create', ['projectId' => $project->id]) }}"
                                       class="btn btn-lg btn-primary themeBtn btn-block" target="_blank"
                                       type="submit">{{ t('create_client_wallet') }}</a>
                                </div>
                            </div>
                        </div>
                        @if( isset($clientWallets) )
                            @if($clientWallets->count())
                                <div class="row mt-3">
                                    <div class="col-md-1 activeLink">No</div>
                                    <div class="col-md-1 activeLink">Currency</div>
                                    <div class="col-md-4 activeLink text-center">Wallet id</div>
                                </div>
                                @foreach($clientWallets as $key => $clientWallet)
                                    <div class="row providersAccounts-item">
                                        <div class="col-md-1">{{ $key + 1 }}</div>
                                        <div class="col-md-1">{{ $clientWallet->currency }}</div>
                                        <div class="col-md-4 breakWord text-center"
                                             style="min-width: inherit">{{ $clientWallet->wallet_id ?? '-' }}</div>
                                        @if($currentAdmin->isAllowed([\App\Enums\BUserPermissions::EDIT_CLIENT_WALLETS], $clientWallet->project_id))
                                            <div class="col-md-3 text-center">
                                                <a href="{{ route('client-wallets.edit', ['client_wallet' => $clientWallet]) }}"
                                                   class="nav-link"
                                                   style="color: black;text-decoration: underline;">{{ t('ui_edit') }}</a>
                                            </div>
                                            <div class="col-md-3 text-center">
                                                <form method="post"
                                                      action="{{ route('regenerate.webhook', ['clientSystemWallet' => $clientWallet]) }}">
                                                    @csrf
                                                    <button type="submit" class="nav-link border-none"
                                                            style="background-color: transparent; cursor: pointer">
                                                        {{ t('ui_regenerate_webhook') }} </button>
                                                </form>
                                            </div>
                                        @endif
                                    </div>
                                @endforeach
                            @endif
                        @else
                            <form action=""></form>
                        @endif
                    @else
                        <div class="row">
                            <input type="hidden" name="createClientWallet" value="true">
                            <div class="col-12" id="clientWalletsBlock">
                                @foreach(\App\Enums\Currency::getList() as $currency)
                                    <div class="row {{ $currency }} mt-3">
                                        <div class="col-2">
                                            <p class="activeLink">Currency</p>
                                            <input readonly name="currency{{$currency}}" value="{{ $currency }}">
                                            @error('currency' . $currency)
                                            <p class="text-danger">{{ $message }}</p>
                                            @enderror
                                        </div>

                                        <div class="col-5">
                                            <p class="activeLink">Wallet ID</p>
                                            <input style="width: 100%" name="walletId{{ $currency }}">
                                            @error('walletId' . $currency)
                                            <p class="text-danger">{{ $message }}</p>
                                            @enderror
                                        </div>

                                        <div class="col-5">
                                            <p class="activeLink">Passphrase</p>
                                            <input type="password" autocomplete="new-password" style="width: 100%" name="passphrase{{$currency}}">
                                            @error('passphrase' .$currency)
                                            <p class="text-danger">{{ $message }}</p>
                                            @enderror
                                        </div>

                                    </div>
                                @endforeach
                            </div>
                        </div>
                    @endif
                </div>

                <div class="col-12">
                    @if($currentAdmin->isAllowed([\App\Enums\BUserPermissions::EDIT_PROJECTS], $project->id ?? null))
                        <div class=" col-md-3 form-group mt-5 m-0 p-0 pl-0">
                            <div class="form-label-group">
                                <button class="btn btn-lg btn-primary themeBtn btn-block" id="submitButton" disabled
                                        type="submit">{{ t('save') }}</button>
                            </div>
                        </div>
                    @endif
                </div>

            </div>
        </div>
    </form>
@endsection

@section('scripts')
    <script src="/js/backoffice/projects/projects.js"></script>
    <script>

        $(document).ready(function () {
            @if( $errors->any() )
            @if($errors->has('generalSettings'))
            $("[data-setting='generalSettings']").click();
            @elseif($errors->has('colorSetting'))
            $("[data-setting='colorSetting']").click();
            @elseif($errors->has('rateSetting'))
            $("[data-setting='rateSetting']").click();
            @elseif($errors->has('managerSetting'))
            $("[data-setting='managerSetting']").click();
            @elseif($errors->has('providerSetting'))
            $("[data-setting='providerSetting']").click();
            @elseif($errors->has('clientWallets'))
            $("[data-setting='clientWallets']").click();
            @endif
            @endif
        });


    </script>
@endsection
