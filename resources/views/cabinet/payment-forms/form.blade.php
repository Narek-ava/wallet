@extends('cabinet.layouts.payment-form')

@section('content')

    <div class="container main-form" hidden>
        <a href="{{ $project->domain ?? '' }}" class="navbar-brand d-block pb-3 text-center">
            <img src="{{ $project->logoPng ?? '' }}" class="projectLogo" alt="">
        </a>
        <div class="login-form login-form-outer-merchant-payment ml-auto mr-auto">
            <div class="common-form">
                <h3 class="text-center step step-1 d-block"><span>{{ t('buy_crypto') }}</span></h3>
                <p class="text-center step step-1 d-block"><span>{{ t('via_card') }}</span></p>

                <h3 class="text-center step step-4 back-step d-none">
                    <span>{{ t('payment_first_step') }}</span>
                    <a href="javascript:void(0)" class="back-step step step-4 d-none">
                        <img src="{{ config('cratos.urls.theme') }}images/back-50.png" alt="">
                    </a>
                </h3>
                <p class="text-center step step-4 d-none"><span>{{ t('verify_phone_number') }}</span></p>


                <h3 class="text-center step step-login back-step d-none">
                    <span>{{ t('payment_login_step') }}</span>
                    <a href="javascript:void(0)" class="back-step-login step step-login d-none">
                        <img src="{{ config('cratos.urls.theme') }}images/back-50.png" alt="">
                    </a>
                </h3>
                <p class="text-center step step-login d-none"><span>{{ t('already_have_an_account') }}</span></p>


                <h3 class="text-center step step-2 back-step d-none">
                    <span>{{ t('verify_email') }}</span>
                    <a href="javascript:void(0)" class="back-step step step-2 d-none">
                        <img src="{{ config('cratos.urls.theme') }}images/back-50.png" alt="">
                    </a>
                </h3>
                <p class="text-center step step-2 d-none"><span>{{ t('for_confirmation') }}</span></p>

                <h3 class="text-center step step-3 back-step d-none">
                    <span>{{ t('verify_email_code') }}</span>
                    <a href="javascript:void(0)" class="back-step step step-3 d-none">
                        <img src="{{ config('cratos.urls.theme') }}images/back-50.png" alt="">
                    </a>
                </h3>
                <p class="text-center step step-3 d-none"><span>{{ t('verify_email_address') }}</span></p>

                <h3 class="text-center step back-step step-5 d-none">
                    <span>{{ t('kyc') }}</span>
                </h3>
                <p class="text-center step step-5 d-none"><span>{{ t('fast_checking') }}</span></p>

                <h3 class="text-center step step-6 d-none"><span>{{ t('wallet_checking') }}</span></h3>
                <p class="text-center step step-6 d-none"><span>{{ t('wait_minute') }}</span></p>

                <h3 class="text-center step step-7 wallet-verification-error d-none"><span>{{ t('unverified_wallet') }}</span></h3>
                <p class="text-center step step-7 wallet-verification-error d-none"><span>{{ t('unverified_wallet_description') }}</span></p>

{{--                <h3 class="text-center step step-8 d-none"><span>{{ t('pending_operation') }}</span></h3>--}}
{{--                <p class="text-center step step-8 pay-step d-none"><span>{{ t('continue_description') }}</span></p>--}}
{{--                <p class="text-center step step-8 end-operation d-none"><span>{{ t('pending_operation_description') }}</span></p>--}}

                <h3 class="text-center step back-step step-9 d-none">
                    <a href="javascript:void(0)" class="back-step step step-9 d-none">
                        <img src="{{ config('cratos.urls.theme') }}images/back-50.png" alt="">
                    </a>
                    <span>{{ t('full_name') }}</span>
                </h3>
                <br class="br-hide-step-5 d-block">


                @if($errors->any())
                    <h4>{{ t('withdraw_wire_validation_errors') }}</h4>
                @endif
                @error('error')
                <p class="alert" style="border-color: red; color: red">{{ $message }}</p>
                @enderror

                <br class="br-hide-step-5 d-block">
                <form method="post" action="{{route('create.operation.by.payment.form', ['paymentForm' => $paymentForm])}}" class="form-signin" id="merchantPaymentForm" data-is-client-outside-form="{{ $paymentForm->type == \App\Enums\PaymentFormTypes::TYPE_CLIENT_OUTSIDE_FORM }}">
                    <input id="paymentFormId" type="hidden" name="payment_form_id" value="{{ $paymentForm->id }}">
                    <input type="hidden" id="verifyUrl" value="{{ route('payment.form.verify.url') }}">
{{--                    @csrf--}}
                    <div class="step step-1 d-block">

                        <div class="form-label-group">
                            <label for="youPay">{{ t('you_pay') }}</label>
                            <span id="max-size">
                                {{ t('max_amount', [
                                    'amount' => generalMoneyFormat($maxPaymentAmount, \App\Enums\Currency::CURRENCY_EUR),
                                    'currency' => \App\Enums\Currency::CURRENCY_EUR
                                ])}}
                            </span>
                            <div class="input-group">
                                <input name="paymentFormAmount" id="amount"
                                       type="number"
                                       autocomplete="off"
                                       value="{{ old('paymentFormAmount') }}"
                                       class="form-control amount font-weight-light payment-form-amount"
                                       data-max-amount-eur = "{{ $maxPaymentAmount }}"
                                       data-min-amount-eur = "{{ $minPaymentAmount }}"
                                       data-get-max-amount-url = "{{ route('cabinet.get.rate.maxPaymentAmount') }}"
                                       data-get-min-amount-url = "{{ route('payment.form.get.min.amount', ['paymentForm' => $paymentForm]) }}"
                                       data-get-rate-crypto-url = "{{ route('cabinet.get.rate.crypto') }}"
                                       placeholder="For example: {{ $maxPaymentAmount }}"  min="{{ $minPaymentAmount }}" max="{{ $maxPaymentAmount }}">
                                <div class="input-group-btn">
                                    <select class="form-control payment-form-dropdown" name="currency"
                                            id="fiatCurrency">
                                        @foreach(\App\Enums\Currency::FIAT_CURRENCY_NAMES as $currency)
                                            <option {{ \App\Enums\Currency::CURRENCY_EUR == $currency ? 'selected' : '' }}
                                                value="{{ $currency }}">{{ (\App\Enums\Currency::FIAT_CURRENCY_SYMBOLS[$currency] ?? '') . ' ' . $currency}}</option>
                                        @endforeach
                                    </select>
                                </div>

                            </div> <br>
                            <p data-error-target="paymentFormAmount" class="error-text"></p>
                            <p data-error-target="currency" class="error-text"></p>
                        </div>

                        <br>
                        <div class="form-label-group">
                            <label for="youGet">{{ t('you_get') }}</label>
                            <div class="input-group">
                                <input class="form-control payment-form-amount" id="expectedAmount" name="expectedAmount" type="text" value="{{ old('expectedAmount') }}" disabled>
                                <div class="input-group-btn">
                                    <select class="form-control payment-form-dropdown" name="cryptoCurrency"
                                            id="cryptoCurrency">
                                        @foreach($paymentForm->allowed_crypto_currencies as $currency)
                                            <option {{ \App\Enums\Currency::CURRENCY_BTC == $currency ? 'selected' : '' }}><i class="fab fa-{{strtolower($currency)}}"></i>{{ $currency }}</option>
                                        @endforeach
                                    </select>
                                </div>
                                <p data-error-target="cryptoCurrency" class="error-text"></p>
                                <p data-error-target="expectedAmount" class="error-text"></p>
                            </div>
                        </div> <br>

                        @if($paymentForm->type == \App\Enums\PaymentFormTypes::TYPE_CLIENT_OUTSIDE_FORM)
                            <div class="form-label-group paymentFormToWalletAddressContainer" hidden>
                                <label for="paymentFormToWalletAddress">{{ t('external_wallet') }}</label>
                                <input name="paymentFormToWalletAddress" id="paymentFormToWalletAddress"
                                       autocomplete="off"
                                       data-verify-wallet-url="{{ route('verify.wallet.address.payment.form') }}"
                                       class="form-control amount font-weight-light">
                            </div>
                            <p data-error-target="paymentFormToWalletAddress" class="error-text"></p>
                        @endif

                        @include('cabinet.payment-forms._price-and-fee')

                        <br>
                        <div class="form-label-group mb-4 firstStepBtn">
                            <button class="btn btn-lg btn-primary themeBtn btn-block expectedAmount step-1-btn" data-save-initial-data-url="{{ route('payment.form.save.initial.data', ['paymentForm' => $paymentForm]) }}"
                                    type="button">{{ t('buy') }}</button>
                        </div>
                    </div>

                    <div class="step step-4 d-none">
                        <div class="form-label-group">
                            <div class="row pl-3 pr-3">
                                <div class="col-sm-6">
                                    <label for="input-phone-cc-part"> <span>{{ t('country_code') }}</span> </label>
                                    <div class="form-group col-12 col-sm-12 pl-0 pr-0 pr-sm-2">
                                        <select name="phone_cc_part" id="input-phone-cc-part" class="select-phone-cc form-control" style="width: 100%;">
                                        </select>
                                    </div>
                                </div>
                                <div class="col-sm-6">
                                    <label for="input-phone-no-part">{{ t('number') }}</label>
                                    <input name="phone_no_part" type="text" id="input-phone-no-part" class="form-control col-12 col-sm-12"
                                           data-phone-number-error = "{{ t('error_cratos_no_part') }}"
                                           data-verify-url = "{{ route('verify.phone.number.payment.form') }}"
                                           placeholder=""  value="{{ old('phone_no_part') }}">
                                </div>
                                <p id="error_phone_no_part" data-error-target="phone_cc_part" class="error-text"></p>
                                <p id="error_phone_cc_part" data-error-target="phone_no_part" class="error-text"></p>
                            </div>
                        </div>

                        <div class="form-label-group mb-4 text-center step-4">
                            <button
                                class="btn btn-lg btn-primary themeBtn register-buttons mb-1 verifyPhoneBtn step-4-btn"
                                data-target="#modal-register-sms"
                                type="button" disabled>
                                {{ t('verify') }}
                            </button>
                        </div>


                        <div style="font-size: 10px;text-align: center;margin-top: 18px;">
                            {!! $termsAndConditions !!}
                        </div>
                    </div>

                    <div class="step step-login d-none">
                        <div class="form-label-group">
                            <div class="row pl-3 pr-3">
                                <div class="col-sm-12">
                                    <div class="col-sm-12 text-center">
                                        <div class="input-group">
                                            <input placeholder="{{ t('payment_login_step_password') }}" name="paymentFormUserPassword" id="paymentFormUserPassword" class="form-control" type="password">
                                        </div>
                                    </div>
                                    <p data-error-target="paymentFormUserPassword" data-empty-password-message="{{ t('empty_password_message') }}" class="error-text"></p>
                                </div>
                            </div>
                        </div>

                        <div class="form-label-group mb-4 text-center step-login">
                            <button
                                data-login-user-url="{{ route('payment.form.login.user') }}"
                                class="btn btn-lg btn-primary themeBtn register-buttons mb-1 login step-login-btn"
                                type="button" disabled>
                                {{ t('payment_login_step') }}
                            </button>
                        </div>

                        <div style="font-size: 12px;text-align: center;margin-top: 20px;">
                            {!! $termsAndConditions !!}
                        </div>
                    </div>


                    <div class="step step-2 d-none">
                        <div class="form-label-group">
                            <div class="row pl-3 pr-3">
                                <div class="col-sm-12">
                                    <div class="col-sm-12 text-center">
                                        <label for="paymentFormEmail">{{ t('email') }}</label>
                                        <div class="input-group">
                                            <input name="paymentFormEmail" id="paymentFormEmail" class="form-control" data-error-text="{{ t("error_cratos_email") }}" data-verify-email-url="{{ route('verify.email.payment.form') }}" type="email" value="{{ old('paymentFormEmail') }}">
                                        </div>
                                    </div>
                                    <p id="emailError" data-error-target="paymentFormEmail" class="error-text"></p>
                                </div>
                            </div>
                        </div>

                        <div class="form-label-group mb-4 text-center step-2">
                            <button
                                class="btn btn-lg btn-primary themeBtn register-buttons mb-1 verifyEmail step-2-btn"
                                type="button" disabled>
                                {{ t('verify') }}
                            </button>
                        </div>


                        <div style="font-size: 10px;text-align: center;margin-top: 18px;">
                            {!! $termsAndConditions !!}
                        </div>
                    </div>


                    <div class="step step-3 d-none">
                        <div class="form-label-group">
                            <div class="row pl-3 pr-3">
                                <div class="col-sm-12">
                                    <div class="col-sm-12 text-center">
                                        <div class="input-group">
                                            <div class="codeInputContainerPaymentForm" id="paymentFormEmailCode" data-verify-email-code-url="{{ route('verify.email.code.payment.form') }}">
                                                @for($i = 0; $i < 6; $i++)
                                                    <input class="codeInputPaymentForm @if($i == 5) lastInput @endif" type="number" name="paymentFormEmailCode[]">
                                                @endfor
                                            </div>
                                        </div>
                                    </div>
                                    <p id="emailCodeError" class="error-text"></p>
                                </div>
                            </div>
                            <div class="resendContainer payment-form-bottom-text">
                                <span class="resendTimerContainer">
                                    Code sent. Please check your email. New code can be sent on <a class="resendTimer"></a>s…
                                </span>
                                <div class="resendButtonContainer" style="display: none; cursor: pointer">
                                    <a class="resendButton verifyEmail">Resend code to email</a>
                                </div>
                            </div>
                        </div>

                    </div>

                    <div class="step step-5 d-none">
                        <div id="checkComplianceStatus" class="complianceUrl" data-get-compliance-data-url="{{ route('payment.form.compliance.data') }}" data-verify-compliance-status-url="{{ route('verify.compliance.status.payment.form') }}">
                            <div id="compliance-websdk-container"></div>
                        </div>
                    </div>

                    <div class="step step-6 d-none">
                        <div class="form-label-group ">
                            <div class="row col-md-12" style="margin: 0">
                                <div class="col-sm-12 payment-form-loader-container">
                                    <img class="payment-form-gif" src="{{ config('cratos.urls.theme') }}images/payment-form-loading.gif"  alt="">
                                </div>
                            </div>
                            <div class="payment-form-bottom-text">
                                <span> {{ t('wallet_checking_after_message') }} </span>
                            </div>
                        </div>
                    </div>

                    <div class="step step-7 d-none">
                        <div class="wallet-verification-error form-label-group d-none">
                            <div class="row col-md-12" style="margin: 0">
                                <div class="col-sm-12 payment-form-loader-container">
                                    <img src="{{ config('cratos.urls.theme') }}images/crossmark.webp" width="50%" alt="">
                                </div>
                            </div>

                            <div class="payment-form-bottom-text">
                                <span> {!! t('unverified_wallet_bottom_text', ['createNewWallet' => route('cabinet.login.get')]) !!} </span>
                            </div>

                            <a class="btn btn-lg btn-primary themeBtn col-md-12 back-to-submit-step" style="text-decoration: none"> {{ t('try_another_wallet') }} </a>
                        </div>
                    </div>

                    <div class="step step-8 d-none">
                        <div class="form-label-group">
                            <div class="row col-md-12" style="margin: 0">
                                <div class="col-sm-12 payment-form-loader-container">
                                    <img src="{{ config('cratos.urls.theme') }}images/checkmark.jpg" width="100%" alt="">
                                </div>
                            </div>
                        </div>
                        <button class="btn btn-lg btn-primary themeBtn col-md-12" id="finishOperation" type="sumbit" style="text-decoration: none"> {{ t('pay') }} </button>
                    </div>

                    <div class="step step-9 d-none">

                        <div id="first-name"  class="form-label-group">
                            <label for="first_name">{{ t('first_name') }}</label>
                            <div class="input-group">
                                <input autocomplete="off" name="first_name" class="form-control" minlength="3" maxlength="15">
                                <p id="first_name_error" class="error-text"></p>
                            </div>
                        </div>

                        <div id="last-name" class="form-label-group">
                            <label for="last_name">{{ t('last_name') }}</label>
                            <div class="input-group">
                                <input autocomplete="off" name="last_name" class="form-control">
                                <p id="last_name_error" class="error-text"></p>
                            </div>
                        </div>

                        <div class="form-label-group mb-4 text-center step-login">
                            <button
                                data-login-user-url="{{ route('payment.form.login.user') }}"
                                class="btn btn-lg btn-primary themeBtn register-buttons mb-1 next-step step-login-btn"
                                type="button">
                                {{ t('next_step') }}
                            </button>
                        </div>

                    </div>

                    <div class="form-label-group text-center d-none paymentFormBtn">
                        <button class="btn btn-lg btn-primary themeBtn btn-block expectedAmount" type="button">{{ t('buy') }}</button>
                    </div>

                </form>

                <div class="form-label-group mb-4 text-center d-none paymentFormResetBtn">
                    <button class="btn btn-lg btn-primary mb-1 themeBtn payment-form-reset-button" data-payment-form-reset-url="{{ route('verify.compliance.reset.payment.form') }}" type="button">{{ t('ui_start_over') }}</button>
                </div>

                @include('cabinet.payment-forms._sms-code')


            </div>
            <div class="register-link text-center step step-1">
                <p>{{ t('ui_cprofile_have_account') }} <a href="{{ route('cabinet.login.get') }}">{{ t('please_login') }}</a></p>
            </div>
        </div>
    </div>
    <div class="container storage-error" hidden>
        <a href="/" class="navbar-brand d-block pb-3 text-center">
            <img src="{{ $project->logoPng }}" class="projectLogo" alt="">
        </a>
        <div class="login-form login-form-outer-merchant-payment ml-auto mr-auto">
            <div class="common-form">
                <h4 class="text-center"><span>{{ t('something_went_wrong') }}</span></h4>
                <p class="text-center"><span>{{ t('incognito_tab_error') }}</span></p>
            </div>
        </div>
    </div>
@endsection
@section('scripts')
    <script>
        window.step = 1;
        window.env = '{{ config('app.env')}}';

    </script>
    <script type="module" src='/js/cabinet/payment-form-sms.js'></script>

    <script type="module" src='/js/cabinet/payment-form.js'></script>


    <script src="https://cdn.jsdelivr.net/npm/popper.js@1.16.1/dist/umd/popper.min.js"
            integrity="sha384-9/reFTGAW83EW2RDu2S0VKaIzap3H66lZH81PoYlFhbGU+6BZp6G7niu735Sk7lN"
            crossorigin="anonymous"></script>

    <script src="https://cdn.jsdelivr.net/npm/select2@4.0.13/dist/js/select2.full.min.js"></script>

    <script>
        let API = '{{ config('cratos.urls.cabinet.api-v1') }}';
        let baseFlagUrl = "{{ config('cratos.urls.theme') }}images/flag/";
        var smsRegisterToShow = "{{ $smsRegisterToShow ?? false}}";
        var twoFAToShow = "{{ $twoFAToShow  ?? false}}";
        window.geetestProtocol = '{{ config('geetest.protocol')}}';
        @include('_countries-json')

        setTimeout(function () {
            $('.alert-success').remove();
        }, 2000);
    </script>
    <script src="/js/cabinet/app.js?v={{ time() }}"></script>


    <script src="https://static.sumsub.com/idensic/static/sns-websdk-builder.js"></script>
    <script src="/js/cabinet/compliance.js"></script>

@endsection
