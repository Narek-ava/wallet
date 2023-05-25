<div id="wallets" class="container-fluid tab-pane fade pl-0 mt-5">
    @if($profile->cryptoAccountDetail->count())
        <h2>Crypto Wallets</h2>
        <div class="row mt-4">
            @foreach($profile->cryptoAccountDetail as $cryptoAccountDetail)
                <div class="col-md-3 crypto-wallet pl-0">
                    <div class="common-shadow-theme wallet-btc btc mb-4 ml-0" style="max-width: 90%">
                        <div class="label">
                            <img
                                src="{{ asset('/cratos.theme/images/' . (\App\Enums\Currency::IMAGES[$cryptoAccountDetail->coin] ?? '')) }}"
                                width="35px" alt="">
                        </div>
                        <div class="d-block">
                            <h5 style="cursor: pointer" >
                                {{ strtoupper($cryptoAccountDetail->coin) }} {{ $cryptoAccountDetail->account->displayAvailableBalance() }}
                            </h5>
                            <p>{{$cryptoAccountDetail->getWalletBalance().' ' . t('ui_available_balance_in_wallet')}}</p>
                            <a class="btn btn-primary themeBtn btnWhiteSpace show-wallet-details mt-2"
                               href="{{ route('backoffice.profile.wallet', $cryptoAccountDetail->id) }}"
                               style="border-radius: 30px" type="button">{{ t('profile_wallets_details') }}</a>
                        </div>
                    </div>
                </div>
            @endforeach
        </div>
    @else
        {{ t('profile_wallets_no_wallets') }}
    @endif

    <br><br>
    @if(config('cratos.enable_fiat_wallets'))
        <h2>{{ t('ui_fiat_wallets') }}</h2>
        <div class="row mt-4">
            @if(!empty($fiatWallets))
            @foreach($fiatWallets as $fiatWallet)
                <div class="col-md-3 crypto-wallet pl-0">
                    <div class="common-shadow-theme wallet-btc btc mb-4 ml-0" style="max-width: 90%">
                        <div class="label">
                            <img
                                src="{{ asset('/cratos.theme/images/' . (\App\Enums\Currency::IMAGES[$fiatWallet->currency] ?? '')) }}"
                                width="35px" alt="">
                        </div>
                        <div class="d-block">
                            <h5 style="cursor: pointer">
                                {{ $fiatWallet->currency }} {{ $fiatWallet->displayAvailableBalance() }}
                            </h5>
                            <a class="btn btn-primary themeBtn btnWhiteSpace show-wallet-details mt-2"
                               href="{{ route('backoffice.profile.fiat.wallet', $fiatWallet->id) }}"
                               style="border-radius: 30px" type="button">{{ t('profile_wallets_details') }}</a>
                        </div>
                    </div>
                </div>
            @endforeach
            @endif
        </div>
    @endif
</div>

<script src="/js/backoffice/wallets.js"></script>


