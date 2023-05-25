<div id="bankSettings" class="container-fluid tab-pane fade pl-0 mt-5"><br>
        <div class="col-md-12 pl-0">
            <h2 style="display: inline;margin-right: 25px;">{{ t('bank_details') }}</h2>
            @if($currentAdmin->isAllowed([\App\Enums\BUserPermissions::ADD_EDIT_BANK_DETAILS_FOR_CLIENT], $profile->cUser->project_id))
                <button class="btn" style="border-radius: 25px;background-color: #000;color: #fff" data-toggle="modal"
                        id="addBankDetail" data-target="#bankDetail"> {{ t('add') }} </button>
            @endif
        </div>

        <div class="col-md-12 pl-0">
            <div class="row">
                @if($accounts->count())
                    @foreach($accounts as $account)
                        @php
                            $address = '';
                            if ($account->account_type == \App\Enums\AccountType::TYPE_WIRE_SWIFT) {
                                $address = $account->wire->swift;
                            } elseif ($account->account_type == \App\Enums\AccountType::TYPE_WIRE_SEPA) {
                                $address = $account->wire->iban;
                            }
                        @endphp
                        <div class="col-md-3 d-flex">
                            <div data-account-id="{{ $account->id }}" class="bankDetailBlockBorderInactive bankDetailBlock w-100">
                                <h5 class="textBold breakWord">{{ \App\Enums\AccountType::getName($account->account_type) }} {{ $account->name }}</h5>
                                <p class="breakWord">{{ $address }} <br>
                                    {{ $account->wire->account_beneficiary }}, {{ $account->wire->bank_name }}</p>
                                    <p class="date-styles"></p>
                            </div>
                        </div>
                    @endforeach
                @else
                    <h6 class="mt-3 pl-3">{{ t('backoffice_have_not_bank_template_yet') }}</h6>
                @endif
            </div>
        </div>
        @include('backoffice.partials.session-message')
        <div class="col-md-12 pl-0 mt-5">
            <h2 style="display: inline;margin-right: 25px;">{{ t('crypto_wallets') }}</h2>
            @if($currentAdmin->isAllowed([\App\Enums\BUserPermissions::ADD_EDIT_BANK_DETAILS_FOR_CLIENT], $profile->cUser->project_id))
                <button class="btn" style="border-radius: 25px;background-color: #000;color: #fff" data-toggle="modal"
                        id="addCryptoDetail" data-target="#cryptoDetail">{{ t('add') }}</button>
            @endif
        </div>

        <div class="col-md-12 pl-0">
            <div class="row" id="walletsSection">
                @if($accountsCrypto->count())
                    @foreach($accountsCrypto as $cryptoAccount)
                        <div class="col-md-3 d-flex">
                            <div class="w-100 crypto-wallets-style">
                                <div style="display: inline-block;width: 100%;">
                                    <div style="width: 49%;float: left">
                                        <h5 class="textBold breakWord">{{ $cryptoAccount->currency }}</h5>
                                    </div>
                                    <div style="width: 49%;float: left;font-weight: bold;color: green;text-align: right">{{ $cryptoAccount->cryptoAccountDetail->risk_score * 100 }} %</div>
                                </div>
                                <div style="position: absolute;font-size: 15px;top:30px;right:30px;cursor:pointer;" class="walletItem" data-account-id="{{ $cryptoAccount->id }}">X</div>
                                <p style="font-size: 11px" class="textBold breakWord">{{ $cryptoAccount->cryptoAccountDetail->address }}</p>
                                <p class="date-styles">
                                    <span>{{ t('ui_cprofile_verified') }}: {{ Carbon\Carbon::createFromFormat('Y-m-d H:i:s', $cryptoAccount->cryptoAccountDetail->verified_at)->format('Y.m.d H:i') }}</span>
                                    <span class="flr">{{ t('ui_expiry') }}: {{ 30 - \Carbon\Carbon::now()->diffInDays($cryptoAccount->cryptoAccountDetail->verified_at) }} days</span>
                                </p>
                            </div>
                        </div>
                    @endforeach
                @else
                    <h6 class="mt-3 pl-3">{{ t('backoffice_have_not_crypto_wallets_yet') }}</h6>
                @endif
            </div>
        </div>

        <div class="modal fade bd-example-modal-sm" id="bankDetailUpdateModal" tabindex="-1" aria-labelledby="bankDetailUpdateModalLabel" aria-hidden="true" >
            <div class="modal-dialog modal-dialog-centered">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title">{{ t('bank_details') }}</h5>
                        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                            <span aria-hidden="true">×</span>
                        </button>
                    </div>
                    <form
                        @if($currentAdmin->isAllowed([\App\Enums\BUserPermissions::ADD_EDIT_BANK_DETAILS_FOR_CLIENT], $profile->cUser->project_id))
                                                      action="{{ route('backoffice.bank.details.update') }}" method="post"
                        @endif
                        name="bankDetailCreate" id="bankDetailCreate">
                        @csrf
                        <input type="hidden" name="u_account_id" value="{{ old('u_account_id') }}">
                        <input type="hidden" name="_method" value="put">
                        <input type="hidden" value="{{ $profile->id }}" name="c_profile_id">

                        <div class="modal-body">
                            <div class="row align-items-end">
                                <div class="col-md-3">
                                    <h5 class="mb-4 textBold">{{ t('template_name') }}</h5>
                                    <input disabled class="disabledField" type="text" id="updateTemplateName" name="u_template_name" value="{{ old('u_template_name') }}">
                                    @error('u_template_name')<p class="text-danger">{{ $message }}</p>@enderror
                                </div>
                                <div class="col-md-4">
                                    <h5 class="mb-4 textBold">{{ t('wire_transfer_country_of_your_bank') }}</h5>
                                    <select disabled name="u_country" id="updateCountry" class="disabledField bank-detail-select-style">
                                        @foreach(\App\Models\Country::getCountries(false) as $countryCode => $country)
                                            <option value="{{ $countryCode }}" >{{ $country }}</option>
                                        @endforeach
                                        @error('u_country')<p class="text-danger">{{ $message }}</p>@enderror
                                    </select>
                                </div>
                                <div class="col-md-2">
                                    <h5 class="mb-4 textBold">{{ t('currency') }}</h5>
                                    <select disabled name="u_currency" id="updateCurrency" class="disabledField backoffice-bank-detail-select-style">
                                        @foreach(\App\Enums\Currency::FIAT_CURRENCY_NAMES as $currency)
                                            <option value="{{ $currency }}">{{ $currency }}</option>
                                        @endforeach
                                        @error('u_currency')<p class="text-danger">{{ $message }}</p>@enderror
                                    </select>
                                </div>
                                <div class="col-md-2">
                                    <h5 class="mb-4 textBold">{{ t('type') }}</h5>
                                    <select disabled name="u_type" id="updateType" class="disabledField backoffice-bank-detail-select-style">
                                        @foreach(\App\Enums\AccountType::ACCOUNT_WIRE_TYPES as $key => $type)
                                            <option value="{{ $key }}">{{ $type }}</option>
                                        @endforeach
                                        @error('u_type')<p class="text-danger">{{ $message }}</p>@enderror
                                    </select>
                                </div>
                                <div class="col-md-5 mt-5">
                                    <h5 class="mb-4 textBold">{{ t('ui_cabinet_bank_details_applicable_iban') }}</h5>
                                    <input disabled class="disabledField" type="text" name="u_iban" id="updateIban" value="{{ old('u_iban') }}">
                                    @error('u_iban')<p class="text-danger">{{ $message }}</p>@enderror
                                </div>
                                <div class="col-md-5 mt-5">
                                    <h5 class="mb-4 textBold">SWIFT/BIC</h5>
                                    <input disabled class="disabledField" type="text" name="u_swift" id="updateSwift" value="{{ old('u_swift') }}">
                                    @error('u_swift')<p class="text-danger">{{ $message }}</p>@enderror
                                </div>
                                <div class="col-md-3 mt-5">
                                    <h5 class="mb-4 textBold">{{ t('withdraw_wire_account_holder') }}</h5>
                                    <input disabled class="disabledField" type="text" name="u_account_holder" id="updateAccountHolder" value="{{ old('u_account_holder') }}">
                                    @error('u_account_holder')<p class="text-danger">{{ $message }}</p>@enderror
                                </div>
                                <div class="col-md-3 mt-5">
                                    <h5 class="mb-4 textBold">{{ t('withdraw_wire_account_number') }}</h5>
                                    <input disabled class="disabledField" type="text" name="u_account_number" id="updateAccountNumber" value="{{ old('u_account_number') }}">
                                    @error('u_account_number')<p class="text-danger">{{ $message }}</p>@enderror
                                </div>
                                <div class="col-md-3 mt-5">
                                    <h5 class="mb-4 textBold">{{ t('withdraw_wire_bank_name') }}</h5>
                                    <input disabled class="disabledField" type="text" name="u_bank_name" id="updateBankName" value="{{ old('u_bank_name') }}">
                                    @error('u_bank_name')<p class="text-danger">{{ $message }}</p>@enderror
                                </div>
                                <div class="col-md-9 mt-5">
                                    <h5 class="mb-4 textBold">{{ t('withdraw_wire_bank_address') }}</h5>
                                    <input disabled class="disabledField" type="text" name="u_bank_address" id="updateBankAddress" value="{{ old('u_bank_address') }}">
                                    @error('u_bank_address')<p class="text-danger">{{ $message }}</p>@enderror
                                </div>

                                <div class="col-md-5 mt-5 u_correspondent_bank_details">
                                    <h5 class="mb-4 textBold">{{ t('ui_cabinet_correspondent_bank') }}</h5>
                                    <input disabled id="updateCorrespondentBank" type="text" class="disabledField backoffice-bank-detail-select-style"
                                           name="u_correspondent_bank" value="{{ old('u_correspondent_bank') }}">
                                    @error('u_correspondent_bank')<p class="text-danger">{{ $message }}</p>@enderror
                                </div>

                                <div class="col-md-5 mt-5 u_correspondent_bank_details">
                                    <h5 class="mb-4 textBold">{{ t('ui_cabinet_correspondent_bank_swift') }}</h5>
                                        <input disabled id="updateCorrespondentBankSwift" type="text" class="disabledField backoffice-bank-detail-select-style" name="u_correspondent_bank_swift" value="{{ old('u_correspondent_bank_swift') }}">
                                    @error('u_correspondent_bank_swift')<p class="text-danger">{{ $message }}</p>@enderror
                                </div>

                                <div class="col-md-5 mt-5 u_intermediary_bank_details">
                                    <h5 class="mb-4 textBold">{{ t('ui_cabinet_intermediary_bank') }}</h5>
                                     <input disabled id="updateIntermediaryBank" type="text" class="disabledField backoffice-bank-detail-select-style"
                                            name="u_intermediary_bank" value="{{ old('u_intermediary_bank') }}">
                                    @error('u_intermediary_bank')<p class="text-danger">{{ $message }}</p>@enderror
                                </div>

                                <div class="col-md-5 mt-5 u_intermediary_bank_details">
                                    <h5 class="mb-4 textBold">{{ t('ui_cabinet_intermediary_bank_swift') }}</h5>
                                    <input disabled id="updateIntermediaryBankSwift" type="text" class="disabledField backoffice-bank-detail-select-style"
                                           name="u_intermediary_bank_swift" value="{{ old('u_intermediary_bank_swift') }}"/>
                                    @error('u_intermediary_bank_swift')<p class="text-danger">{{ $message }}</p>@enderror
                                </div>
                            </div>
                            <div class="dates-container"></div>
                        </div>
                        <div class="modal-footer">
                            @if($currentAdmin->isAllowed([\App\Enums\BUserPermissions::ADD_EDIT_BANK_DETAILS_FOR_CLIENT], $profile->cUser->project_id))
                                <button class="btn themeBtn" type="button"
                                        id="changeButton">{{ t('ui_change') }}</button>
                                <button class="btn themeBtn d-none" type="submit"
                                        id="saveButton">{{ t('save') }}</button>
                                <a data-toggle="modal" id="addBankDetailDelete" data-target="#bankDetailDelete"
                                   style="cursor: pointer"
                                   class="delete-template-styles">{{ t('ui_delete_bank_template') }}</a>
                            @endif
                        </div>
                    </form>
                </div>
            </div>
        </div>

        @if($uAccount && ($currentAdmin->isAllowed([\App\Enums\BUserPermissions::ADD_EDIT_BANK_DETAILS_FOR_CLIENT], $profile->cUser->project_id)))
            <div class="modal fade bd-example-modal-sm" id="bankDetailDelete" tabindex="-1" aria-labelledby="bankDetailDeleteLabel" style="display:none;padding-top: 20%;align-items: center;width: 100%;height: 100%;" aria-hidden="true" >
                <div class="modal-dialog modal-sm">
                    <div class="modal-content" style="border-radius: 31px; padding: 17px">
                        <button style="margin-left: auto;margin-right: 0;" type="button" class="close" data-dismiss="modal" aria-label="Close">
                            <span aria-hidden="true">×</span>
                        </button>
                        <div class="modal-body" style="margin: 0 auto">
                            <h1>{{ t('delete_template') }}</h1>
                            <p>{{ t('delete_template_text') }}
                            <form action="{{ route('backoffice.bank.details.delete') }}" method="post">
                                @csrf
                                <input type="hidden" value="{{ $profile->id }}" name="c_profile_id">
                                <input id="deleteBankAccountId" type="hidden" name="account_id" value="{{ $uAccount->id }}">
                                <button type="submit" class="btn" style="margin-left: auto;border-radius: 25px;background-color: #000;color: #fff">{{ t('confirm') }}</button>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        @endif


        <div class="modal fade " id="bankDetail" tabindex="-1" aria-labelledby="bankDetailLabel" style="display:none" aria-hidden="true" >
            <div class="modal-dialog">
                <div class="modal-content" style="border-radius: 31px; padding: 17px">
                    <div class="modal-header">
                        <h4 class="modal-title" id="exampleModalLabel">{{ t('add_bank_details') }}</h4>
                        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                            <span aria-hidden="true">×</span>
                        </button>
                    </div>
                    <div class="modal-body">
                        <form
                            @if($currentAdmin->isAllowed([\App\Enums\BUserPermissions::ADD_EDIT_BANK_DETAILS_FOR_CLIENT], $profile->cUser->project_id))
                            action="{{ route('backoffice.bank.details.store') }}" method="post"
                            @endif
                            name="bankDetailUpdate" id="bankDetailUpdate">
                            {{ csrf_field() }}
                            <input type="hidden" value="{{ $profile->id }}" name="c_profile_id">
                            <input type="hidden" class="type_swift" value="{{ \App\Enums\TemplateType::TEMPLATE_TYPE_SWIFT }}" />
                            <div class="row">
                                <div class="col-md-3">
                                    <div class="form-group">
                                        <label for="templateName" class="font-weight-bold">{{ t('template_name') }}</label>
                                        <input id="templateName" class="form-control" name="template_name" value="{{ old('template_name') }}">
                                        @error('template_name')<p class="text-danger">{{ $message }}</p>@enderror
                                    </div>
                                </div>
                                <div class="col-md-3">
                                    <div class="form-group">
                                        <label for="country" class="font-weight-bold">{{ t('country_of_clients_bank') }}</label>
                                        <select id="country" class="form-control grey-rounded-border" name="country">
                                            <option hidden="" value="">{{ t('select_option') }}</option>
                                            @foreach(\App\Models\Country::getCountries(false) as $countryCode => $country)
                                                <option value="{{ $countryCode }}" @if(old('country') == $countryCode) selected @endif>{{ $country }}</option>
                                            @endforeach
                                        </select>
                                        @error('country')<p class="text-danger">{{ $message }}</p>@enderror
                                    </div>
                                </div>
                                <div class="col-md-2">
                                    <div class="form-group">
                                        <label for="currency" class="font-weight-bold">{{ t('currency') }}</label>
                                        <select id="currency" class="form-control grey-rounded-border" name="currency">
                                            <option hidden="" value="">{{ t('select_option') }}</option>
                                            @foreach(\App\Enums\Currency::FIAT_CURRENCY_NAMES as $currency)
                                                <option value="{{ $currency }}" @if(old('currency') == $currency) selected @endif>{{ $currency }}</option>
                                            @endforeach
                                        </select>
                                        @error('currency')<p class="text-danger">{{ $message }}</p>@enderror
                                    </div>
                                </div>
                                <div class="col-md-2">
                                    <div class="form-group">
                                        <label for="type" class="font-weight-bold">{{ t('method') }}</label>
                                        <select id="type" class="form-control grey-rounded-border" name="type">
                                            <option hidden="" value="">{{ t('select_option') }}</option>
                                            @foreach(\App\Enums\AccountType::ACCOUNT_WIRE_TYPES as $key => $type)
                                                <option value="{{ $key }}" @if(($errors->any() && (int)old('type') == (int)$key)) selected @endif>{{ $type }}</option>
                                            @endforeach
                                        </select>
                                        @error('type')<p class="text-danger">{{ $message }}</p>@enderror
                                    </div>
                                </div>
                            </div>
                            <div class="row">
                                <div class="col-md-5">
                                    <div class="form-group">
                                        <label for="iban" class="font-weight-bold">{{ t('ui_cabinet_bank_details_applicable_iban') }}</label>
                                        <input id="iban" type="text" class="form-control" name="iban" value="{{ old('iban') }}">
                                        @error('iban')<p class="text-danger">{{ $message }}</p>@enderror
                                    </div>
                                </div>
                                <div class="col-md-5">
                                    <div class="form-group">
                                        <label for="swift" class="font-weight-bold">{{ t('ui_cabinet_bank_details_swift_bic') }}</label>
                                        <input id="swift" type="text" class="form-control" name="swift" value="{{ old('swift') }}">
                                        @error('swift')<p class="text-danger">{{ $message }}</p>@enderror
                                    </div>
                                </div>
                            </div>
                            <div class="row ">
                                <div class="col-md-3">
                                    <div class="form-group">
                                        <label for="accountHolder" class="font-weight-bold">{{ t('ui_cabinet_bank_details_account_holder_placeholder') }}</label>
                                        <input id="accountHolder" type="text" class="form-control" name="account_holder" value="{{ old('account_holder') }}">
                                        @error('account_holder')<p class="text-danger">{{ $message }}</p>@enderror
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="form-group">
                                        <label for="accountNumber" class="font-weight-bold">{{ t('ui_cabinet_bank_details_account_number_placeholder') }}</label>
                                        <input id="accountNumber" type="text" class="form-control" name="account_number" value="{{ old('account_number') }}">
                                        @error('account_number')<p class="text-danger">{{ $message }}</p>@enderror
                                    </div>
                                </div>
                                <div class="col-md-3">
                                    <div class="form-group">
                                        <label for="bankName" class="font-weight-bold">{{ t('ui_cabinet_bank_details_bank_name_placeholder') }}</label>
                                        <input id="bankName" type="text" class="form-control" name="bank_name" value="{{ old('bank_name') }}">
                                        @error('bank_name')<p class="text-danger">{{ $message }}</p>@enderror
                                    </div>
                                </div>
                            </div>
                            <div class="row">
                                <div class="col-md-10">
                                    <div class="form-group">
                                        <label for="bankAddress" class="font-weight-bold">{{ t('ui_cabinet_bank_details_bank_address_placeholder') }}</label>
                                        <input id="bankAddress" type="text" class="form-control" name="bank_address" value="{{ old('bank_address') }}">
                                        @error('bank_address')<p class="text-danger">{{ $message }}</p>@enderror
                                    </div>
                                </div>
                            </div>
                            <div class="row correspondent_bank_details" hidden>
                                <div class="col-md-5">
                                    <div class="form-group">
                                        <label for="correspondent_bank" class="font-weight-bold"> {{ t('ui_cabinet_correspondent_bank') }}</label>
                                        <input id="correspondent_bank" type="text" class="form-control" name="correspondent_bank" value="">
                                    </div>
                                </div>
                                <div class="col-md-5">
                                    <div class="form-group">
                                        <label for="correspondent_bank_swift" class="font-weight-bold"> {{ t('ui_cabinet_correspondent_bank_swift') }} </label>
                                        <input id="correspondent_bank_swift" type="text" class="form-control" name="correspondent_bank_swift" value="">
                                    </div>
                                </div>
                            </div>
                            <div class="row intermediary_bank_details" hidden>
                                <div class="col-md-5">
                                    <div class="form-group">
                                        <label for="intermediary_bank" class="font-weight-bold"> {{ t('ui_cabinet_intermediary_bank') }}</label>
                                        <input id="intermediary_bank" type="text" class="form-control" name="intermediary_bank" value="">
                                    </div>
                                </div>
                                <div class="col-md-5">
                                    <div class="form-group">
                                        <label for="intermediary_bank_swift" class="font-weight-bold"> {{ t('ui_cabinet_intermediary_bank_swift') }} </label>
                                        <input id="intermediary_bank_swift" type="text" class="form-control" name="intermediary_bank_swift" value="">
                                    </div>
                                </div>
                            </div>
                            @if($currentAdmin->isAllowed([\App\Enums\BUserPermissions::ADD_EDIT_BANK_DETAILS_FOR_CLIENT], $profile->cUser->project_id))
                                <button class="btn themeBtn round-border" type="submit">{{ t('save') }}</button>
                            @endif
                        </form>
                    </div>
                </div>
            </div>
        </div>

        <div class="modal fade " id="cryptoDetail" tabindex="-1" aria-labelledby="cryptoDetailLabel" style="display:none" aria-hidden="true" >
            <div class="modal-dialog">
                <div class="modal-content" style="border-radius: 31px; padding: 17px">
                    <div class="modal-header">
                        <h4 class="modal-title" id="exampleCryptoModalLabel">{{ t('add_crypto_wallet') }}</h4>
                        @if($currentAdmin->isAllowed([\App\Enums\BUserPermissions::ADD_EDIT_BANK_DETAILS_FOR_CLIENT], $profile->cUser->project_id))
                            <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                                <span aria-hidden="true">×</span>
                            </button>
                        @endif
                    </div>
                    <div class="modal-body">
                        <form action="{{ route('backoffice.crypto.check.address') }}" method="post" name="cryptoDetailUpdate" id="cryptoDetailUpdate">
                            <input type="hidden" value="{{ $profile->id }}" name="c_profile_id">
                            @csrf
                            <div class="row">
                                <div class="col-md-8">
                                    <div class="form-group">
                                        <label for="walletAddress" class="font-weight-bold">{{ t('wallet_address') }}</label>
                                        <input id="walletAddress" class="form-control" name="wallet_address" value="{{ old('wallet_address') }}">
                                        @error('wallet_address')<p class="text-danger">{{ $message }}</p>@enderror
                                    </div>
                                </div>
                                <div class="col-md-2">
                                    <div class="form-group">
                                        <label for="cryptoCurrency" class="font-weight-bold">{{ t('currency') }}</label>
                                        <select id="cryptoCurrency" class="form-control grey-rounded-border" name="crypto_currency">
                                            <option hidden="" value="">{{ t('select_option') }}</option>
                                            @foreach(\App\Enums\Currency::getList() as $currency)
                                                <option value="{{ $currency }}" @if(old('crypto_currency') == $currency) selected @endif>{{ $currency }}</option>
                                            @endforeach
                                        </select>
                                        @error('crypto_currency')<p class="text-danger">{{ $message }}</p>@enderror
                                    </div>
                                </div>
                            </div>
                            @if($currentAdmin->isAllowed([\App\Enums\BUserPermissions::ADD_EDIT_BANK_DETAILS_FOR_CLIENT], $profile->cUser->project_id))
                                <button class="btn themeBtn round-border" type="submit">{{ t('save') }}</button>
                            @endif
                        </form>
                    </div>
                </div>
            </div>
        </div>

</div>
