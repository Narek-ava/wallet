<!-- Modal -->
<div class="modal fade" id="exampleModal" tabindex="-1" aria-labelledby="exampleModalLabel"
     aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content" style="border-radius: 31px; padding: 17px">
            <div class="modal-header">
                <h4 class="modal-title" id="exampleModalLabel">{{ t('bank_details') }}</h4>
                <input type="hidden" class="type_swift" value="{{ \App\Enums\TemplateType::TEMPLATE_TYPE_SWIFT }}" />
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <form action="{{ route('backoffice.add.bank.detail', $operation->id)}}" method="post">
                    @csrf
                    <div class="row">
                        <div class="col-md-3">
                            <div class="form-group">
                                <label for="inputEmail" class="font-weight-bold">{{ t('template_name') }}</label>
                                <input class="form-control" name="template_name"
                                       value="{{ old('template_name') }}">
                                @error('template_name')
                                <div class="error text-danger">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>

                        <div class="col-md-3">
                            <div class="form-group">
                                <label for="inputEmail"
                                       class="font-weight-bold">{{ t('country_of_clients_bank') }}</label>
                                <select class="form-control grey-rounded-border" name="country">
                                    <option value="" hidden>{{ t('select') }} ...</option>
                                    @foreach(\App\Models\Country::getCountries(false) as $key => $country)
                                        <option value="{{ $key }}"> {{ $country }}</option>
                                    @endforeach
                                </select>
                                @error('country')
                                <div class="error text-danger">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>

                        <div class="col-md-2">
                            <div class="form-group">
                                <label for="inputEmail" class="font-weight-bold">{{ t('currency') }}</label>
                                <select class="form-control grey-rounded-border" name="currency">
                                    <option value="" hidden>{{ t('select') }} ...</option>
                                    @foreach(App\Enums\Currency::FIAT_CURRENCY_NAMES as $key => $currency)
                                        <option value="{{ $currency }}"> {{ $currency }}</option>
                                    @endforeach
                                </select>
                                @error('currency')
                                <div class="error text-danger">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>

                        <div class="col-md-2">
                            <div class="form-group">
                                <label for="type" class="font-weight-bold">{{ t('method') }}</label>
                                <select id="type" class="form-control grey-rounded-border" name="type">
                                    <option hidden="" value="">{{ t('select_option') }}</option>
                                    @foreach(\App\Enums\AccountType::ACCOUNT_WIRE_TYPES as $key => $type)
                                        <option value="{{ $key }}" @if(($errors->any() && !is_null(old('type')) && (int)old('type') == (int)$key)) selected @endif>{{ $type }}</option>
                                    @endforeach
                                </select>
                                @error('type')<p class="text-danger">{{ $message }}</p>@enderror
                            </div>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-5">
                            <div class="form-group">
                                <label for="inputEmail" class="font-weight-bold">IBAN</label>
                                <input type="text" class="form-control"
                                       id="exampleInputPassword1" name="iban" value="{{ old('iban') }}">
                                @error('iban')
                                <div class="error text-danger">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>
                        <div class="col-md-5">
                            <div class="form-group">
                                <label for="inputEmail" class="font-weight-bold">SWIFT/BIC</label>
                                <input type="text" class="form-control"
                                       id="exampleInputPassword1" name="swift"
                                       value="{{ old('swift') }}">
                                @error('swift')
                                <div class="error text-danger">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-3">
                            <div class="form-group">
                                <label for="inputEmail" class="font-weight-bold">{{ t('account_holder') }}</label>
                                <input type="text" class="form-control" name="account_holder"
                                       id="exampleInputPassword1" value="{{ old('template_name') }}">
                                @error('account_holder')
                                <div class="error text-danger">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="form-group">
                                <label for="inputEmail" class="font-weight-bold">{{ t('account_number') }}</label>
                                <input type="text" class="form-control" name="account_number"
                                       id="exampleInputPassword1" value="{{ old('account_number') }}">
                                @error('account_number')
                                <div class="error text-danger">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="form-group">
                                <label for="inputEmail" class="font-weight-bold">{{ t('bank_name') }}</label>
                                <input type="text" class="form-control" name="bank_name"
                                       id="exampleInputPassword1" value="{{ old('bank_name') }}">
                                @error('bank_name')
                                <div class="error text-danger">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-10">
                            <div class="form-group">
                                <label for="inputEmail" class="font-weight-bold">{{ t('bank_address') }}</label>
                                <input type="text" class="form-control" name="bank_address"
                                       id="exampleInputPassword1" value="{{ old('bank_address') }}">
                                @error('bank_address')
                                <div class="error text-danger">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>
                    </div>

                    <div class="row correspondent_bank_details" hidden>
                        <div class="col-md-5">
                            <div class="form-group">
                                <label for="correspondent_bank" class="font-weight-bold"> {{ t('ui_cabinet_correspondent_bank') }}</label>
                                <input id="correspondent_bank" type="text" class="form-control" name="correspondent_bank" value="{{ old('correspondent_bank') }}">
                            </div>
                        </div>
                        <div class="col-md-5">
                            <div class="form-group">
                                <label for="correspondent_bank_swift" class="font-weight-bold"> {{ t('ui_cabinet_correspondent_bank_swift') }} </label>
                                <input id="correspondent_bank_swift" type="text" class="form-control" name="correspondent_bank_swift" value="{{ old('correspondent_bank_swift') }}">
                            </div>
                        </div>
                    </div>
                    <div class="row intermediary_bank_details" hidden>
                        <div class="col-md-5">
                            <div class="form-group">
                                <label for="intermediary_bank" class="font-weight-bold"> {{ t('ui_cabinet_intermediary_bank') }}</label>
                                <input id="intermediary_bank" type="text" class="form-control" name="intermediary_bank" value="{{ old('intermediary_bank') }}">
                            </div>
                        </div>
                        <div class="col-md-5">
                            <div class="form-group">
                                <label for="intermediary_bank_swift" class="font-weight-bold"> {{ t('ui_cabinet_intermediary_bank_swift') }} </label>
                                <input id="intermediary_bank_swift" type="text" class="form-control" name="intermediary_bank_swift" value="{{old('intermediary_bank_swift')}}">
                            </div>
                        </div>
                    </div>

                    <button class="btn themeBtn round-border" type="submit">{{ t('save') }}</button>
                </form>
            </div>
        </div>
    </div>
</div>
@if($accounts)
    <h5 class="d-block mt-3 ml-4">{{ t('choose_from') }}</h5>
    <form action="{{ route('backoffice.confirm.transaction', $operation->id) }}" method="post">
        @csrf
        <select name="account" class="d-block w-75 select-detail ml-4 mt-1">
            <option value="">{{t('select')}}</option>
            @foreach($accounts as $account)
                <option value="{{ $account->id }}" @if($operation->from_account == $account->id) selected @endif>{{ $account->name }}</option>
            @endforeach
        </select>
        @error('account')
        <div class="error text-danger">{{ $message }}</div>
        @enderror
        <button class="btn themeBtn mt-2 mb-2 ml-4 round-border" type="submit">{{ t('confirm') }}</button>
    </form>
@endif
