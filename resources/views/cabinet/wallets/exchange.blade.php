@extends('cabinet.layouts.cabinet')
@section('title', t('title_send_bts_wallet_page'))

@section('content')
    <div class="row mb-5">
        <div class="col-md-12">
            <div class="row mb-5">
                <div class="col-md-5">
                    <h3 class="mb-3 large-heading-section page-title">SEND - BTS Wallet</h3>
                    <div class="row">
                        <div class="col-md-12 d-flex justify-content-between">
                            <div class="balance">
                                {{ t('ui_platform_operated') }}
                            </div>
                        </div>
                    </div>
                </div>
                @include('cabinet.partials.notification', ['notify' => getNotification(\Illuminate\Support\Facades\Auth::id()), 'admin' => false])
            </div>

            <div class="row">
                <h6 class="pl-3">{{ t('wire_transfer_select_wire_type') }}</h6>
                <div class="col-md-12 pl-0">
                    <div class="row mt-4">
                        <ul class="nav nav-cards d-flex w-100 justify-content-center justify-content-sm-start" role="tablist">
                            <li class="nav-item col-md-4 col-lg-3 mb-2">
                                <a href="{{ route('cabinet.wallets.send.crypto', $cryptoAccountDetail->id) }}"
                                    class="nav-link btn btn-outline-light text-dark mt-2 px-4 py-4 ml-3 exchange-type-btn active show"
                                   >{{ t('send_crypto_send_crypto') }}
                                </a>
                            </li>
                            <li class="nav-item col-md-4 col-lg-3 mb-2">
                                <a href="{{ route('cabinet.wallets.send.wire', $cryptoAccountDetail->id) }}"
                                    class="nav-link btn btn-outline-light text-dark mt-2 px-4 py-4 ml-3 exchange-type-btn">{{ t('send_crypto_wire') }}
                                </a>
                            </li>
                            <li class="nav-item col-md-4 col-lg-3 mb-2">
                                <a href="#"
                                    class="nav-link btn btn-outline-light text-dark mt-2 px-4 py-4 ml-3 exchange-type-btn"
                                     style="border: 2px solid var(--border-color)">{{ t('send_crypto_exchange') }}
                                </a>
                            </li>
                        </ul>
                    </div>
                </div>
            </div>

            <div class="row mt-5">
                <div class="col-md-2">
                    <h6>{{ t('currency') }}</h6>
                    <select class="w-100 mb-4" name="" style="border: 1px solid #bfb7b7;">
                        <option value="">BTC</option>
                    </select>
                </div>
                <div class="col-md-2">
                    <h6 class="mb-0">{{ t('ui_cabinet_deposit_amount') }}</h6>
                    <input class="mt-0 mb-4" value="" name="" placeholder="Amount">
                </div>
{{--                <div class="col-md-2">--}}
{{--                    <h6>{{ t('ui_cabinet_deposit_exchange_to') }}</h6>--}}
{{--                    <select class="w-100 mb-4" name="" style="border: 1px solid #bfb7b7;">--}}
{{--                        <option value="">ETH</option>--}}
{{--                    </select>--}}
{{--                </div>--}}
                <div class="col-md-2 mt-2 mb-4 text-left">
                    <h5 class="text-danger d-block">&asymp; 39.25</h5>
                    <p class="font-weight-bold text-muted">1 BTC = 38.3</p>
                </div>
            </div>

            <div class="row mt-4 pl-3 pr-3">
                <div class="col-md-5">
                    <div class="row">
                        <h5>{{ t('send_crypto_summary') }}</h5>
                    </div>
                    <div class="row py-2 px-0 mt-3 wallet-border-pink p-3">
                        <div class="col-md-5 text-left">
                            <h6 class="font-weight-bold">{{ t('send_crypto_time') }}</h6>
                            <p>Instantly</p>
                            <h6 class="font-weight-bold">{{ t('wire_transfer_exchange_fee') }}</h6>
                            <p>0.5%</p>
                        </div>
                        <div class="col-md-7 text-left">
                            <h6 class="font-weight-bold">{{ t('compliance_rates_limits_table_heading_transaction_limit') }}</h6>
                            <p>eq. $400</p>
                            <h6 class="font-weight-bold">{{ t('ui_cabinet_deposit_available_limit') }}</h6>
                            <p>eq. $39999</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-2">
            <button class="btn themeBtn btnWhiteSpace mt-4" type="submit" style="border-radius: 25px">{{ t('title_exchange_page') }}</button>
        </div>
    </div>
@endsection
