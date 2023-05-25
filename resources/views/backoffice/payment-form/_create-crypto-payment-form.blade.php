<div class="modal fade modal-center" id="paymentCryptoForm" role="dialog">
    <div class="modal-dialog modal-dialog-center">
        <!-- Modal content-->
        <div class="modal-content" style="border:none;padding: 25px;width: 980px;border-radius: 30px;">
            <div class="modal-body">
                <form method="post" name="paymentCryptoForm" data-update-action="{{ route('backoffice.update.payment.crypto.form', ['paymentForm' => '##formId##']) }}" data-create-action="{{ route('backoffice.add.payment.crypto.form') }}">
                    @csrf
                    <input type="hidden" name="paymentFormId">
                    <div style="margin-right: 25px;vertical-align: top;" class="row">
                        <h1 id="cryptoAccountHeader" class="mr-3" data-create-text="{{ t('new_crypto_payment_form') }}" data-update-text="{{ t('update_crypto_payment_form') }}"></h1>
                        <select name="paymentFormStatus" style="width:200px;padding-right: 50px;">
                            @foreach(\App\Enums\PaymentFormStatuses::NAMES as $key => $status)
                                <option value="{{ $key }}">{{ \App\Enums\PaymentFormStatuses::getName($key) }}</option>
                            @endforeach
                        </select>
                        <div class="projectForPaymentForm d-flex align-items-center"
                             data-get-url="{{ route('backoffice.get.payment.form.data.project') }}">
                            <select name="paymentFormProject" id="paymentFormProject"
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
                    </div>

                    <div class="row col-12 mt-5">
                            <div class="col-4">
                                <p class="col-12 activeLink"> {{ t('ui_name') }} </p>
                                <div class="col-12 mt-2">
                                    <input name="paymentFormName"/>
                                </div>
                            </div>
                    </div>

                    <div id="merchantSettings" class="mt-5">
                        <h2>{{ t('merchant_settings') }}</h2>

                        <div class="row col-12">
                            <p class="col-3 activeLink"> {{ t('merchant') }} {{ t('ui_name') }}</p>
                            <p class="col-3 activeLink"> {{ t('website_url') }} </p>
                            <p class="col-3 activeLink"> {{ t('payment_description') }} </p>
                            <p class="col-3 activeLink"> {{ t('merchant_logo') }} </p>
                        </div>

                        <div class="row col-12">
                            <div class="col-3 merchantContainer">
                                <select name="paymentFormMerchant" class="paymentFormMerchantSelect col-12">
                                </select>
                                <div class="liquidityProviders">
                                    <select name="paymentFormLiquidityProvider" style="display: none">
                                    </select>
                                </div>
                                <div class="cardProviders">
                                    <select name="paymentFormCardProvider" style="display: none">
                                    </select>
                                </div>
                            </div>
                            <div class="col-3">
                                <div class="col-12 mt-2">
                                    <input name="paymentFormWebSiteUrl"/>
                                </div>
                            </div>
                            <div class="col-3">
                                <div class="col-12 mt-2">
                                    <input type="text" name="paymentFormDescription">
                                </div>
                            </div>
                            <div class="col-3">
                                <label id="labelFile" for="files" style="border: 1px solid; padding: 6px 37px 3px 37px; border-radius: 10px;"> {{ t('upload') }}</label>
                                <input type="file" id="merchant_logo" style="display: none;" class="hidden" name="paymentFormMerchantLogo"/>
                                <img src="" id="updateMerchantLogo" style="max-width: 240px;max-height: 240px">
                                <p id="paymentFormMerchantLogoStatus" class="text-success"></p>
                            </div>
                        </div>
                    </div>

                    <div id="providers" class="mt-5">
                        <h2>{{ t('ui_providers') }}</h2>
                        <div class="row">
                            <div class="col-md-4">
                                <div class="row col-12">
                                    <p class="col-3 activeLink"> {{ t('wallet_provider') }} </p>
                                </div>

                                <div class="row col-12">
                                    <div class="walletProviders col-3">
                                        <select name="paymentFormWalletProvider" style="min-width: 170px; padding-right: 20px;">
                                        </select>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="row col-12">
                                    <p class="col-5 activeLink"> {{ t('ui_liquidity_provider_fee') }} </p>
                                </div>

                                <div class="row col-12">
                                    <div class="liquidityProviders col-5">
                                        <select name="paymentFormLiquidityProvider" style="min-width: 170px; padding-right: 20px;">
                                        </select>
                                    </div>
                                </div>



                            </div>
                            <div class="col-md-4">
                                <div class="row col-12">
                                    <p class="col-5 activeLink"> {{ t('card_provider') }} </p>
                                </div>

                                <div class="row col-12">
                                    <div class="cardProviders col-5">
                                        <select name="paymentFormCardProvider" style="min-width: 170px; padding-right: 20px;">
                                        </select>
                                    </div>
                                </div>



                            </div>


                        </div>
                    </div>




                    <div id="fees" class="mt-5">
                        <h2>{{ t('fees') }}</h2>

                        <div class="row col-12">
                            <p class="col-5 activeLink"> {{ t('incoming_fees') }} (%)</p>
                        </div>

                        <div class="row col-12">
                            <div class="col-5">
                                <input type="text" name="paymentFormIncomingFee">
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
