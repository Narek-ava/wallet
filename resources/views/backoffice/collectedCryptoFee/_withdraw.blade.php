<!-- Modal -->
<div class="modal fade" id="withdrawCollectedCryptoFee" tabindex="-1" aria-labelledby="exampleModalLabel"
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
                    @foreach($notCollectedCryptoFeeTransactions as $currency => $notCollectedCryptoFeeTransactionsArr)
                        <form action="{{ route('make.withdraw.collected.fees') }}"
                              id="collectedFeeWithdraw{{ $currency }}" class="transactionForm" method="post">
                            @csrf
                            <input type="hidden" name="project_id" value="{{ $selectedProject->id }}">

                            <div class="row">
                                <div class="col-md-3">
                                    <div class="form-group">
                                        <label class="font-weight-bold">{{ t('amount') }}</label>
                                        <input readonly type="number" class="form-control grey-rounded-border" data-get-fee="{{ route('get.not.collected.transactions.fee') }}"
                                               name="amount" min="0"
                                               onkeypress="return (event.charCode == 8 || event.charCode == 0 || event.charCode == 13 || event.charCode == 46) ? null : event.charCode >= 48 && event.charCode <= 57" step="any">
                                        @error('amount')
                                        <div class="error text-danger">{{ $message }}</div>
                                        @enderror
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label class="font-weight-bold">{{ t('address') }}</label>
                                        <input type="text" class="form-control grey-rounded-border" name="toAddress" required>
                                        @error('toAddress')
                                        <div class="error text-danger">{{ $message }}</div>
                                        @enderror
                                    </div>
                                </div>

                                <div class="col-md-3">
                                    <div class="form-group">
                                        <label for="currency" class="font-weight-bold">{{ t('currency') }}</label>
                                        <input class="form-control grey-rounded-border currency"
                                               id="currency" name="currency" value="" readonly>
                                        @error('currency')
                                        <div class="error text-danger">{{ $message }}</div>
                                        @enderror
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <button class="btn themeBtn round-border" type="submit">{{ t('withdraw') }}</button>
                                </div><br>
                                <div class="col-12">
                                    <div class="float-right">
                                        <p>
                                            <a class="font-weight-bold">{{ t('fee') }}: </a> <a class="providerFeeAmount"> {{ $feesForWithdraw[$currency]['feeAmount'] }} </a>
                                        </p>
                                        <p>
                                            <a class="font-weight-bold">{{ t('blockchain_fee') }}: </a> <a class="blockchainFee"> {{ $feesForWithdraw[$currency]['blockchainFee'] }}</a>
                                        </p>
                                    </div>
                                </div>
                                <div class="row mt-5 disabled" style="min-width: 100%">
                                    @foreach($notCollectedCryptoFeeTransactionsArr as $notCollectedCryptoFeeTransaction)
                                        <div class="row col-md-12 collectedTransactions">
                                            <input type="checkbox" class="transaction-checkbox" name="checkedTransactions{{$currency}}[]" checked data-amount="{{ $notCollectedCryptoFeeTransaction->amount }}"
                                                   value="{{ $notCollectedCryptoFeeTransaction->id }}">
                                            <div
                                                class="col-md-3">{{ $notCollectedCryptoFeeTransaction->clientAccount->name ?? '' }}</div>
                                            <div
                                                class="col-md-20">{{ $notCollectedCryptoFeeTransaction->amount }}</div>
                                            <div
                                                class="col-md-1">{{ $notCollectedCryptoFeeTransaction->currency }}</div>
                                            <div
                                                class="col-md-2">{{ \App\Enums\CollectedCryptoFee::getName($notCollectedCryptoFeeTransaction->is_collected) }}</div>
                                            <div
                                                class="col-md-3">{{ $notCollectedCryptoFeeTransaction->created_at->format('d-m-Y') }}</div>
                                        </div>
                                    @endforeach
                                </div>
                            </div>
                        </form>
                    @endforeach
                </div>
            </div>
        </div>
    </div>
</div>
