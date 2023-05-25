<form action="">
    <div class="row col-md-8 pt-2">
        <input class="date-inputs display-sell" name="from" id="from" value="{{ request()->from }}"
               placeholder="From date" autocomplete="off">
        <input class="date-inputs display-sell" name="to" id="to" value="{{ request()->to }}" placeholder="To date"
               autocomplete="off">
        <button class="btn btn-lg btn-primary themeBtn register-buttons mb-4 mb-md-0"
                type="submit">{{ t('find') }}
        </button>
    </div>
</form>

<div class="row common-shadow-theme p-3 w-100  col-md-8 mt-5">
    <div class="col-md-12">
        <div class="row">
            <div class="col-md-12 text-left">
                <div class="col-md-12">
                    <div class="row">
                        <div class="col-md-2 mt-2 textBold breakWord">{{ t('collected_crypto_fees_currency') }}</div>
                        <div class="col-md-3 mt-2 textBold breakWord ">{{ t('collected_crypto_fees_total_collected') }}</div>
                        <div class="col-md-4 mt-2 textBold breakWord">{{ t('collected_crypto_fees_ready_for_withdrawal') }}</div>
                        <div class="col-md-3 mt-2 textBold breakWord"></div>
                    </div>
                </div>

                <div class="col-md-12 mt-3">
                    @if(!$totalCollected->isEmpty())
                        @foreach($totalCollected as $currency => $totalCollectedFeeAmount)
                            <div class="row">
                                <div class="col-md-2 mt-2">{{ $currency }}</div>
                                <div class="col-md-3 mt-2">{{ $totalCollectedFeeAmount }}</div>
                                <div class="col-md-4 mt-2">{{ $readyForWithdrawal[$currency] ?? 0 }}</div>
                                <div class="col-md-3 mt-2">
                                    @if($currentAdmin->hasPermissionInAnyProject([\App\Enums\BUserPermissions::WITHDRAW_COLLECTED_CRYPTO_FEES]))
                                        <button
                                            class="btn btn-lg btn-primary themeBtn register-buttons mb-4 mb-md-0 openWithdrawModalBtn"
                                            type="submit"
                                            data-withdrawal-amount="{{ $readyForWithdrawal[$currency] ?? 0 }}"
                                            data-toggle="modal" data-currency="{{ $currency }}"
                                            data-target="#withdrawCollectedCryptoFee">{{ t('withdraw') }}
                                        </button>
                                    @endif
                                </div>
                            </div>
                        @endforeach
                    @else
                        <div class="row">
                            <div class="col-md-2 mt-2 text-center">0</div>
                            <div class="col-md-3 mt-2 text-center">0</div>
                            <div class="col-md-4 mt-2 text-center">0</div>
                        </div>
                    @endif
                    @include('backoffice.collectedCryptoFee._withdraw')
                </div>
            </div>
        </div>
    </div>
</div>
