<!-- Modal -->
<style>
    @media only screen and (max-width: 768px) {
        .from-fee-minimum {
            margin-left: 0 !important;
            margin-top: 0 !important;
        }
    }
</style>
<div class="modal fade" id="exampleModal1" tabindex="-1" aria-labelledby="exampleModalLabel"
     aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content" style="border-radius: 31px; padding: 17px">
            <div class="modal-header">
                <h4 class="modal-title" id="exampleModalLabel">{{ t('transaction_details') }}</h4>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <div>
                    @if($operation->from_account)
                        <form action="{{ route('backoffice.add.card.transaction', $operation) }}" method="post">
                            @csrf
                            <div class="row">
                                <div class="col-md-3">
                                    <div class="form-group">
                                        <label for="inputEmail" class="font-weight-bold">{{ t('date') }}</label>
                                        <input name="date" data-provide="datepicker" data-date-format="yyyy-mm-dd"
                                               class="form-control grey-rounded-border" autocomplete="off" value="{{ Carbon\Carbon::now()->format('Y-m-d') }}">
                                        @error('date')
                                        <div class="error text-danger">{{ $message }}</div>
                                        @enderror
                                    </div>
                                </div>

                                <div class="col-md-3">
                                    <div class="form-group">
                                        <label for="inputEmail"
                                               class="font-weight-bold">{{ t('transaction_type') }}</label>
                                        <select id="exchange"
                                                class="form-control grey-rounded-border transaction-type"
                                                name="transaction_type">
                                            <option value="" hidden>{{ t('select') }}...</option>
                                            @foreach(App\Enums\TransactionType::TRX_TYPES_FOR_TOP_UP_CARD as $key => $trxType)
                                                @if($key === \App\Enums\TransactionType::REFUND)
                                                    @if( $operation->step === \App\Enums\TransactionSteps::TRX_STEP_TWO)
                                                        <option value="{{ $key }}" @if($key == $transactionType) selected @endif>{{ t($trxType) }}</option>
                                                    @endif
                                                @elseif($key === \App\Enums\TransactionType::CHARGEBACK)
                                                    @if( $operation->step === \App\Enums\TransactionSteps::TRX_STEP_ONE || \App\Enums\TransactionSteps::TRX_STEP_FIVE)
                                                        <option value="{{ $key }}">{{ t($trxType) }}</option>
                                                    @endif
                                                @else
                                                    <option value="{{ $key }}" @if($key == $transactionType) selected @endif>{{ t($trxType) }}</option>
                                                @endif
                                            @endforeach
                                        </select>
                                        @error('transaction_type')
                                        <div class="error text-danger">{{ $message }}</div>
                                        @enderror
                                    </div>
                                </div>
                                <div class="col-md-3 exchange-rate">
                                    <div class="form-group">
                                        <label for="inputEmail"
                                               class="font-weight-bold">{{ t('exchange_rate') }}</label>
                                        <input type="text" name="exchange_rate"
                                               class="form-control grey-rounded-border"
                                               id="exchangeRate" readonly="">
                                        @error('exchange_rate')
                                        <div class="error text-danger">{{ $message }}</div>
                                        @enderror
                                    </div>
                                </div>
                                <div class="col-md-3 exchange-api">
                                    <div class="form-group">
                                        <input hidden type="text" id="exchange_api" value="{{ $api }}"
                                               name="exchange_api">
                                        @error('exchange_api')
                                        <div class="error text-danger">{{ $message }}</div>
                                        @enderror
                                    </div>
                                </div>
                            </div>

                            <div class="row">
                                <div class="col-md-3">
                                    <div class="form-group">
                                        <label for="inputEmail" class="font-weight-bold">{{ t('from_type') }}</label>
                                        <select id="from_type" class="form-control grey-rounded-border from-type"
                                                name="from_type"
                                                onchange="getFromAccountsByType('from_account')">
                                            <option value="" hidden>{{ t('ui_cabinet_default_select_option_text') }}</option>
                                            @foreach(App\Enums\Providers::NAMES as $key => $providerType)
                                                <option value="{{ $key }}"  @if($key == $fromType) selected @endif>{{ $providerType }}</option>
                                            @endforeach
                                        </select>
                                        @error('from_type')
                                        <div class="error text-danger">{{ $message }}</div>
                                        @enderror
                                    </div>
                                </div>
                                <div class="col-md-3">
                                    <div class="form-group">
                                        <label for="inputEmail" class="font-weight-bold">{{ t('account') }}</label>
                                        <select id="from_account" class="form-control grey-rounded-border"
                                                name="from_account" disabled
                                                onchange="getCommissionsFromAccount('from_account', 1)">
                                            <option value="" hidden>{{ t('ui_cabinet_default_select_option_text') }}</option>
                                            <option class="from-account-option" value=""></option>
                                        </select>
                                        @error('from_account')
                                        <div class="error text-danger">{{ $message }}</div>
                                        @enderror
                                    </div>
                                </div>
                                <div class="col-md-3">
                                    <div class="form-group">
                                        <label for="inputEmail" class="font-weight-bold">{{ t('to_type') }}</label>
                                        <select class="form-control grey-rounded-border to-type" name="to_type"
                                                id="to_type"
                                                onchange="getToAccountsByType('to_account')">
                                            <option value="" hidden>{{ t('select') }}...</option>
                                            @foreach(App\Enums\Providers::NAMES as $key => $providerType)
                                                <option value="{{ $key }}" @if($key == $toType) selected @endif>{{ $providerType }}</option>
                                            @endforeach
                                        </select>
                                        @error('to_type')
                                        <div class="error text-danger">{{ $message }}</div>
                                        @enderror
                                    </div>
                                </div>
                                <div class="col-md-3">
                                    <div class="form-group">
                                        <label for="inputEmail" class="font-weight-bold">{{ t('account') }}</label>
                                        <select id="to_account" class="form-control grey-rounded-border"
                                                name="to_account" disabled
                                                onchange="getCommissionsToAccount('to_account', 0)">
                                            <option value="" hidden>{{ t('ui_cabinet_default_select_option_text') }}</option>
                                            <option class="to-account-option" value=""></option>
                                        </select>
                                        @error('to_account')
                                        <div class="error text-danger">{{ $message }}</div>
                                        @enderror
                                    </div>
                                </div>
                            </div>

                            <div class="row">
                                <div class="col-md-3">
                                    <div class="form-group">
                                        <label for="inputEmail" class="font-weight-bold">{{ t('currency') }}</label>
                                        <select class="form-control grey-rounded-border from_currency"
                                                id="fromFiat" name="from_currency" onchange="getAccountsByCurrency('from')">
                                            <option value="" hidden>{{ t('select') }}...</option>
                                            @foreach(App\Enums\Currency::FIAT_CURRENCY_NAMES as $key => $currencyType)
                                                <option value="{{ $currencyType }}" @if($fromCurrency == $key) selected @endif>{{ $currencyType }}</option>
                                            @endforeach
                                        </select>
                                        @error('from_currency')
                                        <div class="error text-danger">{{ $message }}</div>
                                        @enderror
                                    </div>
                                </div>
                                <div class="col-md-3">
                                    <div class="form-group">
                                        <label for="inputEmail" class="font-weight-bold">{{ t('amount') }}</label>
                                        <input type="number" class="form-control grey-rounded-border"
                                               value="{{ $allowedMaxAmount }}"
                                               name="currency_amount" min="0"
                                               onkeypress="return (event.charCode == 8 || event.charCode == 0 || event.charCode == 13 || event.charCode == 46) ? null : event.charCode >= 48 && event.charCode <= 57"
                                               @if($allowedMaxAmount)
                                               max="{{$allowedMaxAmount}}"
                                               @endif
                                               id="currencyAmount" step="any">
                                        @error('currency_amount')
                                        <div class="error text-danger">{{ $message }}</div>
                                        @enderror
                                    </div>
                                </div>
                                <div class="col-md-3 to-cryptocurrency" hidden>
                                    <div class="form-group">
                                        <label for="inputEmail"
                                               class="font-weight-bold to_currency">{{ t('to_cryptocurrency') }}</label>
                                        <select class="form-control grey-rounded-border to-currency"
                                                id="toCryptocurrency" name="to_currency" onchange="getAccountsByCurrency('to')">
                                            <option value="" hidden>{{ t('select') }}...</option>
                                            @foreach(App\Enums\Currency::getList() as $key => $currencyType)
                                                <option value="{{ $currencyType }}">{{ $currencyType }}</option>
                                            @endforeach
                                        </select>
                                        @error('to_currency')
                                        <div class="error text-danger">{{ $message }}</div>
                                        @enderror
                                    </div>
                                </div>

                                <div class="col-md-3 cryptocurrency-amount" hidden>
                                    <div class="form-group">
                                        <label for="inputEmail" class="font-weight-bold">{{ t('amount') }}</label>
                                        <input type="text" class="form-control grey-rounded-border"
                                               name="cryptocurrency_amount" min="0"
                                               onkeypress="return (event.charCode == 8 || event.charCode == 0 || event.charCode == 13 || event.charCode == 46) ? null : event.charCode >= 48 && event.charCode <= 57"
                                               id="amountTo" readonly="">
                                        @error('cryptocurrency_amount')
                                        <div class="error text-danger">{{ $message }}</div>
                                        @enderror
                                    </div>
                                </div>

                                <div class="col-md-4 crypto-address" hidden>
                                    <div class="form-group">
                                        <label for="inputEmail" class="font-weight-bold">{{ t('to_address') }}</label>
                                        <input type="text" class="form-control grey-rounded-border crypto-address"
                                               name="to_address"
                                               id="to_address"
                                               value="" readonly>
                                        @error('to_address')
                                        <div class="error text-danger">{{ $message }}</div>
                                        @enderror
                                    </div>
                                </div>
                            </div>

                            <div class="row commissionFee">
                                <div class="col-md-4"><h4 class="mt-3 mb-5">{{ t('commissions') }}</h4></div>
                            </div>

                            <div class="row commissionFee">
                                <div class="col-md-3 commissions">
                                    <div class="form-group">
                                        <label for="inputEmail"
                                               class="font-weight-bold exchange-fee-percent">{{ t('exchange_fee') }}
                                            %</label>
                                        <input type="number" class="form-control grey-rounded-border commission"
                                               name="exchange_fee_percent" readonly step="any" min="0"
                                               id="exchange_fee_percent" value="">
                                        @error('exchange_fee_percent')
                                        <div class="error text-danger">{{ $message }}</div>
                                        @enderror
                                    </div>
                                </div>
                                <div class="col-md-3">
                                    <div class="form-group">
                                        <label for="inputEmail"
                                               class="font-weight-bold exchange-fee">{{ t('exchange_fee') }}</label>
                                        <input type="number" class="form-control grey-rounded-border commission"
                                               readonly step="any" min="0"
                                               name="exchange_fee"
                                               id="exchange_fee" value="">
                                        @error('exchange_fee')
                                        <div class="error text-danger">{{ $message }}</div>
                                        @enderror
                                    </div>
                                </div>
                                <div class="col-md-3 to-fee">
                                    <div class="form-group">
                                        <label for="inputEmail"
                                               class="font-weight-bold to-fee-percent">{{ t('to_fee') }} %</label>
                                        <input type="number" class="form-control grey-rounded-border commission"
                                               name="to_fee_percent" readonly step="any" min="0"
                                               id="to_fee_percent" value="">
                                        @error('to_fee_percent')
                                        <div class="error text-danger">{{ $message }}</div>
                                        @enderror
                                    </div>
                                </div>
                                <div class="col-md-3 to-fee">
                                    <div class="form-group">
                                        <label for="inputEmail" class="font-weight-bold ">{{ t('to_fee') }}</label>
                                        <input type="number" class="form-control grey-rounded-border commission"
                                               readonly step="any" min="0"
                                               name="to_fee"
                                               id="to_fee" value="">
                                        @error('to_fee')
                                        <div class="error text-danger">{{ $message }}</div>
                                        @enderror
                                    </div>
                                </div>

                            </div>

                            <div class="row commissionFee">
                                <div class="col-md-3 from-fee-minimum">
                                    <div class="form-group">
                                        <label for="inputEmail"
                                               class="font-weight-bold exchange-fee-min">{{ t('exchange_fee_minimum') }}</label>
                                        <input type="number" class="form-control grey-rounded-border commission"
                                               name="exchange_fee_min" readonly step="any" min="0"
                                               id="exchange_fee_min" value="">
                                        @error('exchange_fee_min')
                                        <div class="error text-danger">{{ $message }}</div>
                                        @enderror
                                    </div>
                                </div>
                                <div class="col-md-3"></div>
                                <div class="col-md-3 to-fee">
                                    <div class="form-group">
                                        <label for="inputEmail"
                                               class="font-weight-bold  to-fee-min">{{ t('to_fee_minimum') }}</label>
                                        <input type="number" class="form-control grey-rounded-border commission"
                                               name="to_fee_min" readonly step="any" min="0"
                                               id="to_fee_min" value="">
                                        @error('to_fee_min')
                                        <div class="error text-danger">{{ $message }}</div>
                                        @enderror
                                    </div>
                                </div>
                            </div>

                            <div class="row">

                                <div class="col-md-6">
                                    <button class="btn themeBtn round-border add-trx-btn"
                                            type="submit">{{ t('save') }}</button>
                                </div>
                                <div class="col-md-6">
                                    <button type="button" class="btn themeBtn round-border"
                                            onclick="toggleCommissionsEdit()">{{ t('edit_commissions') }}
                                    </button>
                                </div>
                            </div>
                        </form>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>


