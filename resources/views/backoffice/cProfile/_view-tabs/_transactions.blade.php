<div id="transactions" class="container-fluid tab-pane fade"><br>
    @if($profile->cryptoAccountDetail->count())
        @foreach($profile->cryptoAccountDetail as $cryptoAccountDetail)
            @if($cryptoAccountDetail->is_hidden == 0)
                <div class="col-md-12 mb-5">
                    <div class="row">
                        <div class="col-md-6">
                            <div class="wallet-style wallet-btc btc mb-4" style="max-width: 90%">
                                <div class="label">
                                    <img src="{{ asset('/cratos.theme/images/' . (\App\Enums\Currency::IMAGES[$cryptoAccountDetail->coin] ?? '')) }}" width="35px" alt="">
                                </div>
                                <div class="d-block">
                                    <h3>{{ strtoupper($cryptoAccountDetail->coin) }} {{ $cryptoAccountDetail->account->amount }}</h3>
                                    <h4>€ 0.00</h4>
                                    <h6>$ 0.00</h6>
                                    <div class="mb-3 w-100 mt-4">
                                        <button id="{{ $cryptoAccountDetail->coin }}" class="btn btn-light" onclick="copyText(this.id)">
                                            {{ $cryptoAccountDetail->address }}
                                            <i class="fa fa-copy" aria-hidden="true"></i>
                                        </button>
                                    </div>
                                    <a class="btn btn-dark btnWhiteSpace p-1 m-2" style="border-radius: 30px" href="" type="submit">{{ t('block') }}</a>
                                    <a class="btn btn-primary themeBtn btnWhiteSpace p-1 m-2" style="border-radius: 30px" type="submit">{{ t('ui_send') }}</a>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="row">
                                <div class="col-md-12">
                                    <span class="textBold">{{ t('created_on') }}</span> <br><br> {{ $cryptoAccountDetail->updated_at }}
                                </div>
                                <div class="col-md-12 mt-5">
                                    <span class="textBold">{{ t('wallet_provider') }}</span> <br><br> {{ $cryptoAccountDetail->account->name }}
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            @endif
        @endforeach
        @include('backoffice.partials.transactions.index', ['client' => false])
    @else
        <h1>{{ t('ui_you_have_not_wallet') }}</h1>
    @endif
</div>
