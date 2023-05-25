<!-- Modal -->
<div class="modal fade" id="createProviderWithdrawOperation" tabindex="-1" aria-labelledby="exampleModalLabel"
     aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content" style="border-radius: 31px; padding: 17px">
            <div class="modal-header">
                <h4 class="col-lg-auto" id="exampleModalLabel">{{ t('transaction_details') }}</h4>
<button type="button" class="close" data-dismiss="modal" aria-label="Close">
    <span aria-hidden="true">&times;</span>
</button>
</div>
<div class="modal-body">
    <div>
        <form action="{{ route('backoffice.make.provider.operation',[ 'account' => $account ]) }}" method="post">
            @csrf
            <div class="row">
                <div class="col-md-3">
                    <div class="form-group">
                        <label for="date" class="font-weight-bold">{{ t('date') }}</label>
                        <input name="date" data-provide="datepicker" data-date-format="yyyy-mm-dd" id="date"
                               class="form-control grey-rounded-border" autocomplete="off" value="{{ Carbon\Carbon::now()->format('Y-m-d') }}">
                        @error('date')
                        <div class="error text-danger">{{ $message }}</div>
                        @enderror
                    </div>
                </div>

                <div class="col-md-3">
                    <div class = "form-group">
                        <label for="transaction_type"
                               class="font-weight-bold">{{ t('transaction_type') }}</label>
                        <select id="transaction_type"
                                class="form-control grey-rounded-border transaction-type"
                                name="transaction_type">
                            <option value="{{\App\Enums\TransactionType::PROVIDER_WITHDRAW_TRX }}">{{ t(\App\Enums\TransactionType::NAMES[\App\Enums\TransactionType::PROVIDER_WITHDRAW_TRX ]) }}</option>
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
                        <label for="from_type" class = "font-weight-bold">{{ t('from_type') }}</label>
                        <select id="from_type" class = "form-control grey-rounded-border"
                                name="from_type" readonly>
                            <option value="{{ $account->provider->provider_type }}">{{ App\Enums\Providers::NAMES[$account->provider->provider_type] }}</option>
                        </select>
                        @error('from_type')
                        <div class="error text-danger">{{ $message }}</div>
                        @enderror
                    </div>
                </div>

                <div class="col-md-3">
                    <div class="form-group">
                        <label for="from_account" class="font-weight-bold">{{ t('account') }}</label>
                        <select id="from_account" class="form-control grey-rounded-border"
                                name="from_account" readonly>
                            <option value="{{ $account->id }}">{{ $account->provider->name  }}</option>
                        </select>
                        @error('from_account')
                        <div class="error text-danger">{{ $message }}</div>
                        @enderror
                    </div>
                </div>

                <div class="col-md-3">
                    <div class="form-group">
                        <label for="to_type" class="font-weight-bold">{{ t('to_type') }}</label>
                        <select id="to_type"  class="form-control grey-rounded-border to-type"
                                onchange="getFromAccountsByType('to_account_withdraw')"
                                name="to_type" >
                            <option value="" hidden>{{ t('ui_cabinet_default_select_option_text') }}</option>
                            @foreach(App\Enums\Providers::ONLY_PROVIDER_NAMES as $key => $providerType)
                                <option value="{{ $key }}" >{{ $providerType }}</option>
                            @endforeach
                        </select>
                        @error('to_type')
                        <div class="error text-danger">{{ $message }}</div>
                        @enderror
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="form-group">
                        <label for="to_account" class="font-weight-bold">{{ t('account') }}</label>
                        <select id="to_account_withdraw" class="form-control grey-rounded-border"
                                name="to_account" disabled>
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
                        <label for="currency" class="font-weight-bold">{{ t('currency') }}</label>
                        <input class="form-control grey-rounded-border currency"
                               id="currency" name="currency" value="{{ $account->currency }}" readonly>
                        @error('currency')
                        <div class="error text-danger">{{ $message }}</div>
                        @enderror
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="form-group">
                        <label for="amount" class="font-weight-bold">{{ t('amount') }}</label>
                        <input type="number" class="form-control grey-rounded-border"
                               name="amount" min="0"
                               onkeypress="return (event.charCode == 8 || event.charCode == 0 || event.charCode == 13 || event.charCode == 46) ? null : event.charCode >= 48 && event.charCode <= 57"
                               id="amount" step="any">
                        @error('amount')
                        <div class="error text-danger">{{ $message }}</div>
                        @enderror
                    </div>
                </div>
            </div><br>
            <div class="row">
                <div class="col-md-6">
                    <button class="btn themeBtn round-border add-trx-btn"
                            type="submit">{{ t('save') }}</button>
                </div>
            </div>
        </form>
    </div>
</div>
</div>
</div>
</div>


