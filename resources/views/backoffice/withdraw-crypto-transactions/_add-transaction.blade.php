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
                        <form action="{{ ($operation->operation_type == \App\Enums\OperationOperationType::TYPE_WITHDRAW_CRYPTO) ?
                         route('backoffice.add.withdraw.crypto.transaction', $operation->id) : route('backoffice.add.topup.crypto.transaction', $operation->id) }}" method="post">
                            @csrf
                            <div class="row">
                                <div class="col-md-3">
                                    <div class="form-group">
                                        <label for="inputEmail" class="font-weight-bold">{{ t('date') }}</label>
                                        <input name="date" data-provide="datepicker" data-date-format="yyyy-mm-dd" value="{{date('Y-m-d')}}"
                                               class="form-control grey-rounded-border" autocomplete="off">
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

                                            <option selected value="{{ \App\Enums\TransactionType::CRYPTO_TRX }}">
                                                {{  \App\Enums\TransactionType::getName(\App\Enums\TransactionType::CRYPTO_TRX) }}
                                            </option>
                                            @if($operation->operation_type == \App\Enums\OperationOperationType::TYPE_TOP_UP_CRYPTO)
                                                <option value="{{ \App\Enums\TransactionType::REFUND }}">{{  \App\Enums\TransactionType::getName(\App\Enums\TransactionType::REFUND) }}</option>
                                            @endif
                                        </select>
                                        @error('transaction_type')
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
                                                name="from_type">
                                            <option selected
                                                    value="{{ App\Enums\Providers::CLIENT }}">{{ t('client') }}</option>
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
                                                name="from_account"
                                                onchange="getCommissionsFromAccount('from_account', 1)">
                                            <option selected class="from-account-option"
                                                    value="{{ $operation->fromAccount->id ?? '-'}}">
                                                {{ $operation->fromAccount->name }}
                                            </option>
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
                                                id="to_type">
                                            <option selected
                                                    value="{{ App\Enums\Providers::CLIENT }}">{{ t('client') }}</option>
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
                                            <option selected class="to-account-option"
                                                    value="{{ $operation->toAccount->id ?? '-' }}">
                                                {{ $operation->toAccount->name ?? '-' }}
                                            </option>
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
                                                name="from_currency" onchange="getAccountsByCurrency('from')">
                                            <option value="{{ $operation->from_currency }}" selected>
                                                {{ $operation->from_currency }}
                                            </option>
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
                                               value="{{ floatval($operation->amount) }}"
                                               name="currency_amount" step="any" min="0"
                                               onkeypress="return (event.charCode == 8 || event.charCode == 0 || event.charCode == 13 || event.charCode == 46) ? null : event.charCode >= 48 && event.charCode <= 57"
                                               @if($allowedMaxAmount)
                                               max="{{$allowedMaxAmount}}"
                                               @endif
                                               id="currencyAmount">
                                        @error('currency_amount')
                                        <div class="error text-danger">{{ $message }}</div>
                                        @enderror
                                    </div>
                                </div>
                                <div class="col-md-4 crypto-address">
                                    <div class="form-group">
                                        <label for="inputEmail" class="font-weight-bold">{{ t('to_address') }}</label>
                                        <input type="text" class="form-control grey-rounded-border crypto-address"
                                               name="to_address" readonly
                                               id="to_address"
                                               value="{{ $address }}">
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
                                               class="font-weight-bold exchange-fee-percent">{{ t('from_fee') }}
                                            %</label>
                                        <input type="number" class="form-control grey-rounded-border commission"
                                               name="exchange_fee_percent" readonly step="any" min="0"
                                               id="exchange_fee_percent" value="{{$operation->getCryptoPercentCommission($commissions ?? null) }}">
                                        @error('exchange_fee_percent')
                                        <div class="error text-danger">{{ $message }}</div>
                                        @enderror
                                    </div>
                                </div>
                                <div class="col-md-3">
                                    <div class="form-group">
                                        <label for="inputEmail"
                                               class="font-weight-bold exchange-fee">{{ t('from_fee') }}</label>
                                        <input type="number" class="form-control grey-rounded-border commission"
                                               readonly step="any" min="0"
                                               name="exchange_fee"
                                               id="exchange_fee" value="{{ $operation->getCryptoFixedCommission($commissions ?? null) }}">
                                        @error('exchange_fee')
                                        <div class="error text-danger">{{ $message }}</div>
                                        @enderror
                                    </div>
                                </div>
                                <div class="col-md-3 from-fee-minimum">
                                    <div class="form-group">
                                        <label for="inputEmail"
                                               class="font-weight-bold exchange-fee-min">{{ t('blockchain_fee') }}</label>
                                        <input type="number" class="form-control grey-rounded-border commission"
                                               name="blockchain_fee" readonly step="any" min="0"
                                               id="exchange_fee_min" value="{{ !empty($commissions->blockchain_fee) ? formatMoney($commissions->blockchain_fee, $commissions->currency) : '' }}">
                                        @error('blockchain_fee')
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

</div>

