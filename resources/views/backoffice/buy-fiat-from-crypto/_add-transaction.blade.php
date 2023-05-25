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
                        <form action="{{ route('backoffice.add.buy.fiat.from.crypto.transaction', $operation->id) }}" method="post">
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
                                                name="transaction_type"
                                                onchange="selectForm(this.id, {{ $operation->step }})">
                                            <option value="" hidden>{{ t('select') }}...</option>
                                            @foreach(App\Enums\TransactionType::TRX_TYPES as $key => $transactionType)
                                                <option value="{{ $key }}">{{  t($transactionType) }}</option>
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
                                               id="exchangeRate" readonly="" value="{{ $exchangeRate }}">
                                        @error('exchange_rate')
                                        <div class="error text-danger">{{ $message }}</div>
                                        @enderror
                                    </div>
                                </div>
                                <div class="col-md-3 exchange-api">
                                    <div class="form-group">
                                        <label for="inputExchangeApi"
                                               class="font-weight-bold">{{ t('exchange_api') }}</label>
                                        <select id="exchangeApi"
                                                class="form-control grey-rounded-border transaction-type"
                                                name="exchange_api">
                                            @foreach(App\Enums\ExchangeApiProviders::NAMES as $key => $transactionType)
                                                <option value="{{ $key }}"
                                                        @if ($cryptoTransaction && $cryptoTransaction->type == $transactionType) selected @endif
                                                >{{ t($transactionType) }}</option>
                                            @endforeach
                                        </select>
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
                                            @foreach(App\Enums\Providers::NAMES as $key => $providerType)
                                                <option value="{{ $key }}"
                                                >{{ $providerType }}</option>
                                            @endforeach
                                        </select>
                                        @error('from_type')
                                        <div class="error text-danger">{{ $message }}</div>
                                        @enderror
                                    </div>
                                </div>
                                <div class="col-md-3">
                                    <div class="form-group from-account">
                                        <label for="inputEmail" class="font-weight-bold">{{ t('account') }}</label>
                                        <select id="from_account" class="form-control grey-rounded-border"
                                                name="from_account"
                                                onchange="getCommissionsFromAccount('from_account', 1)">
                                            @if(isset($fromAccounts))
                                                @foreach($fromAccounts as $id => $fromAccount)
                                                    <option class="to-account-option"
                                                            value="{{ $fromAccount->id}}"
                                                            @if($operation->step == 3 && $fromAccount->id == $operation->provider_account_id ) selected @endif>
                                                           {{$fromAccount->name}}
                                                    </option>
                                                @endforeach
                                            @endif
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
                                                <option value="{{ $key }}">{{ $providerType }}</option>
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
                                                name="to_account"
                                                onchange="getCommissionsToAccount('to_account', 0)">
                                            @if(isset($toAccounts))
                                                @foreach($toAccounts as $id => $toAccount)
                                                    <option class="to-account-option"
                                                            value="{{ $toAccount->id}}"
                                                            @if( $operation->step == 2 && $toAccount->id == $operation->provider_account_id ) selected @endif >
                                                        @if($toProviders) {{ $toProviders[$id]->name }} - @endif
                                                        {{$toAccount->name}}
                                                    </option>
                                                @endforeach
                                            @endif
                                        </select>
                                        @error('to_account')
                                        <div class="error text-danger">{{ $message }}</div>
                                        @enderror
                                    </div>
                                </div>
                            </div>

                            <div class="row">
                                <div class="col-md-3 from-currency">
                                    <div class="form-group">
                                        <label for="inputEmail" class="font-weight-bold from-currency-label">{{ t('currency') }}</label>
                                        <select class="form-control grey-rounded-border from_currency"
                                                id="fromFiat" name="from_currency"
                                                onchange=" getAccountsByCurrency('from');">
                                            @foreach($allCurrencies as $key => $currencyType)
                                                <option value="{{ $currencyType }}"
                                                        @if($fromCurrency == $currencyType) selected @endif
                                                >{{ $currencyType }}</option>
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
                                               value="{{$recipientAmount ?? $operation->amount}}"
                                               name="currency_amount"
                                               onkeypress="return (event.charCode == 8 || event.charCode == 0 || event.charCode == 13 || event.charCode == 46) ? null : event.charCode >= 48 && event.charCode <= 57"
                                               @if($allowedMaxAmount)
                                               max="{{$allowedMaxAmount}}"
                                               @endif
                                               min="0"
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
                                            @foreach(App\Enums\Currency::FIAT_CURRENCY_NAMES as $key => $currencyType)
                                                <option value="{{ $currencyType }}"
                                                        @if($currencyType == $operation->to_currency) selected @endif
                                                >{{ $currencyType }}</option>
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
                                               name="cryptocurrency_amount"
                                               onkeypress="return (event.charCode == 8 || event.charCode == 0 || event.charCode == 13 || event.charCode == 46) ? null : event.charCode >= 48 && event.charCode <= 57"
                                               value="{{ $cryptocurrencyAmount }}"
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
                                               value="">
                                        @error('to_address')
                                        <div class="error text-danger">{{ $message }}</div>
                                        @enderror
                                    </div>
                                </div>
                            </div>

                            <div class="row">
                                <div class="col-md-4"><h4 class="mt-3 mb-5">{{ t('commissions') }}</h4></div>
                            </div>

                            <div class="row">
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
                                <div class="col-md-3 blockchain-fee">
                                    <div class="form-group">
                                        <label for="inputEmail"
                                               class="font-weight-bold blockchain-fee-label">{{ t('blockchain_fee') }}</label>
                                        <input type="number" class="form-control grey-rounded-border commission"
                                               name="blockchain_fee" readonly step="any" min="0"
                                               id="blockchain_fee" value="">
                                        @error('blockchain_fee')
                                        <div class="error text-danger">{{ $message }}</div>
                                        @enderror
                                    </div>
                                </div>
                            </div>

                            <div class="row">
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
