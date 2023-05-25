<div class="modal fade bd-example-modal-sm" id="bankDetailUpdateModal" tabindex="-1" aria-labelledby="bankDetailUpdateModalLabel" aria-hidden="true" >
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">{{ t('bank_details') }}</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">×</span>
                </button>
            </div>
            <form action="{{ route('cabinet.bank.details.update') }}" method="post" name="bankDetailCreate" id="bankDetailCreate">
                @csrf
                <input type="hidden" name="u_account_id" value="{{ old('u_account_id') ?? $uAccount->id ?? null}}">
                <input type="hidden" name="_method" value="put">

                <div class="modal-body">
                    <div class="row align-items-end">
                        <input type="hidden" class="type_swift" value="{{ \App\Enums\TemplateType::TEMPLATE_TYPE_SWIFT }}" />
                        <div class="col-md-6 col-lg-3">
                            <h6 class="mt-4 textBold mb-0">{{ t('ui_cabinet_bank_details_name_of_template') }}</h6>
                            <input class="disabledField" disabled type="text" id="updateTemplateName" name="u_template_name" value="{{  old('u_template_name') }}">
                            @error('u_template_name')<p class="text-danger">{{ $message }}</p>@enderror
                        </div>
                        <div class="col-md-6 col-lg-3">
                            <h6 class="mt-4 textBold">{{ t('wire_transfer_country_of_your_bank') }}</h6>
                            <select disabled name="u_country" id="updateCountry" class="disabledField bank-detail-select-style" style="width: 100%;">
                                @foreach(\App\Models\Country::getCountries(false) as $countryCode => $country)
                                    <option value="{{ $countryCode }}">{{ $country }}</option>
                                @endforeach
                                @error('u_country')<p class="text-danger">{{ $message }}</p>@enderror
                            </select>
                        </div>
                        <div class="col-md-6 col-lg-3">
                            <h6 class="mt-4 textBold">{{ t('withdrawal_currency') }}</h6>
                            <select disabled name="u_currency" id="updateCurrency" class="disabledField bank-detail-select-style">
                                @foreach(\App\Enums\Currency::FIAT_CURRENCY_NAMES as $currency)
                                    <option value="{{ $currency }}" >{{ $currency }}</option>
                                @endforeach
                                @error('u_currency')<p class="text-danger">{{ $message }}</p>@enderror
                            </select>
                        </div>
                        <div class="col-md-6 col-lg-3">
                            <h6 class="mt-4 textBold">{{ t('type') }}</h6>
                            <select disabled name="u_type" id="updateType" class="disabledField bank-detail-select-style">
                                @foreach(\App\Enums\AccountType::ACCOUNT_WIRE_TYPES as $key => $type)
                                    <option value="{{ $key }}" >{{ $type }}</option>
                                @endforeach
                                @error('u_type')<p class="text-danger">{{ $message }}</p>@enderror
                            </select>
                        </div>
                        <div class="col-md-6">
                            <h6 class="mt-4 textBold">{{ t('ui_cabinet_bank_details_applicable_iban') }}</h6>
                            <input class="disabledField" disabled type="text" name="u_iban" id="updateIban" value="{{ old('u_iban') }}">
                            @error('u_iban')<p class="text-danger">{{ $message }}</p>@enderror
                        </div>
                        <div class="col-md-6">
                            <h6 class="mt-4 textBold">SWIFT/BIC</h6>
                            <input class="disabledField" disabled type="text" name="u_swift" id="updateSwift" value="{{ old('u_swift') }}">
                            @error('u_swift')<p class="text-danger">{{ $message }}</p>@enderror
                        </div>
                        <div class="col-md-6">
                            <h6 class="mt-4 textBold">{{ t('withdraw_wire_account_holder') }}</h6>
                            <input class="disabledField" disabled type="text" name="u_account_holder" id="updateAccountHolder" value="{{ old('u_account_holder') }}">
                            @error('u_account_holder')<p class="text-danger">{{ $message }}</p>@enderror
                        </div>
                        <div class="col-md-6">
                            <h6 class="mt-4 textBold">{{ t('withdraw_wire_account_number') }}</h6>
                            <input class="disabledField" disabled type="text" name="u_account_number" id="updateAccountNumber" value="{{ old('u_account_number') }}">
                            @error('u_account_number')<p class="text-danger">{{ $message }}</p>@enderror
                        </div>
                        <div class="col-md-6">
                            <h6 class="mt-4 textBold">{{ t('withdraw_wire_bank_name') }}</h6>
                            <input class="disabledField" disabled type="text" name="u_bank_name" id="updateBankName" value="{{ old('u_bank_name') }}">
                            @error('u_bank_name')<p class="text-danger">{{ $message }}</p>@enderror
                        </div>
                        <div class="col-md-6">
                            <h6 class="mt-4 textBold">{{ t('withdraw_wire_bank_address') }}</h6>
                            <input class="disabledField" disabled type="text" name="u_bank_address" id="updateBankAddress" value="{{ old('u_bank_address') }}">
                            @error('u_bank_address')<p class="text-danger">{{ $message }}</p>@enderror
                        </div>
                        <div class="col-md-6 u_correspondent_bank_details">
                            <h6 class="mt-4 textBold">{{ t('ui_cabinet_correspondent_bank') }}</h6>
                            <input disabled id="updateCorrespondentBank" type="text" class="disabledField" name="u_correspondent_bank" value="{{ old('u_correspondent_bank') }}">
                            @error('u_correspondent_bank')<p class="text-danger">{{ $message }}</p>@enderror
                        </div>

                        <div class="col-md-6 u_correspondent_bank_details">
                            <h6 class="mt-4 textBold">{{ t('ui_cabinet_correspondent_bank_swift') }}</h6>
                            <input disabled id="updateCorrespondentBankSwift" type="text" class="disabledField" name="u_correspondent_bank_swift" value="{{ old('u_correspondent_bank_swift') }}">
                            @error('u_correspondent_bank_swift')<p class="text-danger">{{ $message }}</p>@enderror
                        </div>

                        <div class="col-md-6 u_intermediary_bank_details">
                            <h6 class="mt-4 textBold">{{ t('ui_cabinet_intermediary_bank') }}</h6>
                            <input disabled id="updateIntermediaryBank" type="text" class="disabledField"
                                   name="u_intermediary_bank" value="{{ old('u_intermediary_bank') }}">
                            @error('u_intermediary_bank')<p class="text-danger">{{ $message }}</p>@enderror
                        </div>

                        <div class="col-md-6 u_intermediary_bank_details">
                            <h6 class="mt-4 textBold">{{ t('ui_cabinet_intermediary_bank_swift') }}</h6>
                            <input disabled id="updateIntermediaryBankSwift" type="text" class="disabledField"
                                   name="u_intermediary_bank_swift" value="{{ old('u_intermediary_bank_swift') }}"/>
                            @error('u_intermediary_bank_swift')<p class="text-danger">{{ $message }}</p>@enderror
                        </div>
                    </div>
                    <div class="dates-container"></div>
                </div>
                <div class="modal-footer">
                    {{--<a id="deleteBankDetail" href="{{ route('cabinet.bank.details.delete', ['id' => $uAccount->id]) }}" class="delete-template-styles">Delete bank template</a>--}}
                    <button class="btn themeBtn" type="button" id="changeButton">{{ t('ui_change') }}</button>
                    <button class="btn themeBtn d-none loader" type="submit" id="saveButton">{{ t('save') }}</button>
                    <a data-toggle="modal" id="addBankDetailDelete" data-target="#bankDetailDelete" style="cursor: pointer" class="delete-template-styles">{{ t('ui_delete_bank_template') }}</a>
                </div>
            </form>
        </div>
    </div>
</div>
