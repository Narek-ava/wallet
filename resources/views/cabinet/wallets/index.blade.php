@extends('cabinet.layouts.cabinet')
@section('title', t('title_wallets_page'))
@section('tiktokpixel')
    <script>
        !function (w, d, t) {
            w.TiktokAnalyticsObject = t;
            var ttq = w[t] = w[t] || [];
            ttq.methods = ["page", "track", "identify", "instances", "debug", "on", "off", "once", "ready", "alias", "group", "enableCookie", "disableCookie"], ttq.setAndDefer = function (t, e) {
                t[e] = function () {
                    t.push([e].concat(Array.prototype.slice.call(arguments, 0)))
                }
            };
            for (var i = 0; i < ttq.methods.length; i++) ttq.setAndDefer(ttq, ttq.methods[i]);
            ttq.instance = function (t) {
                for (var e = ttq._i[t] || [], n = 0; n < ttq.methods.length; n++) ttq.setAndDefer(e, ttq.methods[n]);
                return e
            }, ttq.load = function (e, n) {
                var i = "https://analytics.tiktok.com/i18n/pixel/events.js";
                ttq._i = ttq._i || {}, ttq._i[e] = [], ttq._i[e]._u = i, ttq._t = ttq._t || {}, ttq._t[e] = +new Date, ttq._o = ttq._o || {}, ttq._o[e] = n || {};
                var o = document.createElement("script");
                o.type = "text/javascript", o.async = !0, o.src = i + "?sdkid=" + e + "&lib=" + t;
                var a = document.getElementsByTagName("script")[0];
                a.parentNode.insertBefore(o, a)
            };

            ttq.load('C4MAMKD1KC6QQ9D187O0');
            ttq.page();
        }(window, document, 'ttq');
    </script>
@endsection
@section('content')
    <div class="row mb-5">
        <div class="col-md-12">
            <div class="row mb-3">
                <div class="col-md-12">
                    <h2 class="mb-3 large-heading-section page-title">Total Balance</h2>
                    <div class="row">
                        <div class="col-md-5 d-flex justify-content-between">
                            <div class="balance">
                                {!! t('ui_personal_cryptowallet') !!}
                            </div>
                        </div>
                        @include('cabinet.partials.notification', ['notify' => getNotification(\Illuminate\Support\Facades\Auth::id()), 'admin' => false])
                    </div>
                </div>
            </div>
        </div>
    </div>

    @if(session()->has('error'))
        <div class="alert alert-error alert-dismissible fade show" role="alert" id="errorMessageAlert">
            <h4>{{ session()->get('error') }}</h4>
            <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                <span aria-hidden="true">&times;</span>
            </button>
        </div>
    @endif

    @error('cryptocurrency')
    <div class="alert alert-danger alert-dismissible fade show" role="alert" id="successMessageAlert">
        <h4>{{ $message }}</h4>
        <button type="button" class="close" data-dismiss="alert" aria-label="Close">
            <span aria-hidden="true">&times;</span>
        </button>
    </div>
    @enderror

    @include('cabinet.wallets._withdraw-wire-popup')

    @if($cProfile->status == \App\Enums\CProfileStatuses::STATUS_ACTIVE && $cProfile->cUser->email_verified_at)

        @if(config('cratos.enable_fiat_wallets'))
            <div class="row mt-5 mb-5">
                <div class="col-md-12">
                    <div class="row">
                        <div class="col m-2 pl-2 pt-1 pt-sm-0" style="max-width: 80px;">
                            <h4 class="d-inline-block mb-0">{{ t('ui_fiat') }}</h4>
                        </div>

                        <div class="col mt-1 pl-0 pl-sm-2">
                            <button type="button" class="btn btn-sm btn-dark badge-pill" data-toggle="modal"
                                    data-target=".bd-example-modal-sm-fiat"
                                    style="width: 90px;border-radius: 20px">{{ t('add') }}
                            </button>
                            @include('cabinet.wallets._add_fiat_wallet')
                        </div>
                    </div>
                </div>
            </div>
            <div class="row mb-5 ml-0">
                <div id="fiatsTablist" class="col-md-12 p-0 wallet-tablist">
                    <a class="wallet-tab btn text-dark pl-4 pr-4 mb-2 active" data-fiat="all">
                        <span>{{ t('operation_type_all') }}</span>
                    </a>
                    @foreach($fiatWallets as $fiatWallet)
                        <a class="wallet-tab btn text-dark pl-4 pr-4 mb-2"
                           data-fiat="{{ strtolower($fiatWallet->currency) }}">
                            <span>{{ strtoupper($fiatWallet->currency) }}</span>
                        </a>
                    @endforeach
                </div>
            </div>
            <div class="row mt-5 mb-5 wallets">
                <div class="col-md-12">
                    <div class="row">
                        <div class="col-md-12">
                            <div class="row tab-content">

                                @foreach($fiatWallets as $fiatWallet)
                                    <div id="{{ $fiatWallet->id }}"
                                         class="col-md-4">
                                        <div class="common-shadow-theme wallet-eur btc mb-4 ml-0 mr-0"
                                             data-fiat="{{strtolower($fiatWallet->currency)}}" style="max-width: 100%">
                                            <div class="label">
                                                <img
                                                    src="{{ asset('/cratos.theme/images/' . (\App\Enums\Currency::IMAGES[$fiatWallet->currency] ?? '')) }}"
                                                    width="35px" alt="">
                                            </div>
                                            <div class="d-block">
                                                <h3>{{ strtoupper($fiatWallet->currency) }} {{$fiatWallet->displayAvailableBalance()}}</h3>
                                                <div class="mb-3 w-100 mt-4">
                                                    <a href="{{ route('cabinet.wallets.fiat.show', $fiatWallet->id) }}"
                                                       class="text-dark border-bottom" style="text-decoration: none">Wallet
                                                        details</a>
                                                </div>

                                                <a class="btn btn-primary themeBtn btnWhiteSpace m-2"
                                                   style="border-radius: 30px"
                                                   href="{{ route('cabinet.fiat.top_up', $fiatWallet->id) }}"
                                                >{{ t('ui_top_up') }}</a>

                                                @if($hasB2CProvider)
                                                    <a class="btn btn-primary themeBtn btnWhiteSpace m-2  {{ $fiatWallet->displayAvailableBalance() <= 0 ? 'disabled' : '' }}"
                                                       style="border-radius: 30px"
                                                       href="{{ route('cabinet.fiat.withdraw.wire', $fiatWallet->id) }}"
                                                    >{{ t('wallet_detail_withdraw') }}</a>
                                                @endif


                                            </div>
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        @endif

        <div class="row mt-5 mb-5">
            <div class="col-md-12">
                <div class="row">
                    <div class="col m-2 pl-2 pt-1 pt-sm-0" style="max-width: 80px;">
                        <h4 class="d-inline-block mb-0">{{ t('ui_coins') }}</h4>
                    </div>

                    <div class="col mt-1 pl-0 pl-sm-2">
                        <button type="button" class="btn btn-sm btn-dark badge-pill" data-toggle="modal"
                                data-target=".bd-example-modal-sm"
                                style="width: 90px;border-radius: 20px">{{ t('add') }}
                        </button>
                        <div class="modal fade bd-example-modal-sm mt-5 m-auto" tabindex="-1" role="dialog"
                             aria-labelledby="mySmallModalLabel" aria-hidden="true">
                            <div class="modal-dialog modal-dialog-centered" style="max-width: 500px;">
                                <div class="modal-content modal-content-centered">
                                    <div class="modal-header">
                                        <h5 class="modal-title">{{ t('provider_add_wallet_new') }}</h5>
                                        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                                            <span aria-hidden="true">×</span>
                                        </button>
                                    </div>
                                    <form id="addWalletForm" action="{{ route('cabinet.wallets.add.wallet') }}"
                                          method="post">
                                        <div class="modal-body">
                                            <p>{{ t('ui_choose_cryptowallet') }}</p>
                                            @csrf
                                            <label for="inputEmail"
                                                   class="">{{ t('transaction_history_detail_cryptocurrency') }}</label>
                                            <select id="selectCoin" class="w-100 mb-3" name="cryptocurrency"
                                                    onchange="enableAddCurrencyButton()"
                                                    style="border: 1px solid #bfb7b7;">
                                                <option value="">{{ t('ui_select_coin') }}</option>
                                                @foreach($allowedCoinsForAccount as $key => $allowedCoinForAccount)
                                                    <option
                                                        value="{{  $key }}">{{\App\Enums\Currency::FULL_NAMES[$allowedCoinForAccount] . '(' . strtoupper($allowedCoinForAccount) . ')'}}</option>
                                                @endforeach
                                            </select>
                                        </div>
                                        <div class="modal-footer">
                                            <button type="submit" class="btn themeBtn btnWhiteSpace addCurrency"
                                                    disabled>Add
                                            </button>
                                        </div>
                                    </form>
                                </div>
                            </div>
                        </div>


                    </div>
                </div>
            </div>
        </div>
        <div class="row mb-5 ml-0">
            <div id="cryptocurrenciesTablist" class="col-md-12 p-0 wallet-tablist">
                <a class="wallet-tab btn text-dark pl-4 pr-4 mb-2 active" data-cryptocurrency="all">
                    <span>{{ t('operation_type_all') }}</span>
                </a>
                @foreach($cryptoAccountDetails as $cryptoAccountDetail)
                    @if($cryptoAccountDetail->is_hidden == 0)
                        <a class="wallet-tab btn text-dark pl-4 pr-4 mb-2"
                           data-cryptocurrency="{{ strtolower($cryptoAccountDetail->coin) }}">
                            <span>{{ strtoupper($cryptoAccountDetail->coin) }}</span>
                        </a>
                    @endif
                @endforeach
            </div>
        </div>
        <div class="row mt-5 mb-5 wallets">
            <div class="col-md-12">
                <div class="row">
                    <div class="col-md-12">
                        <div class="row tab-content">

                            @foreach($cryptoAccountDetails as $cryptoAccountDetail)
                                @if($cryptoAccountDetail->is_hidden == 0)
                                    <div id="{{ $cryptoAccountDetail->coin }}"
                                         class="col-md-4 {{ $cryptoAccountDetail->blocked ? 'opacityClass' : '' }}">
                                        @if($cryptoAccountDetail->blocked)
                                            <div
                                                style="position:absolute;z-index: 5;font-size: 25px;text-align: center; bottom: 0">
                                                {!! t('ui_wallet_blocked') !!}
                                            </div>
                                        @endif
                                        <div class="common-shadow-theme wallet-btc btc mb-4 ml-0 mr-0"
                                             data-cryptocurrency="{{strtolower($cryptoAccountDetail->coin)}}"
                                             style="max-width: 100%">
                                            <div class="label">
                                                <img
                                                    src="{{ asset('/cratos.theme/images/' . (\App\Enums\Currency::IMAGES[$cryptoAccountDetail->coin] ?? '')) }}"
                                                    width="35px" alt="">
                                            </div>
                                            <div class="d-block">
                                                <h3>{{ strtoupper($cryptoAccountDetail->coin) }} {{ $cryptoAccountDetail->account->displayAvailableBalance() }}</h3>
                                                <a href="{{ route('cabinet.wallets.show', $cryptoAccountDetail->id) }}"
                                                   class="text-dark border-bottom" style="text-decoration: none">Wallet
                                                    details</a>
                                                <div class="mb-3 w-100 mt-4">
                                                    <button id="{{ $cryptoAccountDetail->coin }}" class="btn btn-light"
                                                            onclick="copyText(this)">
                                                        <i class="fa fa-copy" aria-hidden="true"></i>
                                                    </button>
                                                    <input class="w-75 text-secondary border-bottom-0" type="text"
                                                           value="copy address">
                                                    <input id="{{ 'text' . $cryptoAccountDetail->coin }}"
                                                           class="w-75"
                                                           style="position:absolute;left:-10000px;top:-10000px"
                                                           type="text"
                                                           value="{{ $cryptoAccountDetail->address }}">

                                                </div>
                                                @if(!$cryptoAccountDetail->blocked)
                                                    <a class="btn btn-primary themeBtn btnWhiteSpace m-2"
                                                       style="border-radius: 30px"
                                                       href="{{ route('cabinet.wallets.top_up_crypto', $cryptoAccountDetail->id) }}"
                                                       type="submit">{{ t('ui_top_up') }}</a>

                                                    <a class="btn btn-primary themeBtn btnWhiteSpace m-2  {{ $cryptoAccountDetail->account->displayAvailableBalance() <= 0 ? 'disabled' : '' }}"
                                                       style="border-radius: 30px"
                                                       href="{{ route('cabinet.wallets.send.crypto', $cryptoAccountDetail->id) }}"
                                                       type="submit">{{ t('wallet_detail_withdraw') }}</a>

                                                @endif
                                            </div>
                                        </div>
                                    </div>
                                @endif
                            @endforeach
                        </div>
                    </div>
                </div>
            </div>
        </div>

    @else
        <div class="row mt-5 mb-4">
            <div class="col-md-12">
                <div class="row pl-1 pl-sm-0">
                    <div class="col-md-1 m-2 pl-2">
                        <h4 class="d-inline-block">{{ t('ui_coins') }}</h4>
                    </div>

                    <div class="col-md-12 ml-2 mb-0 pl-2">
                        <h5 class="d-inline-block font-weight-bold">{{ t('ui_wallet_ready') }}</h5>
                        <div class="mt-2">{{ t('ui_access_team') }}</div>
                    </div>
                </div>
            </div>
        </div>
        @php



            @endphp
        <div class="row pt-4 pl-3 wallet-instruction-items pr-3 pr-md-0">
            <div class="col-md pl-0 pr-0">
                <div
                    class="common-shadow-theme wallet-instruction-item row h-100 m-0 p-3 pt-4 pb-4 {{ $steps['stepState_1'] }}">
                    <div class="col-12">
                        <div class="textBold activeLevel"></div>
                        <div class="textBold inactiveLevel"></div>
                        <h2 class="mb-3">{{ t('ui_email_verification') }}</h2>
                    </div>
                    <div class="col-12">
                        @if($isEmailVerificationSent)
                            <p>{{ t('ui_verify_email') }}</p>
                        @else
                            <p>{{ t('ui_send_verify_email') }}</p>
                        @endif
                    </div>
                    <div class="col-12 mt-auto">
                        <a href="{{ route('cabinet.resend.email.verification', ['cUser' => json_decode(auth()->user())->id]) }}"
                           class="btn btn-lg btn-primary themeBtn">
                            @if($isEmailVerificationSent)
                                {{ t('ui_resend_email') }}
                            @else
                                {{ t('ui_send_email') }}
                            @endif
                        </a>
                    </div>
                </div>
            </div>
            <div class="col-md-1 p-1 mx-auto mt-0 mb-3" style="max-width: 2%">
                <div class="dashedBlock"></div>
            </div>
            <div class="col-md pl-0 pr-0">
                <div
                    class="common-shadow-theme wallet-instruction-item row h-100 m-0 p-3 pt-4 pb-4 {{ $steps['stepState_2'] }}">
                    <div class="col-12">
                        <div class="textBold activeLevel"></div>
                        <div class="textBold inactiveLevel"></div>
                        <h2 class="mb-3">{{ t('ui_personal_information') }}</h2>
                    </div>
                    <div class="col-12">
                        <p>{{ t('ui_information') }}</p>
                    </div>
                    <div class="col-12 mt-auto">
                        <a href="{{ route('cabinet.settings.get', ['open' => 'personal_info']) }}"
                           class="btn btn-lg btn-primary themeBtn">
                            {{ t('ui_go_settings') }}
                        </a>
                    </div>
                </div>
            </div>
            @if($hasComplianceProvider)
            <div class="col-md-1 p-1 mx-auto mt-0 mb-3" style="max-width: 2%">
                <div class="dashedBlock"></div>
            </div>
            <div class="col-md pl-0 pr-0 pr-md-3">
                <div
                    class="common-shadow-theme wallet-instruction-item row h-100 m-0 p-3 pt-4 pb-4 {{ $steps['stepState_3'] }}">
                    <div class="col-12">
                        <div class="textBold activeLevel"></div>
                        <div class="textBold inactiveLevel"></div>
                        <h2 class="mb-3">{{ t('ui_verify_documents') }}</h2>
                    </div>
                    <div class="col-12">
                        <p>{{ t('ui_upload_documents') }}</p>
                    </div>
                    <div class="col-12 mt-auto">
                        <a href="{{ route('cabinet.compliance') }}" class="btn btn-lg btn-primary themeBtn">
                            {{ t('ui_2fa_verify_button') }}
                        </a>
                    </div>
                </div>
            </div>
            @endif
        </div>
    @endif


@endsection

@section('scripts')
    <script>
        $(document).ready(function () {
            $('body').on('click', '.opacityClass', function (e) {
                e.preventDefault();
            })
        });

        // enable-disable add wallet button's functions
        function enableAddCurrencyButton() {
            if (!$('#selectCoin').val()) {
                $('.addCurrency').attr('disabled', 'disabled')
            } else {
                $('.addCurrency').attr('disabled', false)
            }
        }

        // enable-disable add fiat wallet button's functions
        function enableAddFiatButton() {
            if (!$('#selectFiat').val()) {
                $('.addFiat').attr('disabled', 'disabled')
            } else {
                $('.addFiat').attr('disabled', false)
            }
        }

        function disableAddCurrencyButton() {
            $('.addCurrency').attr('disabled', 'disabled')
        }

        $("#addWalletForm").on("submit", function () {
            $('.addCurrency').attr('disabled', 'disabled')
        });

        $('#cryptocurrenciesTablist .btn').click(function () {
            $(this).closest('.wallet-tablist').find('.btn').removeClass('active');
            $(this).addClass('active');

            var targetCryptocurrency = $(this).attr('data-cryptocurrency');

            if (targetCryptocurrency == 'all') {
                $('.wallet-btc[data-cryptocurrency]').parent().show();
            } else {
                $('.wallet-btc[data-cryptocurrency]').parent().hide();
                $('.wallet-btc[data-cryptocurrency=' + targetCryptocurrency + ']').parent().show();
            }
        })

        $('#fiatsTablist .btn').click(function () {
            $(this).closest('.wallet-tablist').find('.btn').removeClass('active');
            $(this).addClass('active');

            var targetFiat = $(this).attr('data-fiat');

            if (targetFiat == 'all') {
                $('.wallet-eur[data-fiat]').parent().show();
            } else {
                $('.wallet-eur[data-fiat]').parent().hide();
                $('.wallet-eur[data-fiat=' + targetFiat + ']').parent().show();
            }
        })

        @if(session()->has('operationCreated'))
            $("#operationCreatedPopUp").modal("toggle");
        @endif
    </script>
@endsection
