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
            <form id="card_form" method="post"
                  action="{{ route('cabinet.wallets.card.transfer', $cryptoAccountDetail) }}">
                @csrf
                <div class="tab">
                    <a id="prevBtn" onclick="nextPrev(-1)">
                        <i class="fa fa-arrow-left" aria-hidden="true"></i>
                    </a>
                    <a href="{{ route('cabinet.wallets.top_up_crypto', $cryptoAccountDetail->id) }}"
                       class="text-dark"><i class="fa fa-arrow-left" aria-hidden="true"></i></a>
                    <h5 class="d-inline-block ml-1">{{ t('card_transfer_step') }}</h5>

                    <div class="row mb-5 mt-5">
                        <div class="col-md-3 col-lg-2 mb-4">
                            <h6 class="mb-0">{{ t('wire_transfer_amount') }}</h6>
                            <input class="coin" type="text" hidden value="{{ $cryptoAccountDetail->coin }}">
                            <input class="amount" value="" name="amount" placeholder="Amount"
                                   onkeypress="return (event.charCode == 8 || event.charCode == 0 || event.charCode == 13 || event.charCode == 46) ? null : event.charCode >= 48 &amp;&amp; event.charCode <= 57"
                                   onchange="getExpectedAmount()">
                            <input class="mt-2 c_profile_id" hidden value="{{ auth()->user()->cProfile->id }}">

                            @error('amount')
                                 <small class="error text-danger">{{ $message }}</small>
                            @enderror
                        </div>
                        <div class="col-md-3 col-lg-2 mb-4">
                            <h6>Currency</h6>
                            <select class="w-100 currency" name="currency" style="border: 1px solid #bfb7b7;"
                                    onclick="removeError('currency')" onchange='getExpectedAmount()'>
                                <option value="">{{ t('wire_transfer_select_currency') }}</option>
                                @foreach(\App\Enums\Currency::NEW_FIAT_CURRENCIES as $currency)
                                    <option value="{{ $currency }}">{{ $currency }}</option>
                                @endforeach
                            </select>
                            @error('currency')
                            <div class="error text-danger">{{ $message }}</div>
                            @enderror
                        </div>
                        <div class="col-md-3 col-lg-2 mb-4">
                            <h6>{{ t('wire_transfer_exchange_to') }}</h6>
                            <input type="text" min="0" class="w-100 p-2" readonly id="exchange_to" name="exchange_to"
                                   value="{{ $cryptoAccountDetail->coin }}"
                                   style="border: 1px solid #bfb7b7;border-radius: 10px; height: 37px; color: #7d7777">
                        </div>

                        <input type="text" hidden name="compliance_level"
                               value="{{ auth()->user()->cProfile->compliance_level }}">

                        <input type="text" hidden name="userId"
                               value="{{ auth()->user()->id }}">

                        <div class="col-md-3">
                            <h6 class="mb-0">Expected rate
                                <i class="fa fa-info-circle payment-info-sepa"
                                   style="margin-top: -5px; margin-left: 5px; font-size: 22px; color: red; cursor: pointer"
                                   data-toggle="tooltip" title="" data-content="Some content inside the popover"
                                   data-original-title="The exact rate will be known at the time the funds are credited at the time of the exchange of funds."></i>
                            </h6>
                            <span class="text-danger mb-0 mt-4" style="position: absolute;top: 10px;">≈</span>
                            <input class="expected-amount d-inline border-0 font36bold mb-0 pb-0 pt-1" type="text"
                                   readonly=""
                                   name="expected_amount" style="font-size: 20px; margin-left: 20px;color: var(--main-color);">
                            <p class="fs14 text-muted" style="margin-top: -5px;margin-left: 20px;">1 {{ $cryptoAccountDetail->coin }} = <span
                                    class="expected-rate"></span>
                            </p>
                        </div>
                    </div>

                    <input id="operationType" hidden value="{{ \App\Enums\OperationOperationType::TYPE_CARD }}"/>
                    <input id="wireType" hidden value="{{ \App\Enums\OperationType::TOP_UP_CARD }}"/>
                </div>

                <div class="tab " id="summery" style="display: none">
                    <div class="row pl-3 pr-3">
                        <div class="col-md-7" style="max-width: 500px;">
                            <div class="row">
                                <h5>{{ t('send_crypto_summary') }}</h5>
                            </div>
                            <div class="row py-2 px-0 mt-3 wallet-border-pink p-3">
                                <div class="col-md-6">
                                    <h6 class="font-weight-bold mb-0">{{ t('wire_transfer_time') }}</h6>
                                    <p class="time-to-fund mb-2">{{ t('top_up_by_card_found_time') }}</p>
                                    <h6 class="font-weight-bold mb-0">{{ t('wire_transfer_top_up_fee') }}</h6>
                                    <p class="topup-fee mb-2">-</p>
                                    <h6 class="font-weight-bold mb-0">{{ t('wire_transfer_blockchain_fee') }}</h6>
                                    <p class="blockchain-fee mb-2"  data-count="{{ \App\Enums\OperationOperationType::BLOCKCHAIN_FEE_COUNT_TOP_UP_CARD }} ">-</p>
                                </div>
                                <div class="col-md-6">
                                    <h6 class="font-weight-bold mb-0">{{ t('wire_transfer_transaction_limit') }}</h6>
                                    <p class="mb-2 transaction-limit">-</p>
                                    <h6 class="font-weight-bold mb-0">{{ t('wire_transfer_available_limit') }}</h6>
                                    <p class="available-limit mb-2">-</p>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-5 complianceLevel mt-5 pl-3 pl-md-5 row align-content-center">
                            <p>
                                <i class="fa fa-info-circle payment-info-sepa"
                                   style="font-size: 22px; color: red; cursor: pointer"
                                   data-toggle="tooltip" title="" data-content="Some content inside the popover"
                                   data-original-title="The exact rate will be known at the time the funds are credited at the time of the exchange of funds."></i>

                                {!! t('card_transfer_transaction_exceeds_verification_about_card') !!}
                            </p>
                            <p>
                                <i class="fa fa-info-circle payment-info-sepa"
                                   style="font-size: 22px; color: red; cursor: pointer"
                                   data-toggle="tooltip" title="" data-content="Some content inside the popover"
                                   data-original-title="The exact rate will be known at the time the funds are credited at the time of the exchange of funds."></i>
                                {{ t('card_transfer_warning_message') }}
                            </p>
                        </div>
                    </div>
                    <br><br>

                    <div class="d-flex pl-2">
                        <input id="agreeTerms" type="checkbox" name="agreeTerms" required
                               style="width: max-content; margin-right: 15px; transform: scale(2); margin-top: 12px;"/>
                        <label for="agreeTerms" class="w-100" style="max-width: 575px">
                            {!! t('card_transfer_transaction_terms_and_conditions_agreement', ['termsAndCond' =>"<a href='https://cratos.net/terms-and-conditions/'>Terms And Conditions</a>"]) !!}
                        </label>

                    </div>
                    <small class="terms-fail-message text-danger mt-2 mb-2 d-none">{{ t('terms_and_agreements_checkbox_not_checked') }}</small>
                    <small class="provider-status-suspended text-danger mt-2 mb-2 d-none">{{ t('provider_status_is_suspended')  }}</small>
                </div>

                <div class="buttons mt-4">
                    <button class="btn btn-primary themeBtn btnWhiteSpace loader" type="submit" id="nextBtn"> Next</button>
                    <img src="{{ asset('/cratos.theme/images/' . 'payment_logos.png') }}" alt="">
                </div>
            </form>
        </div>

        <div class="overlay"></div>

        <div style="text-align:center;margin-top:40px;" hidden>
            <span class="step"></span>
            <span class="step"></span>
        </div>
    </div>
@endsection

@section('scripts')
    <script src="/js/cabinet/topUpCard.js"></script>


@endsection
