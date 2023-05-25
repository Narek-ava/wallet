@extends('cabinet.layouts.cabinet')
@section('title', t('title_withdraw_page') . ' ' . strtoupper($cryptoAccountDetail->coin) . ' ' . t('title_wallet_page'))

@section('content')
    <div class="row mb-5">
        <div class="col-md-12">
            <h3 class="mb-3 large-heading-section page-title"> {{ t('title_withdraw_page') . ' ' . strtoupper($cryptoAccountDetail->coin) }}
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

    <div class="alert alert-danger alert-dismissible fade show validationError
         @if($errors->any()) d-block @else d-none @endif" role="alert">
        <h4>{{ t('withdraw_wire_validation_errors') }}</h4>
        <button type="button" class="close" data-dismiss="alert" aria-label="Close">
            <span aria-hidden="true">&times;</span>
        </button>
    </div>

    <div class="row">
        <div class="col-md-12">
            <form id="withdraw_to_fiat_form" method="post"
                  action="{{ route('cabinet.wallets.withdraw.to.fiat.operation', $cryptoAccountDetail->id) }}">
                @csrf
                <input type="text" name="operation_id" hidden value="{{ $currentId }}">
                <div class="tab">
                    <a id="prevBtn" onclick="nextPrev(-1)">
                        <i class="fa fa-arrow-left" aria-hidden="true"></i>
                    </a>
                    <a href="{{ route('cabinet.wallets.send.crypto', $cryptoAccountDetail->id) }}"
                       class="text-dark"><i class="fa fa-arrow-left" aria-hidden="true"></i></a>
                    <h5 class="d-inline-block ml-1">{{ t('wire_transfer_step_one') }} - Select type</h5>
                    <div class="row">
                        <div class="col-md-12 pl-0">
                            <div class="row mt-5">
                                <ul class="nav nav-cards d-flex w-100 justify-content-center justify-content-sm-start"
                                    role="tablist">
                                    <li class="nav-item col-md-4 col-lg-3 mb-2">
                                        <a href="{{ route('cabinet.wallets.send.crypto', $cryptoAccountDetail->id) }}"
                                           class="nav-link btn btn-outline-light text-dark mt-2 px-4 py-4 ml-3 exchange-type-btn show"
                                        >{{ t('send_crypto_send_crypto') }}
                                        </a>
                                    </li>

                                    <li class="nav-item col-md-4 col-lg-3">
                                        <a href="{{ route('cabinet.wallets.withdraw.wire', $cryptoAccountDetail->id) }}"
                                           class="nav-link btn btn-outline-light text-dark mt-2 px-4 py-4 ml-3 exchange-type-btn">{{ t('send_crypto_wire') }}
                                        </a>
                                    </li>

                                    <li class="nav-item col-md-4 col-lg-3 mb-2">
                                        <a href="{{ route('cabinet.wallets.withdraw.to.fiat', $cryptoAccountDetail->id) }}"
                                           class="nav-link btn btn-outline-light text-dark mt-2 px-4 py-4 ml-3 exchange-type-btn active"
                                        >{{ t('send_crypto_fiat') }}
                                        </a>
                                    </li>
                                </ul>
                            </div>
                        </div>
                    </div>
                    <div class="row mt-5">
                        <div class="col-md-3">
                            <h6 class="mb-2">{{ t('ui_cabinet_deposit_amount') }}</h6>
                            <input class="coin" type="text" hidden value="{{ $cryptoAccountDetail->coin }}">
                            <input class="mt-2 amount" value="{{ old('amount') }}" name="amount"
                                   placeholder="Crypto Amount"
                                   onchange="checkBalance()"
                                   onkeypress="return (event.charCode == 8 || event.charCode == 0 || event.charCode == 13 || event.charCode == 46) ? null : event.charCode >= 48 && event.charCode <= 57">
                            <small
                                class="balance-fail-message text-danger d-none">{{ t('send_crypto_balance_fail') }}</small>
                            @error('amount')
                            <div class="error text-danger">{{ $message }}</div>
                            @enderror
                            <input class="mt-2 c_profile_id" hidden value="{{ auth()->user()->cProfile->id }}">
                            <p class="row ">
                            <h6> {{ t('withdraw_operation_balance') }}</h6>
                            {{ $availableCurrentAmount . ' ' . $cryptoAccountDetail->coin}}
                            </p>
                        </div>
                        <div class="col-md-3">
                            <h6>{{ t('ui_cabinet_deposit_exchange_to') }}</h6>
                            <select class="w-100 mt-3 currency" name="currency" style="border: 1px solid #bfb7b7;">
                                <option value="">{{ t('select') }} ...</option>
                                @foreach ($fiatWallets as $fiatWallet)
                                    <option value="{{ $fiatWallet->id }}">{{ $fiatWallet->currency }}</option>
                                @endforeach
                            </select>
                            @error('currency')
                            <div class="error text-danger">{{ $message }}</div>
                            @enderror
                        </div>

                        <input type="text" hidden name="compliance_level"
                               value="{{ auth()->user()->cProfile->compliance_level }}">
                        <div class="col-md-3">
                            <h6 class="mb-2">{{ t('ui_cabinet_deposit_amount') }}</h6>
                            <input class="coin" type="text" hidden value="{{ $cryptoAccountDetail->coin }}">
                            <input class="mt-2 amountFiat" value="{{ old('amountFiat') }}" name="amountFiat"
                                   placeholder="{{ t('ui_fiat_amount') }}"
                                   onkeypress="return (event.charCode == 8 || event.charCode == 0 || event.charCode == 13 || event.charCode == 46) ? null : event.charCode >= 48 && event.charCode <= 57">
                            @error('amountFiat')@enderror
                        </div>
                        <span class="limit-fail-text text-danger ml-3 mt-3"></span> <br>

                        <input type="hidden" class="wire-type" value="{{ \App\Enums\OperationType::TYPE_WITHDRAW_TO_FIAT_WALLET }}">
                    </div>
                </div>

                <div class="tab">
                    <a type="button" id="prevBtn" onclick="nextPrev(-1)">
                        <i class="fa fa-arrow-left" aria-hidden="true"></i>
                    </a>
                    <a href="{{ route('cabinet.wallets.send.crypto', $cryptoAccountDetail->id) }}"
                       class="text-dark"></a>
                    <h5 class="d-inline-block ml-4">{{ t('withdraw_wire_step_two') }}</h5>

                    <div class="row pl-3 pr-3">
                        <div class="col-md-8" style="max-width: 500px;">
                            <div class="row py-2 px-0 mt-5 wallet-border-pink p-3">
                                <div class="col-md-5 text-left">
                                    <h6 class="font-weight-bold">{{ t('wire_transfer_withdrawal_fee') }}</h6>
                                    <p class="withdraw-fee"></p>
                                    <h6 class="font-weight-bold">{{ t('wire_transfer_blockchain_fee') }}</h6>
                                    <p class="blockchain-fee">
                                        {{ $blockChainFee }}
                                    </p>
                                </div>
                                <div class="col-md-7 text-left">
                                    <h6 class="font-weight-bold">{{ t('wire_transfer_transaction_limit') }}</h6>
                                    <p class="transaction-limit">{{ moneyFormatWithCurrency('EUR', $limits->transaction_amount_max)}}</p>
                                    <h6 class="font-weight-bold">{{ t('wire_transfer_available_limit') }}</h6>
                                    <p class="available-limit">-</p>
                                </div>
                            </div>
                        </div>
                        <div class="col-md complianceLevel mt-5 d-none">
                            <p>{{ t('wire_transfer_transaction_exceeds_verification_level') }}</p>
                            <p>{{ t('wire_transfer_promote_before_committing_transaction') }}
                                <a href="{{ route('cabinet.compliance') }}"
                                   class="text-dark text-decoration-none"><strong>{{ t('ui_compliance_page') }}</strong></a>.
                            </p>
                        </div>
                    </div>
                </div>

                <div class="buttons mt-1">
                    <a class="btn btn-primary themeBtn btnWhiteSpace mt-5 loader" type="submit" id="nextBtn"
                       onclick="nextPrev(1)">{{ t('wire_transfer_next') }}
                    </a>
                </div>
            </form>
            <div class="overlay"></div>

            <!-- Circles which indicates the steps of the form: -->
            <div style="text-align:center;margin-top:40px;" hidden>
                <span class="step"></span>
                <span class="step"></span>
                <span class="step"></span>
            </div>
        </div>
    </div>


    <div id="providerContainer" class="col-md-12 m-5" style="display: none">
        <div class="row">
            <div class="col-md-12 mt-4 pt-2">
                <label class="component ml-0 p-0">
                    <input hidden class="account_type" value="{{ \App\Enums\AccountType::TYPE_FIAT }}"/>

                    <input type="hidden" name="provider_account_id" class="provider_account_id" value="">
                    @error('bank_detail')
                    <div class="error text-danger">{{ $message }}</div>
                    @enderror
                    <label class="bank-details" style="cursor: pointer">
                        <input type="radio" name="bank_detail" class="provider_name d-none" value="">
                        <div class="checkmark bank-details">
                            <div class="row bank-details-checkmark-text">
                                <div class="col-md-12">
                                    <h5 class="themeColorRed provider_name_text"></h5>

                                    <div class="row">
                                        <div class="col-md-5">
                                            <h6 class="m-0 bank-details-rows">{{ t('wire_transfer_account_beneficiary') }}</h6>
                                        </div>
                                        <div class="col-md-7"><small class="d-block account_beneficiary"></small></div>
                                    </div>
                                    <div class="row mt-2 mt-md-0">
                                        <div class="col-md-5">
                                            <h6 class="m-0 bank-details-rows">{{ t('wire_transfer_beneficiary_address') }}</h6>
                                        </div>
                                        <div class="col-md-7"><small class="d-block beneficiary_address"></small></div>
                                    </div>
                                    <div class="row mt-2 mt-md-0">
                                        <div class="col-md-5 iban_eur_text bank-details-rows"><h6 class="m-0">IBAN
                                                EUR</h6></div>
                                        <div class="col-md-7"><small class="d-block iban_eur"></small></div>
                                    </div>
                                    <div class="row mt-2 mt-md-0 bank-details-rows">
                                        <div class="col-md-5"><h6 class="m-0">SWIFT/BIC</h6></div>
                                        <div class="col-md-7"><small class="d-block swift_bic"></small></div>
                                    </div>
                                    <div class="row mt-2 mt-md-0 bank-details-rows">
                                        <div class="col-md-5"><h6 class="m-0">{{ t('wire_transfer_bank_name') }}</h6>
                                        </div>
                                        <div class="col-md-7"><small class="d-block bank_name"></small></div>
                                    </div>
                                    <div class="row mt-2 mt-md-0 bank-details-rows">
                                        <div class="col-md-5"><h6 class="m-0">{{ t('wire_transfer_bank_address') }}</h6>
                                        </div>
                                        <div class="col-md-7"><small class="d-block bank_address"></small></div>
                                    </div>

                                    <div class="row mt-2 mt-md-0 swift-details" hidden>
                                        <div class="col-md-5"><h6
                                                class="m-0 bank-details-rows">{{ t('ui_cabinet_correspondent_bank') }}</h6>
                                        </div>
                                        <div class="col-md-7"><small class="d-block correspondent_bank"></small></div>
                                    </div>
                                    <div class="row mt-2 mt-md-0 swift-details" hidden>
                                        <div class="col-md-5"><h6
                                                class="m-0 bank-details-rows">{{ t('ui_cabinet_correspondent_bank_swift') }}</h6>
                                        </div>
                                        <div class="col-md-7"><small class="d-block correspondent_bank_swift"></small>
                                        </div>
                                    </div>
                                    <div class="row mt-2 mt-md-0 swift-details" hidden>
                                        <div class="col-md-5"><h6
                                                class="m-0 bank-details-rows">{{ t('ui_cabinet_intermediary_bank') }}</h6>
                                        </div>
                                        <div class="col-md-7"><small class="d-block intermediary_bank"></small></div>
                                    </div>
                                    <div class="row mt-2 mt-md-0 swift-details" hidden>
                                        <div class="col-md-5"><h6
                                                class="m-0 bank-details-rows">{{ t('ui_cabinet_intermediary_bank_swift') }}</h6>
                                        </div>
                                        <div class="col-md-7"><small class="d-block intermediary_bank_swift"></small>
                                        </div>
                                    </div>

                                    <div class="purpose-transfer-row row d-none reference-number mt-2 mt-md-0">
                                        <div class="col-md-5"><h6
                                                class="m-0 d-none reference-number bank-details-rows">{{ t('wire_transfer_purpose_of_transfer') }}</h6>
                                        </div>
                                        <div class="col-md-7">
                                            <div>
                                                <small
                                                    class="d-none purpose_transfer reference-number font-weight-bold themeColorRed"
                                                    id="{{ $currentId }}">{{ $currentId }}</small>

                                                <small class="d-none reference-number font themeColorRed">
                                                    {{ t('wire_transfer_unique_reference_message') }}
                                                </small>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </label>
                </label>
            </div>
        </div>
    </div>
    @includeWhen( $cUser->two_fa_type, 'cabinet._modals.2fa-operation-confirm')
@endsection

@section('scripts')
{{--    <script src="/js/cabinet/withdrawWire.js"></script>--}}
    <script src="/js/cabinet/withdrawToFiat.js"></script>
    <script>
        function checkBalance() {
            let balance = parseFloat("{{ $availableCurrentAmount }}");
            let amount = parseFloat($('.amount').val());
            if (balance < amount) {
                $('.balance-fail-message').addClass('d-block').removeClass('d-none');
            } else {
                $('.balance-fail-message').removeClass('d-block').addClass('d-none');
            }
        }
    </script>

    @if ($cUser->two_fa_type)
        <script>
            $(document).ready(function () {
                let ask2fa = new AskTwoFA();
                ask2fa.attachToFormSubmit('#withdraw_to_fiat_form');
            });
        </script>
    @endif

@endsection

