@extends('cabinet.layouts.cabinet')
@section('title', t('title_top_up_page') . strtoupper($cryptoAccountDetail->coin) . ' ' . t('title_wallet_page'))

@section('content')
    <div class="row mb-5">
        <div class="col-md-12">
            <h3 class="mb-3 large-heading-section page-title">Top Up - {{ strtoupper($cryptoAccountDetail->coin) }}
                Wallet</h3>
            <div class="row">
                <div class="col-md-5 d-flex justify-content-between">
                    <div class="balance">
                        {{ t('backoffice_profile_page_header_body') }}
                    </div>
                </div>
                @include('cabinet.partials.notification', ['notify' => getNotification(\Illuminate\Support\Facades\Auth::id()), 'admin' => false])
            </div>
        </div>
    </div>
    @include('cabinet.partials.session-message')


    <div class="row">
        <div class="col-md-12">
            <form id="fiat_form" method="post"
                  action="{{ route('cabinet.wallets.create.buy.crypto.from.fiat', $cryptoAccountDetail) }}">
                @csrf
                <input type="text" name="operation_id" hidden value="{{ $currentId }}">
                <div class="tab">
                    <a id="prevBtn" onclick="nextPrev(-1)">
                        <i class="fa fa-arrow-left" aria-hidden="true"></i>
                    </a>
                    <a href="{{ route('cabinet.wallets.top_up_crypto', $cryptoAccountDetail->id) }}"
                       class="text-dark"><i class="fa fa-arrow-left" aria-hidden="true"></i></a>
                    <h5 class="d-inline-block ml-1">{{ t('card_transfer_step') }}</h5>

                    <div class="row mb-5 mt-5">
                        <div class="col-md-3 col-lg-2 mb-4">
                            <h6>{{ t('ui_cabinet_deposit_amount') }}</h6>
                            <input class="coin" type="text" hidden value="{{ $cryptoAccountDetail->coin }}">
                            <input class="amount" value="{{ old('amount') }}" name="amount"
                                   placeholder="{{ t('amount') }}"
                                   onkeypress="return (event.charCode == 8 || event.charCode == 0 || event.charCode == 13 || event.charCode == 46) ? null : event.charCode >= 48 && event.charCode <= 57">
                            @error('amountFiat')@enderror
                        </div>

                        <div class="col-md-3 col-lg-2 mb-4">
                            <h6>Currency</h6>
                            <input type="text" min="0" class="w-100 p-2" readonly id="crypto_currency"
                                   value="{{ $cryptoAccountDetail->coin }}"
                                   style="border: 1px solid #bfb7b7;border-radius: 10px; height: 37px; color: #7d7777">
                        </div>
                        <div class="col-md-3 col-lg-2 mb-4">
                            <h6>{{ t('wire_transfer_exchange_to') }}</h6>
                            <select class="w-100 currency" name="currency" style="border: 1px solid #bfb7b7;" id="fiat_currency"
                                    onclick="removeError('currency')">
                                <option value="">{{ t('wire_transfer_select_currency') }}</option>
                                @foreach($fiatWallets as $fiatWallet)
                                    <option data-balance="{{ generalMoneyFormat($fiatWallet->getAvailableBalance(), $fiatWallet->currency) }}"
                                            value="{{ $fiatWallet->id }}" @if ($fiatWallet->id == old('currency')) selected @endif>{{ $fiatWallet->currency }}
                                    </option>
                                @endforeach
                            </select>
                            <div style="display: none; padding: 5px">{{ t('balance') }}: <span class="display_fiat_balance"></span></div>
                            @error('currency')
                            <div class="error text-danger">{{ $message }}</div>
                            @enderror
                        </div>


                        <input type="text" hidden name="compliance_level"
                               value="{{ auth()->user()->cProfile->compliance_level }}">
                        <div class="col-md-3 col-lg-2 mb-4">
                            <h6 class="mb-0">{{ t('ui_fiat_amount') }}</h6>
                            <input class="coin" type="text" hidden value="{{ $cryptoAccountDetail->coin }}">
                            <input class="amountFiat" onclick="removeError('amountFiat')" value="{{ old('amountFiat') }}" name="amountFiat" placeholder="{{ t('ui_fiat_amount') }}"
                                   onkeypress="return (event.charCode == 8 || event.charCode == 0 || event.charCode == 13 || event.charCode == 46) ? null : event.charCode >= 48 &amp;&amp; event.charCode <= 57"
                                   >
                            <input class="mt-2 c_profile_id" hidden value="{{ auth()->user()->cProfile->id }}">

                            @error('amountFiat')
                            <small class="error amountFiat-error text-danger">{{ $message }}</small>
                            @enderror
                        </div>

                    </div>

                    <input id="operationType" hidden value="{{ \App\Enums\OperationOperationType::TYPE_BUY_CRYPTO_FROM_FIAT }}"/>
                    <input id="wireType" hidden value="{{ \App\Enums\OperationType::TOP_UP_FROM_FIAT }}"/>
                </div>

                <div class="tab " id="summery" style="display: none">
                    <div class="row pl-3 pr-3">
                        <div class="col-md-7" style="max-width: 500px;">
                            <div class="row">
                                <h5>{{ t('send_crypto_summary') }}</h5>
                            </div>
                            <div class="row py-2 px-0 mt-3 wallet-border-pink p-3">
                                <div class="col-md-6">
<!--                                    <h6 class="font-weight-bold mb-0">{{ t('wire_transfer_time') }}</h6>
&lt;!&ndash;                                    <p class="time-to-fund mb-2">{{ t('top_up_by_card_found_time') }}</p>&ndash;&gt;-->
                                    <h6 class="font-weight-bold mb-0">{{ t('wire_transfer_top_up_fee') }}</h6>
                                    <p class="topup-fee mb-2">-</p>
                                    <h6 class="font-weight-bold mb-0">{{ t('wire_transfer_blockchain_fee') }}</h6>
                                    <p class="blockchain-fee mb-2"  data-count="{{ \App\Enums\OperationOperationType::TYPE_BUY_CRYPTO_FROM_FIAT }} ">-</p>
                                </div>
                                <div class="col-md-6">
                                    <h6 class="font-weight-bold mb-0">{{ t('wire_transfer_transaction_limit') }}</h6>
                                    <p class="mb-2 transaction-limit">-</p>
                                    <h6 class="font-weight-bold mb-0">{{ t('wire_transfer_available_limit') }}</h6>
                                    <p class="available-limit mb-2">-</p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="buttons mt-4">
                    <button class="btn btn-primary themeBtn btnWhiteSpace loader" type="submit" id="nextBtn"> Next</button>
                </div>
            </form>
        </div>

        <div class="overlay"></div>

        <div style="text-align:center;margin-top:40px;" hidden>
            <span class="step"></span>
            <span class="step"></span>
        </div>
    </div>
    @includeWhen( $cUser->two_fa_type, 'cabinet._modals.2fa-operation-confirm')
@endsection

@section('scripts')
    <script src="/js/cabinet/buyCryptoFromFiat.js"></script>



    @if ($cUser->two_fa_type)
        <script>
            $(document).ready(function () {
                const ask2fa = new AskTwoFA();
                ask2fa.attachToFormSubmit('#fiat_form');
            });
        </script>
    @endif

@endsection
