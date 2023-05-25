<div class="modal fade modal-center" id="paymentForm" role="dialog">
    <div class="modal-dialog modal-dialog-center">
        <!-- Modal content-->
        <div class="modal-content" style="border:none;padding: 25px;width: 900px;border-radius: 30px;">
            <div class="modal-body">
                <form method="post" name="paymentForm" data-update-action="{{ route('backoffice.update.payment.form', ['paymentForm' => '##formId##']) }}" data-create-action="{{ route('backoffice.add.payment.form') }}">
                    @csrf
                    <input type="hidden" name="paymentFormId">
                    <div style="margin-right: 25px;vertical-align: top;" class="row">
                        <h1 id="accountHeader" class="mr-3" data-create-text="{{ t('new_payment_form') }}" data-update-text="{{ t('update_payment_form') }}"></h1>
                        <select name="paymentFormStatus" style="width:200px;padding-right: 50px;">
                            @foreach(\App\Enums\PaymentFormStatuses::NAMES as $key => $status)
                                <option value="{{ $key }}">{{ \App\Enums\PaymentFormStatuses::getName($key) }}</option>
                            @endforeach
                        </select>
                        @error('paymentFormStatus')
                            <p class="text-danger">{{ $message }}</p>
                        @enderror
                        <div class="projectForPaymentForm d-flex align-items-center"
                             data-get-url="{{ route('backoffice.get.payment.form.data.project') }}">
                            <select name="paymentFormProject" id="paymentFormProject" class="projectSelect"
                                    data-permission="{{ \App\Enums\BUserPermissions::ADD_AND_EDIT_PAYMENT_FORMS }}"
                                    style="width:200px;padding-right: 50px; margin-left: 10px">
                                <option value="">Select project</option>
                                @foreach($activeProjects as $key => $name)
                                    <option
                                        value="{{ $key }}">{{ $name }}</option>
                                @endforeach
                            </select>
                            @error('paymentFormStatus')
                            <p class="text-danger">{{ $message }}</p>
                            @enderror
                        </div>
                        <div class="error text-danger projectSelectError"></div>
                    </div>
                    <div class="row col-12 mt-5">
                            <div class="col-4">
                                <p class="col-12 activeLink"> {{ t('ui_name') }} </p>
                                <div class="col-12 mt-2">
                                    <input name="paymentFormName"/>
                                </div>
                                @error('paymentFormName')
                                <p class="text-danger">{{ $message }}</p>
                                @enderror
                            </div>
                    </div>

                    <div id="merchantSettings" class="mt-5">
                        <h2>{{ t('merchant_settings') }}</h2>

                        <div class="row col-12">
                            <p class="col-4 activeLink merchantRateContainerTitle"> {{ t('merchant') }} </p>
                            <p class="col-4 activeLink clientRateShowHide" style="display: none"> {{ t('rate_template_for_customer') }} </p>
                            <p class="col-4 activeLink"> {{ t('type') }} </p>
                        </div>

                        <div class="row col-12">
                            <div class="col-4 merchantContainer">
                                <select name="paymentFormMerchant" class="paymentFormMerchantSelect col-12">
                                    <option value=""></option>
                                </select>
                            </div>
                            <div class="col-4 rateContainer" style="display: none">
                                <select name="paymentFormRate" class="paymentFormRateSelect col-12">
                                    <option value=""></option>
                                </select>
                            </div>

                            <div class="col-4 merchantTypeContainer" data-merchant-outside-form="{{ \App\Enums\PaymentFormTypes::TYPE_MERCHANT_OUTSIDE_FORM }}"
                                 data-client-outside-form="{{ \App\Enums\PaymentFormTypes::TYPE_CLIENT_OUTSIDE_FORM }}" data-client-inside-form="{{ \App\Enums\PaymentFormTypes::TYPE_CLIENT_INSIDE_FORM }}">
                            </div>
                        </div>
                        <div class="row col-12 mt-5" id="walletAddressContainer">
                            @foreach(\App\Enums\Currency::getList() as $currency)
                                <div class="col-4">
                                    <p class="col-12 activeLink"> {{ $currency . ' ' .t('address') }} </p>
                                    <div class="col-12 mt-2">
                                        <input name="address_{{$currency}}"/>
                                    </div>
                                    @error("address_" . $currency)
                                        <p class="text-danger">{{ $message }}</p>
                                    @enderror
                                </div>
                            @endforeach
                        </div>

                    </div>

                    <div id="providers" class="mt-5">
                        <h2>{{ t('ui_providers') }}</h2>

                        <div class="row col-12">
                            <p class="col-3 activeLink"> {{ t('card_provider') }} </p>
                            <p class="col-3 activeLink"> {{ t('liquidity_provider') }} </p>
                            <p class="col-3 activeLink"> {{ t('wallet_provider') }} </p>
                            <p class="col-3 activeLink kycContainer"> {{ t('kyc') }} </p>
                        </div>

                        <div class="row col-12">
                            <div class="col-3">
                                <div class="cardProviders">

                                <select name="paymentFormCardProvider" style="min-width: 170px; padding-right: 20px;">
                                </select>
                                </div>

                                @error('paymentFormCardProvider')
                                <p class="text-danger">{{ $message }}</p>
                                @enderror
                            </div>
                            <div class="col-3">
                                <div class="liquidityProviders">
                                <select name="paymentFormLiquidityProvider" style="min-width: 170px; padding-right: 20px;">
                                </select>
                                </div>
                                @error('paymentFormLiquidityProvider')
                                <p class="text-danger">{{ $message }}</p>
                                @enderror
                            </div>
                            <div class="col-3">
                                <div class="walletProviders">
                                <select name="paymentFormWalletProvider" id="newPaymentFormWalletProvider"
                                        style="min-width: 170px; padding-right: 20px;">
                                </select>
                                </div>
                                @error('paymentFormWalletProvider')
                                <p class="text-danger">{{ $message }}</p>
                                @enderror
                            </div>
                            <div class="col-3 kycContainer" id="paymentFormKYCContainer"
                                 data-kyc="{{ \App\Models\PaymentForm::KYC }}"
                                 data-kyc-text="{{ t(\App\Models\PaymentForm::KYC_VARIANTS[\App\Models\PaymentForm::KYC]) }}">
                                <select name="paymentFormKYC" id="paymentFormKYC" style="min-width: 170px; padding-right: 20px;">
                                    @foreach(\App\Models\PaymentForm::KYC_VARIANTS as $key => $kycVariant)
                                        <option value="{{ $key }}">{{ t($kycVariant) }}</option>
                                    @endforeach
                                </select>
                                @error('paymentFormKYC')
                                <p class="text-danger">{{ $message }}</p>
                                @enderror
                            </div>
                        </div>
                    </div>
                    <div class="mt-5 ml-1">
                        <button class="btn btn-lg btn-primary themeBtn register-buttons mb-4 mb-md-0 mr-3"
                                type="submit"> {{ t('save') }} </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
