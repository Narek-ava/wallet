@extends('cabinet.layouts.payment-form')

@section('content')

    <div class="container main-form" hidden>
        <a href="{{ $paymentForm->website_url }}"
           class="navbar-brand d-block pb-3 text-center col-md-6 ml-auto mr-auto">
            <img src="{{ config('cratos.urls.theme') . 'images/' . $paymentForm->merchant_logo}}" style="max-width: 240px;max-height: 240px"
                 alt="Your logo">
            <p style="color: black; font-size: 15px">{{ $paymentForm->website_url }}</p>
        </a>

        <div class="login-form login-form-outer-merchant-payment ml-auto mr-auto">
            <div class="common-form">
                <h3 class="text-center step step-1 d-block"><span>{{ t('buy_crypto') }}</span></h3>
                <h3 class="text-center step step-2 back-step d-none">
                    <span class="merchant-form-order-summary">{{ t('order_summary') }}</span>
                    <a href="javascript:void(0)" class="back-step step step-2 d-none">
                        <img src="{{ config('cratos.urls.theme') }}images/back-50.png" alt="">
                    </a>
                </h3>

                <h3 class="text-center step step-3 back-step d-none">
                    <span>{{ t('payer_information') }}</span>
                    <a href="javascript:void(0)" class="back-step step step-3 d-none">
                        <img src="{{ config('cratos.urls.theme') }}images/back-50.png" alt="">
                    </a>
                </h3>

                <h3 class="text-center step step-4 back-step d-none">
                    <span>
                        <button class="btn btn-light walletAddressCopy" type="button"
                                onclick="copyText(this)">
                                                <i class="fa fa-copy" aria-hidden="true"></i>
                                            </button>
                        <span class="ml-3 amountAndCurrency text-center"></span>
                    </span>
                    <a href="javascript:void(0)" class="back-step step step-4 d-none">
                        <img src="{{ config('cratos.urls.theme') }}images/back-50.png" alt="">
                    </a>
                </h3>

                @if($errors->any())
                    <h4>{{ t('withdraw_wire_validation_errors') }}</h4>
                @endif
                @error('error')
                <p class="alert" style="border-color: red; color: red">{{ $message }}</p>
                @enderror

                <form method="post"
                      action="{{route('create.operation.by.payment.form', ['paymentForm' => $paymentForm])}}"
                      class="form-signin" id="merchantPaymentCryptoForm"
                      data-is-client-outside-form="{{ $paymentForm->type == \App\Enums\PaymentFormTypes::TYPE_CLIENT_OUTSIDE_FORM }}">
                    <input id="paymentFormId" type="hidden" name="payment_form_id" value="{{ $paymentForm->id }}">
                    {{--                    @csrf--}}
                    <div class="step step-1 d-block">

                        <div class="form-label-group mt-3">
                            <label for="youPay">{{ t('you_pay') }}</label>
                            <span id="min-size-euro">
                                {{ t('min_amount', [
                                    'amount' => generalMoneyFormat($minPaymentAmountInEuro, \App\Enums\Currency::CURRENCY_EUR),
                                    'currency' => \App\Enums\Currency::CURRENCY_EUR
                                ])}}
                            </span>
                            <div class="input-group">
                                <input name="paymentFormAmountInEuro" id="amountInEuro"
                                       type="number"
                                       autocomplete="off"
                                       value="{{ old('paymentFormAmountInEuro') }}" step="1"
                                       class="form-control amount font-weight-light payment-form-amount"
                                       data-get-change-amounts-url="{{ route('payment.form.crypto.get.change.amounts', ['paymentForm' => $paymentForm]) }}"
                                       placeholder="For example: {{ $minPaymentAmountInEuro }}"
                                       min="{{ $minPaymentAmountInEuro }}"
                                />
                            </div>
                            <br>
                            <label for="youPay">{{ t('you_pay') }}</label>
                            <span id="min-size">
                                {{ t('min_amount', [
                                    'amount' => generalMoneyFormat($minPaymentAmount, $cryptoCurrency),
                                    'currency' => $cryptoCurrency
                                ])}}
                            </span>
                            <div class="input-group">
                                <input name="paymentFormAmount" id="amount"
                                       type="number"
                                       autocomplete="off"
                                       value="{{ old('paymentFormAmount') }}" step="1"
                                       class="form-control amount font-weight-light payment-form-amount"
                                       data-get-min-amount-url="{{ route('payment.form.crypto.get.min.amount', ['paymentForm' => $paymentForm]) }}"
                                       placeholder="For example: {{ $minPaymentAmount }}" min="{{ $minPaymentAmount }}">
                                <div class="input-group-btn">
                                    <select class="form-control payment-form-dropdown" name="cryptoCurrency"
                                            id="cryptoCurrency">
                                        @foreach($paymentForm->allowed_crypto_currencies as $currency)
                                            <option {{ \App\Enums\Currency::CURRENCY_BTC == $currency ? 'selected' : '' }}>
                                                <i class="fab fa-{{strtolower($currency)}}"></i>{{ $currency }}</option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>
                            <br>
                            <p data-error-target="cryptoCurrency" class="error-text"></p>
                            <p data-error-target="amount" class="error-text"></p>
                            <p data-error-target="paymentFormAmount" class="error-text"></p>
                            <p data-error-target="currency" class="error-text"></p>
                        </div>

                        @include('cabinet.payment-forms._price-and-fee')

                        <br>
                        <div class="form-label-group mb-4 firstStepBtn text-center">
                            <button class="btn btn-lg btn-primary themeBtn payBtn step-1-btn" disabled
                                    type="button">{{ t('buy') }}</button>
                        </div>
                    </div>

                    <div class="step step-2 d-none">

                        <div class="form-label-group mt-5">
                            <div class="input-group">
                                <strong
                                    class="col-md-4 merchant-payment-form-coll merchant-payment-form-bold">{{ t('details') }}</strong>
                                <span class="col-md-8 summary-details merchant-payment-form-coll"></span>
                            </div>
                            <div class="input-group mt-3">
                                <strong
                                    class="col-md-4 merchant-payment-form-coll merchant-payment-form-bold">{{ t('amount') }}</strong>
                                <span
                                    class="col-md-8 summary-amount merchant-payment-form-coll merchant-payment-form-light"></span>
                            </div>
                            <div class="input-group mt-3">
                                <strong
                                    class="col-md-4 merchant-payment-form-coll merchant-payment-form-bold">{{ t('fee') }}</strong>
                                <span
                                    class="col-md-8 summary-fee merchant-payment-form-coll merchant-payment-form-light"></span>
                            </div>
                            <div class="input-group mt-3">
                                <strong
                                    class="col-md-4 merchant-payment-form-coll merchant-payment-form-bold">{{ t('ui_network_cost') }}</strong>
                                <span
                                    class="col-md-8 merchant-payment-form-coll merchant-payment-form-light">{{ t('summary_network_cost') }}</span>
                                <span class="col-md-4 merchant-payment-form-coll"></span>
                                <small class="col-md-8 merchant-payment-form-coll merchant-payment-form-light"
                                       style="color: #94e240; font-size: 10px">{{ t('summary_network_cost_description') }}</small>
                            </div>
                            <br><br>
                            <div class="input-group">
                                <strong class="col-md-4 merchant-payment-form-coll merchant-payment-form-bold"
                                        style="font-size: 24px;">{{ t('ui_total') }}</strong>
                                <span
                                    class="col-md-8 summary-total merchant-payment-form-coll merchant-payment-form-light"></span>
                            </div>
                        </div>

                        <div class="form-label-group mt-3 mb-4 text-center">
                            <button class="btn btn-lg btn-primary themeBtn payBtn step-2-btn"
                                    data-save-initial-data-url="{{ route('payment.form.crypto.save.initial.data', ['paymentForm' => $paymentForm]) }}"
                                    type="button">{{ t('pay') }}</button>
                        </div>
                        <div class="input-group mt-3 text-center">
                            <small>{!! t('terms_and_conditions_for_payment_form') !!}</small>
                        </div>

                    </div>

                    <div class="step step-3 d-none">
                        <div class="form-label-group">

                            <div class="col-sm-12">
                                <label for="firstName">{{ t('first_name') }}</label>
                                <div class="input-group">
                                    <input name="paymentFormFirstName" id="firstName"
                                           class="form-control font-weight-light" required min="3" max="50"
                                           value="{{ old('paymentFormFirstName') }}">
                                </div>
                            </div>

                            <p id="firstNameError" data-error-target="first_name"
                               data-error-text="{{ t('error_cratos_first_name') }}" class="error-text"></p>

                            <div class="col-sm-12">
                                <label for="lastName">{{ t('last_name') }}</label>
                                <div class="input-group">
                                    <input name="paymentFormLastName" id="lastName"
                                           class="form-control font-weight-light" required min="3" max="50"
                                           value="{{ old('paymentFormLastName') }}">
                                </div>
                            </div>

                            <p id="lastNameError" data-error-target="last_name" class="error-text"
                               data-error-text="{{ t('error_cratos_last_name') }}"></p>

                            <div class="col-sm-12">
                                <label for="email">{{ t('email') }}</label>
                                <div class="input-group">
                                    <input name="paymentFormEmail" id="email" class="form-control font-weight-light"
                                           type="email" required value="{{ old('paymentFormEmail') }}">
                                </div>
                            </div>

                            <p id="emailError" data-error-target="email" class="error-text"
                               data-error-text="{{ t('error_cratos_email') }}"></p>

                            <div class="row pl-3 pr-3">
                                <div class="col-sm-6">
                                    <label for="input-phone-cc-part"> <span>{{ t('country_code') }}</span> </label>
                                    <div class="form-group col-12 col-sm-12 pl-0 pr-0 pr-sm-2">
                                        <select name="phone_cc_part" id="input-phone-cc-part"
                                                class="select-phone-cc form-control" style="width: 100%;">
                                        </select>
                                    </div>
                                </div>
                                <div class="col-sm-6">
                                    <label for="input-phone-no-part">{{ t('number') }}</label>
                                    <input name="phone_no_part" type="text" id="input-phone-no-part"
                                           class="form-control col-12 col-sm-12"
                                           placeholder="" value="{{ old('phone_no_part') }}">
                                </div>
                                <p data-error-target="phone_cc_part" class="error-text"></p>
                                <p id="phoneNoPartError" data-error-target="phone_no_part" class="error-text"
                                   data-error-text="{{ t('error_cratos_no_part') }}"></p>
                            </div>
                        </div>
                        <div class="form-label-group mb-4 text-center step-3">
                            <button
                                disabled
                                class="btn btn-lg btn-primary themeBtn mb-1 savePayerData"
                                data-save-payer-data-url="{{ route('save.payer.data.payment.form.crypto') }}"
                                type="button">
                                {{ t('verify') }}
                            </button>
                        </div>
                    </div>

                    <div class="step step-4 d-none" style="overflow: hidden">
                        <div class="form-label-group">
                            <div class="row pl-3 pr-3">
                                <div class="col-sm-12">
                                    <div class="col-sm-12 text-center">
                                        <div class="mb-3 d-sm-flex">
                                            <input type="text" class="walletAddress" value=""
                                                   style="position: absolute; left: 90000px">
                                        </div>
                                        <div class="d-flex flex-column align-items-center mb-4">
                                            <span class="cryptocurrencyImage"></span>
                                            <img height="200px" class="mt-1 qrCode" src="" alt="">
                                        </div>
                                        <p>
                                            <small>After we see the payment on the blockchain, you will receive an
                                                additional email notification with the status and transaction
                                                ID.</small>
                                        </p>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </form>
            </div>
            <div style="font-size: 10px;text-align: center;margin-top: 18px;">
                Powered by
                <p>
                    <a href="/" class="navbar-brand d-block pb-3 text-center">
                        <img src="{{ $currentProject->logoPng }}" class="projectLogo" alt="">
                    </a>
                </p>
            </div>

        </div>
    </div>
    <div class="container storage-error" hidden>
        <a href="/" class="navbar-brand d-block pb-3 text-center">
            <img src="{{ $currentProject->logoPng }}" class="projectLogo" alt="">
        </a>
        <div class="login-form login-form-outer-merchant-payment ml-auto mr-auto">
            <div class="common-form">
                <h4 class="text-center"><span>{{ t('something_went_wrong') }}</span></h4>
                <p class="text-center"><span>{{ t('incognito_tab_error') }}</span></p>
            </div>
        </div>
    </div>
    <input type="hidden" id="checkPaymentUrl" value="{{ route('check.crypto.to.crypto.pf.payment') }}">
@endsection
@section('scripts')
    <script>
        window.step = 1;
        window.env = '{{ config('app.env')}}';

    </script>
    <script type="module" src='/js/cabinet/payment-form-sms.js'></script>

    <script type="module" src='/js/cabinet/payment-form-crypto.js'></script>

    <script src='/js/cabinet/common.js'></script>


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
        {{--var cryptoPaymentSmsCode = "{{ route('verify.sms.code.payment.form.crypto') }}";--}}
        @include('_countries-json')

        setTimeout(function () {
            $('.alert-success').remove();
        }, 2000);
    </script>
    <script src="/js/cabinet/app.js?v={{ time() }}"></script>
    <script src="https://static.sumsub.com/idensic/static/sns-websdk-builder.js"></script>
    <script src="/js/cabinet/compliance.js"></script>

@endsection
