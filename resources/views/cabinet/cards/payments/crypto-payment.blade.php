<form method="POST" action="{{ route('wallester.confirm.crypto.payment') }}"
      class="d-flex flex-column justify-content-start">
    @csrf
    <input type="hidden" name="id" value="{{ $account->id }}">
    <div class="d-flex flex-column justify-content-start mt-4" id="walletDropdown">
        <h5>{{ t('select_wallet') }}</h5>
        <select class="col-lg-4 mt-3" id="cryptoWallet" name="fromWallet" style="border: 1px solid #bfb7b7;" required>
            <option value="">{{ t('select') }} ...</option>
            @foreach($wallets as $id => $name)
                <option value="{{ $id }}">{{ $name }}</option>
            @endforeach
        </select>
        <p class="error-summary text-danger d-none"></p>

        @error('fromWallet')
        <div class="error text-danger">{{ $message }}</div>
        @enderror
    </div>
    <div class="mt-5 ml-1">
        <button class="btn btn-lg btn-primary themeBtn register-buttons mb-4 mb-md-0 mr-3 confirmWalletCardOrderByCrypto"
                data-crypto-payment-wallet-chosen="{{ route('wallester.show.crypto.payment.summary') }}"
                type="button"> {{ t('wallester_card_btn_next') }} </button>
    </div>


    <div class="row pl-3 pr-3 summary-crypto-payment d-none">
        <div class="m-2 pl-2 pt-1 pt-sm-0 cursor-pointer" id="prevPageCryptoPayment">
            <a style="color: black">
                <i class="fa fa-arrow-left" aria-hidden="true"></i>
            </a>
        </div>
        <div class="col-md-5">
            <div class="row py-2 px-0 mt-3 wallet-border-pink p-3">
                <div class="col-sm-5 text-left">
                    <h6 class="font-weight-bold mb-0 mt-2">{{ t('amount_euro') }}</h6>
                    <p class="card-amount-euro"></p>
                    <h6 class="font-weight-bold mb-0 mt-2">{{ t('send_crypto_time') }}</h6>
                    <p>{{ t('withdraw_crypto_instantly') }}</p>

                    <h6 class="font-weight-bold mb-0 mt-2">{{ t('send_crypto_withdrawal_fee') }}</h6>
                    <span class="withdraw-fee"></span>
                    <h6 class="font-weight-bold mb-0 mt-2">{{ t('send_crypto_blockchain_fee') }}</h6>
                    <p class="blockchain-fee"></p>
                </div>
                <div class="col-sm-7 text-left">
                    <h6 class="font-weight-bold mb-0 mt-2">{{ t('amount') }}</h6>
                    <span class="card-amount-crypto"></span>
                    <h6 class="font-weight-bold mb-0 mt-2">{{ t('send_crypto_transaction_limit') }}</h6>
                    <p class="trx-limit"></p>
                    <h6 class="font-weight-bold mb-0 mt-2">{{ t('send_crypto_available_limit') }}</h6>
                    <p class="available-limit"></p>
                </div>
            </div>
        </div>
        <div class="mt-5 ml-1">

        <button class="btn btn-lg btn-primary themeBtn register-buttons mb-4 mb-md-0 mr-3"
                type="submit"> {{ t('confirm') }} </button>
        </div>
    </div>
</form>
