<!-- Modal -->
<div class="modal fade" id="withdrawCryptoModal" tabindex="-1" role="dialog" aria-labelledby="blockWalletTitle" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered" role="document" style="max-width: 500px">
        <div class="modal-content">
            <div class="modal-header border-0">
                <h5 class="modal-title text-dark"
                    id="blockWalletLongTitle">{{ t('ui_withdrawal_from_wallet') }}</h5>
                <button type="button" class="close text-dark" data-dismiss="modal"
                        aria-label="Close">
                    <span aria-hidden="true" class="text-dark">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <form action="{{ route('backoffice.wallets.withdraw.crypto') }}" method="post">
                    @csrf
                    <div class="row">
                        <div class="col-md-12">
                            <div class="row">
                                <div class="col-md-12">
                                    <div class="form-group">
                                        <p>{{ t('ui_from_address') }}</p>
                                        <input class="form-control from-wallet-address" type="text" name="from_wallet"
                                               value="{{ $cryptoAccountDetail->address }}"
                                               style="border: 1px solid grey; border-radius: 10px;">
                                        @error('from_wallet')
                                        <div class="error text-danger">{{ $message }}</div>
                                        @enderror
                                        <input class="form-control"  hidden type="text" name="cProfile_id"
                                               value="{{ $profile->id }}">
                                    </div>
                                    <div class="form-group">
                                        <p>{{ t('ui_to_wallet_address') }}</p>
                                        <input class="form-control" type="text" name="to_wallet"
                                               placeholder="To wallet" style="border: 1px solid grey; border-radius: 10px;" value="{{ old('to_wallet') }}">
                                        @error('to_wallet')
                                        <div class="error text-danger">{{ $message }}</div>
                                        @enderror
                                    </div>
                                    <div class="form-group">
                                        <p>Amount</p>
                                        <input id="{{ $cryptoAccountDetail->id }}" class="form-control withdraw-amount" type="text" name="amount"
                                               value="{{ old('amount') }}"
                                               onkeypress="getWithdrawFee(this.id)"
                                               onkeyup="getWithdrawFee(this.id)"
                                               style="border: 1px solid grey; border-radius: 10px;">
                                        @error('amount')
                                        <div class="error text-danger">{{ $message }}</div>
                                        @enderror
                                    </div>

                                    <h6 class="d-inline-block">{{ t('ui_cabinet_deposit_fee') }}:</h6>
                                    <span class="withdraw-fee">-</span>
                                    <span class="withdraw-fee-percent">-</span> <br>
                                    <input type="hidden" name="crypto_account_detail_id" value="{{ $cryptoAccountDetail->id }}">
                                    <button type="submit" class="themeBtn block-wallet btn btn-dark mt-4 w-25">{{ t('ui_send') }}</button>
                                </div>
                            </div>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

