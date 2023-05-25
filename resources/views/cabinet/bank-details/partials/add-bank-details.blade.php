<div class="modal fade " id="bankDetail" tabindex="-1" aria-labelledby="bankDetailLabel" style="display:none" aria-hidden="true" >
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">{{ t('bank_details') }}</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">×</span>
                </button>
            </div>
            <form action="{{ route('cabinet.bank.details.store') }}" method="post" name="bankDetailUpdate" id="bankDetailUpdate">
                {{ csrf_field() }}

                <div class="modal-body">
                    <div class="row">
                        <div class="col-md-6 col-lg-3">
                            <div class="form-group">
                                <label for="templateName" class="font-weight-bold">{{ t('template_name') }}</label>
                                <input id="templateName" class="form-control" name="template_name" value="{{ old('template_name') }}">
                                @error('template_name')<p class="text-danger">{{ $message }}</p>@enderror
                            </div>
                        </div>
                        <div class="col-md-6 col-lg-3">
                            <div class="form-group">
                                <label for="country" class="font-weight-bold mb-2">{{ t('country_of_clients_bank') }}</label>
                                <select id="country" class="form-control grey-rounded-border" name="country" style="width: 100% !important;">
                                    <option hidden="" value="">{{ t('select_option') }}</option>
                                    @foreach(\App\Models\Country::getCountries(false) as $countryCode => $country)
                                        <option value="{{ $countryCode }}" @if(old('country') == $countryCode) selected @endif>{{ $country }}</option>
                                    @endforeach
                                </select>
                                @error('country')<p class="text-danger">{{ $message }}</p>@enderror
                            </div>
                        </div>
                        <div class="col-md-6 col-lg-3">
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
                        <div class="col-md-6 col-lg-3">
                            <div class="form-group">
                                <label for="type" class="font-weight-bold">{{ t('method') }}</label>
                                <select id="type" class="form-control grey-rounded-border" name="type">
                                    <option hidden="" value="">{{ t('select_option') }}</option>
                                    @foreach(\App\Enums\AccountType::ACCOUNT_WIRE_TYPES as $key => $type)
                                        <option value="{{ $key }}" @if(($errors->any() && is_string(old('type')) && (int)old('type') == (int)$key)) selected @endif>{{ $type }}</option>
                                    @endforeach
                                </select>
                                @error('type')<p class="text-danger">{{ $message }}</p>@enderror
                            </div>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="iban" class="font-weight-bold">{{ t('ui_cabinet_bank_details_applicable_iban') }}</label>
                                <input id="iban" type="text" class="form-control" name="iban" value="{{ old('iban') }}">
                                @error('iban')<p class="text-danger">{{ $message }}</p>@enderror
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="swift" class="font-weight-bold">{{ t('ui_cabinet_bank_details_swift_bic') }}</label>
                                <input id="swift" type="text" class="form-control" name="swift" value="{{ old('swift') }}">
                                @error('swift')<p class="text-danger">{{ $message }}</p>@enderror
                            </div>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-12 col-lg-4">
                            <div class="form-group">
                                <label for="accountHolder" class="font-weight-bold">{{ t('ui_cabinet_bank_details_account_holder_placeholder') }}</label>
                                <input id="accountHolder" type="text" class="form-control" name="account_holder" value="{{ old('account_holder') }}">
                                @error('account_holder')<p class="text-danger">{{ $message }}</p>@enderror
                            </div>
                        </div>
                        <div class="col-md-12 col-lg-4">
                            <div class="form-group">
                                <label for="accountNumber" class="font-weight-bold">{{ t('ui_cabinet_bank_details_account_number_placeholder') }}</label>
                                <input id="accountNumber" type="text" class="form-control" name="account_number" value="{{ old('account_number') }}">
                                @error('account_number')<p class="text-danger">{{ $message }}</p>@enderror
                            </div>
                        </div>
                        <div class="col-md-12 col-lg-4">
                            <div class="form-group">
                                <label for="bankName" class="font-weight-bold">{{ t('ui_cabinet_bank_details_bank_name_placeholder') }}</label>
                                <input id="bankName" type="text" class="form-control" name="bank_name" value="{{ old('bank_name') }}">
                                @error('bank_name')<p class="text-danger">{{ $message }}</p>@enderror
                            </div>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-lg-6">
                            <div class="form-group">
                                <label for="bankAddress" class="font-weight-bold">{{ t('ui_cabinet_bank_details_bank_address_placeholder') }}</label>
                                <input id="bankAddress" type="text" class="form-control" name="bank_address" value="{{ old('bank_address') }}">
                                @error('bank_address')<p class="text-danger">{{ $message }}</p>@enderror
                            </div>
                        </div>
                    </div>
                    <div class="row correspondent_bank_details" hidden>
                        <div class="col-md-12 col-lg-6">
                            <div class="form-group">
                                <label for="correspondentBank" class="font-weight-bold">{{ t('ui_cabinet_correspondent_bank') }}</label>
                                <input id="correspondentBank" type="text"
                                       class="form-control" name="correspondent_bank" value="{{ old('correspondent_bank') }}">
                                @error('correspondent_bank')<p class="text-danger">{{ $message }}</p>@enderror
                            </div>
                        </div>
                        <div class="col-md-12 col-lg-6">
                            <div class="form-group">
                                <label for="correspondentBankSwift" class="font-weight-bold">{{ t('ui_cabinet_correspondent_bank_swift') }}</label>
                                <input id="correspondentBankSwift" type="text"
                                       class="form-control" name="correspondent_bank_swift"
                                       value="{{ old('correspondent_bank_swift') }}">
                                @error('correspondent_bank_swift')<p class="text-danger">{{ $message }}</p>@enderror
                            </div>
                        </div>
                    </div>

                    <div class="row intermediary_bank_details" hidden>
                        <div class="col-md-12 col-lg-6">
                            <div class="form-group">
                                <label for="intermediaryBank" class="font-weight-bold">{{ t('ui_cabinet_intermediary_bank') }}</label>
                                <input id="intermediaryBank" type="text"
                                       class="form-control" name="intermediary_bank"
                                       value="{{ old('intermediary_bank') }}">
                                @error('intermediary_bank')<p class="text-danger">{{ $message }}</p>@enderror
                            </div>
                        </div>
                        <div class="col-md-12 col-lg-6">
                            <div class="form-group">
                                <label for="intermediaryBankSwift" class="font-weight-bold">{{ t('ui_cabinet_intermediary_bank_swift') }}</label>
                                <input id="intermediaryBankSwift" type="text"
                                       class="form-control" name="intermediary_bank_swift" value="{{ old('intermediary_bank_swift') }}">
                                @error('intermediary_bank_swift')<p class="text-danger">{{ $message }}</p>@enderror
                            </div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="submit" class="btn btn-lg btn-primary themeBtn loader">{{ t('save') }}</button>
                </div>
            </form>
        </div>
    </div>
</div>
