@extends('cabinet.layouts.cabinet')
@section('title', strtoupper(t('send_crypto_send_crypto')) . '-' . strtoupper($cryptoAccountDetail->coin) . t('title_wallet_page'))

@section('content')
    <div class="row">
        <div class="col-md-12">
            <div class="row">
                <div class="col-md-5">
                    <h3 class="large-heading-section page-title">{{ strtoupper(t('send_crypto_send_crypto')) }}
                        - {{ strtoupper($cryptoAccountDetail->coin) }} Wallet</h3>
                    <div class="row">
                        <div class="col-md-12 d-flex justify-content-between">
                            <div class="balance">
                                {{ t('backoffice_profile_page_header_body') }}
                            </div>
                        </div>
                    </div>
                </div>
                @include('cabinet.partials.notification', ['notify' => getNotification(\Illuminate\Support\Facades\Auth::id()), 'admin' => false])
            </div>

            <div class="row mt-5">
                <h5 class="pl-3">{{ t('select_operation_step_one') }}</h5>
                <div class="col-md-12 pl-0">
                    <div class="row mt-5">
                        <ul class="nav nav-cards d-flex w-100 justify-content-center justify-content-sm-start" role="tablist">
                            <li class="nav-item col-md-4 col-lg-3 mb-2">
                                <a href="{{ route('cabinet.wallets.send.crypto', $cryptoAccountDetail->id) }}"
                                   class="nav-link btn btn-outline-light text-dark mt-2 px-4 py-4 ml-3 exchange-type-btn show active"
                                   data-toggle="tab">{{ t('send_crypto_send_crypto') }}
                                </a>
                            </li>

                            @if($paymentProviderExists)
                                <li class="nav-item col-md-4 col-lg-3"
                                    @if(!config('app.allow_wire') &&
                                        @auth()->user()->cProfile->account_type === \App\Models\Cabinet\CProfile::TYPE_INDIVIDUAL)
                                    hidden
                                    @endif
                                >
                                    <a href="{{ route('cabinet.wallets.withdraw.wire', $cryptoAccountDetail->id) }}"
                                       class="nav-link btn btn-outline-light text-dark mt-2 px-4 py-4 ml-3 exchange-type-btn">{{ t('send_crypto_wire') }}
                                    </a>
                                </li>
                            @endif
                            <li class="nav-item col-md-4 col-lg-3 mb-2">
                                <a href="{{ route('cabinet.wallets.withdraw.to.fiat', $cryptoAccountDetail->id) }}"
                                   class="nav-link btn btn-outline-light text-dark mt-2 px-4 py-4 ml-3 exchange-type-btn"
                                >{{ t('send_crypto_fiat') }}
                                </a>
                            </li>
                        </ul>
                    </div>
                </div>
            </div>

            <form id="withdrawCryptoForm" class="max-w-900" action="{{ route('cabinet.withdrawal.post') }}" method="post">
                @csrf
                <div class="row mt-5">
                    <div class="col-md-3 send-crypto">
                        <input type="hidden" name="crypto_account_detail_id" value="{{ $cryptoAccountDetail->id }}">
                        <h6 class="mt-3 mb-0">{{ t('send_crypto_amount') }}</h6>
                        <input class="mb-3 amount" value="{{ old('amount') ?? request()->amount }}" name="amount" placeholder="Amount"
                               onchange="checkBalance()"
                               onclick="enableButton()"
                               onkeypress="return (event.charCode == 8 || event.charCode == 0 || event.charCode == 13 || event.charCode == 46) ? null : event.charCode >= 48 && event.charCode <= 57">
                        <small class="min-amount-message text-danger d-none">{{ t('send_crypto_min_amount_message') . $commissions->min_amount . ' ' . strtoupper($cryptoAccountDetail->coin) }}</small>
                        <small class="available-amount-message text-danger d-none">{{ t('send_crypto_available_amount_message') . $availableMonthlyAmount }}</small>
                        <small class="balance-fail-message text-danger d-none">{{ t('send_crypto_balance_fail') }}</small>
                        <small class="blockchain-message text-danger d-none">{{ t('send_crypto_blockchain_message') }}</small>
                        @error('amount')
                        <div class="error text-danger">{{ $message }}</div>
                        @enderror
                        <p class="row ">
                            <h6> {{ t('withdraw_operation_balance') }}</h6>
                            {{ $availableCurrentAmount.' '.$cryptoAccountDetail->coin}}
                        </p>
                    </div>

                    <div class="col-md-4 to-wallet">
                        <h6 class="mt-3">{{ t('withdraw_crypto_to_wallet') }}</h6>
                        <select class="w-100 mb-3 select-to-wallet" name="to_wallet" style="border: 1px solid #bfb7b7;"
                                onchange="checkWallet()">
                            <option value="">{{ t('select') }} ...</option>
                            <option value="0" {{ count($accountCoins) == 0 || $errors->has('wallet_address') ? 'selected' : ''}} class="text-danger font-weight-bold" style="font-size: 18px">{{ t('withdraw_crypto_new') }}</option>
                            @foreach($accountCoins as $accountCoin)
                                <option value="{{ $accountCoin->id }}" {{ old('to_wallet') === $accountCoin->id ? 'selected' : '' }}> {{ $accountCoin->coin }} {{ $accountCoin->label }} </option>
                            @endforeach

                        </select>
                        @error('to_wallet')
                        <div class="error text-danger">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="col-md-5 new-wallet  {{ count($accountCoins) == 0 || $errors->has('wallet_address') ? 'd-block' : 'd-none'}}">
                        <h6 class="mt-3">{{ t('withdraw_crypto_add_new') }}</h6>
                        <input class="wallet-address" value="" name="wallet_address" placeholder="Address" style="border: 1px solid #bfb7b7; border-radius: 10px; height: 38px">
                        @error('wallet_address')
                        <div class="error text-danger">{!! $message !!}</div>
                        @enderror
                    </div>

                    <input type="text" hidden name="compliance_level" value="{{ auth()->user()->cProfile->compliance_level }}">
                </div>
                <div class="row new-wallet {{ count($accountCoins) == 0 || $errors->has('wallet_address') ? 'd-block' : 'd-none'}}">
                    <div class="col-md-5">
                        <div class="form-check mt-4 pl-0">
                            <div class="custom-control custom-checkbox">
                                <input type="checkbox" name="allowSaveDraft" class="custom-control-input" id="customCheck1" value="1">
                                <label class="custom-control-label" for="customCheck1"
                                       style="font-size: 16px">{{ t('withdrawal_crypto_save_for_future_use') }}</label>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="row mt-4 pl-3 pr-3">
                    <div class="col-md-5">
                        <div class="row mt-3 mb-5">
                            <h5>{{ t('send_crypto_summary') }}</h5>
                        </div>
                        <div class="row py-2 px-0 mt-3 wallet-border-pink p-3">
                            <div class="col-sm-5 text-left">
                                <h6 class="font-weight-bold mb-0 mt-2">{{ t('send_crypto_time') }}</h6>
                                <p>{{ t('withdraw_crypto_instantly') }}</p>
                                <h6 class="font-weight-bold mb-0 mt-2">{{ t('send_crypto_withdrawal_fee') }}</h6>
                                <span class="withdraw-fee">{{ $commissions->percent_commission . '%' }}</span>
                                <h6 class="font-weight-bold mb-0 mt-2">{{ t('send_crypto_blockchain_fee') }}</h6>
                                <p>{{ formatMoney($blockChainFee, $cryptoAccountDetail->coin) . ' ' . $cryptoAccountDetail->coin}}</p>
                            </div>
                            <div class="col-sm-7 text-left">
                                <h6 class="font-weight-bold mb-0 mt-2">{{ t('send_crypto_transaction_limit') }}</h6>
                                <p>{{t('limit_eq')}} {{ eur_format($limits->transaction_amount_max)}}</p>
                                <h6 class="font-weight-bold mb-0 mt-2">{{ t('send_crypto_available_limit') }}</h6>
                                <p>{{t('limit_eq')}} {{ $availableMonthlyAmount > 0 ? eur_format($availableMonthlyAmount) : eur_format(0) }}</p>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="col-md-2 pl-0">
                    <button class="btn themeBtn btnWhiteSpace mt-4 send-btn loader"
                            style="border-radius: 30px" onclick="checkAmount()"
                            type="submit">{{ t('send_crypto_send') }}
                    </button>
                </div>
            </form>
        </div>

        <div class="overlay"></div>

        @includeWhen( $cUser->two_fa_type, 'cabinet._modals.2fa-operation-confirm')
        <script>
            var cryptoWallet = document.getElementById('cryptoWalletSenderType');

            function checkWallet() {
                if ($('.select-to-wallet').val() == '0') {
                    $('.new-wallet').removeClass('d-none').addClass('d-block')
                } else {
                    $('.new-wallet').addClass('d-none').removeClass('d-block')
                }
            }

            function checkAmount() {
                if ($('.amount').val() <= parseFloat("{{ $commissions->blockchain_fee }}")) { // @todo Artak, why blockchain ?
                    $('.send-btn').attr('disabled', 'disabled');
                    $('.blockchain-message').addClass('d-block').removeClass('d-none');
                }

                let minAmountExceed = parseFloat($('.amount').val()) < parseFloat("{{ $commissions->min_amount }}");
                let balance = "{{ $cryptoAccountDetail->account->displayAvailableBalance() }}";

                if (minAmountExceed) {
                    $('.send-btn').attr('disabled', 'disabled');
                    $('.min-amount-message').addClass('d-block').removeClass('d-none');
                }

                if (balance <= 0) { // @todo Artak check by wallet balance
                    $('.send-btn').attr('disabled', 'disabled');
                    $('.balance-fail-message').addClass('d-block').removeClass('d-none');
                }
            }


            function enableButton() {
                $('.send-btn').attr('disabled', false)
                $('.min-amount-message').removeClass('d-block').addClass('d-none');
            }

            function showNewFields() {
                $('.wallet-address').removeClass('d-none').addClass('d-block');
            }
        </script>
@endsection

@section('scripts')
    @if ($cUser->two_fa_type)
        <script>
            $(document).ready(function () {
                let ask2fa = new AskTwoFA();
                ask2fa.attachToFormSubmit('#withdrawCryptoForm');
            });
        </script>
    @endif
        <script>
            function checkBalance() {
                let balance = "{{ $availableCurrentAmount }}";
                let amount = $('.amount').val();
                if (parseFloat(balance) < parseFloat(amount)) {
                    $('.send-btn').attr('disabled', 'disabled');
                    $('.balance-fail-message').addClass('d-block').removeClass('d-none');
                } else {
                    $('.send-btn').attr('disabled', false);
                    $('.balance-fail-message').removeClass('d-block').addClass('d-none');
                }
                checkAmount()
            }
        </script>
@endsection
