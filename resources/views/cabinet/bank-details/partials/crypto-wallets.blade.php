<div class="col-md-12 pl-0 mt-5">
    <h2 style="display: inline;margin-right: 25px;">{{ t('crypto_wallets') }}</h2>
    <button class="btn mb-2" style="border-radius: 25px;background-color: #000;color: #fff" data-toggle="modal" id="addCryptoDetail" data-target="#cryptoDetail">Add</button>
</div>

<div class="col-md-12">
    <div class="row" id="walletsSection">
        @if($accountsCrypto->count())
        @foreach($accountsCrypto as $cryptoAccount)
        <div class="col-md-6 col-lg-4 pl-0 pr-0 pr-sm-4 d-flex">
            <div class="crypto-wallets-style w-100">
                <div style="display: inline-block;width: 100%;">
                    <div style="width: 49%;float: left">

                        <h6 class="textBold" style="font-size: 20px">{{  $cryptoAccount->currency  }} <h6 class="textBold">{{ $cryptoAccount->cryptoAccountDetail->label}} </h6> </h6>
                    </div>
                </div><br><br>
                <p class="date-styles">
                    <span>{{ t('ui_cprofile_verified') }}: {{ $cryptoAccount->cryptoAccountDetail->verified_at->timezone($timezone) }}</span>
                </p>
            </div>
        </div>
        @endforeach
        @else
        <h6 class="mt-3">{{ t('have_not_crypto_wallets_yet') }}</h6>
        @endif
    </div>
</div>
