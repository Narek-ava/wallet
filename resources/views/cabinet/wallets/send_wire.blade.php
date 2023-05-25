@extends('cabinet.layouts.cabinet')
@section('title', t('title_withdraw_page') . strtoupper($cryptoAccountDetail->coin) . t('title_wallet_page'))

@section('content')
    <style>
        .invalid {
            background: #ffdddd;
        }

        .bank-details {
            height: 265px;
        }

        .bank-details-checkmark-text {
            height: auto;
        }

        @media (max-width: 767px) {
            .bank-details,
            .bank-details-checkmark-text {
                height: auto;
            }
        }
        .checkmark.bank-details{
            height: 100%;
        }
        .component.ml-0.p-0 {
            height: 100%;
        }
        .bank-details-rows {
            font-size: 14px;
        }
    </style>
    <div class="row mb-5">
        <div class="col-md-12">
            <h3 class="mb-3 large-heading-section page-title"> {{ t('title_withdraw_page') . ' ' . strtoupper($cryptoAccountDetail->coin) }} Wallet</h3>
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
            <form id="withdraw_wire_form" method="post"
                  action="{{ route('cabinet.wallets.withdraw.wire.operation', $cryptoAccountDetail->id) }}">
                @csrf
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
                                           class="nav-link btn btn-outline-light text-dark mt-2 px-4 py-4 ml-3 exchange-type-btn active">{{ t('send_crypto_wire') }}
                                        </a>
                                    </li>
                                     <li class="nav-item col-md-4 col-lg-3">
                                         <a href="{{ route('cabinet.wallets.withdraw.to.fiat', $cryptoAccountDetail->id) }}"
                                            class="nav-link btn btn-outline-light text-dark mt-2 px-4 py-4 ml-3 exchange-type-btn"
                                         >{{ t('send_crypto_fiat') }}
                                         </a>
                                    </li>
                                    {{--                                    <li class="nav-item col-md-4 col-lg-3 mb-2">
                                    {{--                                        <a href="{{ route('cabinet.wallets.exchange', $cryptoAccountDetail->id) }}"--}}
                                    {{--                                           class="nav-link btn btn-outline-light text-dark mt-2 px-4 py-4 ml-3 exchange-type-btn"--}}
                                    {{--                                           >{{ t('send_crypto_exchange') }}--}}
                                    {{--                                        </a>--}}
                                    {{--                                    </li>--}}
                                </ul>
                            </div>
                        </div>
                    </div>
                    <div class="row mt-5">
                        <div class="col-md-3">
                            <h6 class="mb-2">{{ t('ui_cabinet_deposit_amount') }}</h6>
                            <input class="coin" type="text" hidden value="{{ $cryptoAccountDetail->coin }}">
                            <input class="mt-2 amount" value="{{ old('amount') }}" name="amount" placeholder="Amount"
                                   onchange="checkBalance()"
                                   onkeypress="return (event.charCode == 8 || event.charCode == 0 || event.charCode == 13 || event.charCode == 46) ? null : event.charCode >= 48 && event.charCode <= 57"
                                   onkeyup='getExpectedAmount()'>
                            <small
                                class="balance-fail-message text-danger d-none">{{ t('send_crypto_balance_fail') }}</small>
                            @error('amount')
                            <div class="error text-danger">{{ $message }}</div>
                            @enderror
                            <input class="mt-2 c_profile_id" hidden value="{{ auth()->user()->cProfile->id }}">
                            <p class="row ">
                            <h6> {{ t('withdraw_operation_balance') }}</h6>
                            {{ $availableCurrentAmount.' '.$cryptoAccountDetail->coin}}
                            </p>

                        </div>
                        <div class="col-md-3">
                            <h6>{{ t('ui_cabinet_deposit_exchange_to') }}</h6>
                            <select class="w-100 mt-3 currency" name="currency" style="border: 1px solid #bfb7b7;"
                                    onchange='getExpectedAmount()'>
                                <option value="">{{ t('select') }} ...</option>
                                @foreach(\App\Enums\Currency::FIAT_CURRENCY_NAMES as $currency)
                                    <option value="{{ $currency }}">{{ $currency }}</option>
                                @endforeach
                            </select>
                            @error('currency')
                            <div class="error text-danger">{{ $message }}</div>
                            @enderror
                        </div>

                        <input type="text" hidden name="compliance_level"
                               value="{{ auth()->user()->cProfile->compliance_level }}">

                        <div class="col-md-3">
                            <h6>{{ t('withdraw_wire_expected_rate') }}
                                <i class="fa fa-info-circle payment-info-sepa"
                                   style="font-size: 22px; color: red; cursor: pointer"
                                   data-toggle="tooltip"
                                   title="{{ t('withdraw_wire_expected_rate_popup') }}"
                                   data-content="Some content inside the popover"></i>
                            </h6>

                            <span class="text-danger mb-0 mt-4" style="position: absolute;top: 22px;">&asymp;</span>
                            <input class="expected-amount d-inline border-0 font36bold mb-0 pb-0 pt-1" type="text"
                                   readonly
                                   name="expected_amount" style="font-size: 20px; margin-left: 20px;color: var(--main-color);">
                            <p class="fs14 text-muted" style="margin-top: -5px;margin-left: 20px;">
                                1 {{ $cryptoAccountDetail->coin }} = <span
                                    class="expected-rate"></span></p>
                        </div>
                    </div>
                    <span class="limit-fail-text text-danger ml-3 mt-3"></span>
                </div>

                <div class="tab">
                    <a type="button" id="prevBtn" onclick="nextPrev(-1)">
                        <i class="fa fa-arrow-left" aria-hidden="true"></i>
                    </a>
                    <a href="{{ route('cabinet.wallets.send.crypto', $cryptoAccountDetail->id) }}"
                       class="text-dark"></a>
                    <h5 class="d-inline-block ml-4">{{ t('withdraw_wire_step_two') }}</h5>

                    <div class="mt-5">
                        <input type="text" name="operation_id" hidden value="{{ $currentId }}">


                        @foreach(\App\Enums\AccountType::ACCOUNT_WIRE_TYPES as $key => $wireType)
                            <label class="component ml-0">
                                <input class="wire-type account-type" type="radio" name="type" @if ($loop->first) checked="checked" @endif
                                       value="{{ \App\Enums\WireType::OPERATION_WITHDRAW_WIRE_TYPES[$key] ?? ''}}">
                                <span class="checkmark">
                                    <i class="fa fa-info-circle position-absolute payment-info payment-info-sepa"
                                       data-toggle="tooltip"
                                       title="{{ $wireType == 'SEPA' ? t('sepa_popup_info') : t('swift_popup_info') }}"
                                       data-content="Some content inside the popover"></i>
                                    <img class="position-absolute h-50" style="left: 68px; top: 30px"
                                         src="{{ asset('/cratos.theme/images/' . (\App\Enums\WireType::IMAGES[$wireType] ?? '')) }}"
                                    >
                                </span>
                            </label>
                        @endforeach
                        @error('type')
                        <div class="error text-danger">{{ $message }}</div>
                        @enderror
                        <p class="select-wire-type d-none text-danger"> {{ t('wire_transfer_select_wire_type') }}</p>
                    </div>
                </div>

                <div class="tab">
                    <a type="button" id="prevBtn" onclick="nextPrev(-1)" class="loader">
                        <i class="fa fa-arrow-left" aria-hidden="true"></i>
                    </a>
                    <h5 class="d-inline-block ml-4">{{ t('withdraw_wire_step_three') }}</h5>
                    <div class="row mt-3">
                        <div class="col-md-4 mb-4">
                            <h6 class="font-weight-bold mb-2">{{ t('withdraw_wire_bank_template') }}</h6>
                            <select id="bank_template" class="w-100" name="bank_template"
                                    style="border: 1px solid #bfb7b7;" onchange="displayBankTemplate()">
                                <option value="">{{ t('select') }} ...</option>
                            </select>
                            @error('bank_template')
                            <div class="error text-danger">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="col-md-4 bank-template-name d-none mb-4">
                            <h6 class="font-weight-bold mb-2 template-name-label">{{ t('withdraw_wire_bank_template_name') }}</h6>
                            <input id="template_name" class="template-name" name="template_name" value="1"
                                   placeholder="Name"
                                   style="border: 1px solid #ccc; border-radius: 10px; padding: 9px; height: 38px">
                            @error('template_name')
                            <div class="error text-danger">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>
                    <div class="row mt-4">
                        <div class="col-md-3 mb-4">
                            <h6 class="font-weight-bold mb-2">{{ t('withdraw_wire_bank_country') }}</h6>
                            <select id="" class="w-100 country" name="country"
                                    style="width: 100% !important">
                                <option value="">{{ t('select') }} ...</option>
                            </select>
                            @error('country')
                            <div class="error text-danger">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="col-md-2 mb-4">
                            <h6 class="font-weight-bold mb-2">{{ t('withdraw_wire_currency') }}</h6>
                            <select id="bank_currency" class="w-100" name="bank_currency"
                                    style="border: 1px solid #bfb7b7;">
                                <option value="">{{ t('select') }} ...</option>
                                @foreach(\App\Enums\Currency::FIAT_CURRENCY_NAMES as $currency)
                                    <option class="bank-currency-option"
                                            value="{{ $currency }}">{{ $currency }}</option>
                                @endforeach
                            </select>
                            @error('bank_currency')
                            <div class="error text-danger">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="col-md-4 mb-4">
                            <h6 class="font-weight-bold mb-0">IBAN</h6>
                            <input class="iban" value="{{ old('iban') }}" name="iban" placeholder="IBAN">
                            @error('iban')
                            <div class="error text-danger">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="col-md-3 mb-4">
                            <h6 class="font-weight-bold mb-0">SWIFT/BIC</h6>
                            <input class="swift" value="{{ old('swift') }}" name="swift" placeholder="SWIFT/BIC">
                            @error('swift')
                            <div class="error text-danger">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>

                    <div class="row mt-4">
                        <div class="col-md-3 mb-4">
                            <h6 class="font-weight-bold mb-0">{{ t('withdraw_wire_account_holder') }}</h6>
                            <input class="account-holder" value="{{ old('account_holder') }}" name="account_holder"
                                   placeholder="{{ t('withdraw_wire_account_holder') }}">
                            @error('account_holder')
                            <div class="error text-danger">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="col-md-3 mb-4">
                            <h6 class="font-weight-bold mb-0">{{ t('withdraw_wire_account_number') }}</h6>
                            <input class="account-number" value="{{ old('account_number') }}" name="account_number"
                                   placeholder="{{ t('withdraw_wire_account_number') }}">
                            @error('account_number')
                            <div class="error text-danger">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="col-md-3 mb-4">
                            <h6 class="font-weight-bold mb-0">{{ t('withdraw_wire_bank_name') }}</h6>
                            <input class="bank-name" value="{{ old('bank_name') }}" name="bank_name"
                                   placeholder="{{ t('withdraw_wire_bank_name') }}">
                            @error('bank_name')
                            <div class="error text-danger">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>
                    <div class="row mt-4">
                        <div class="col-md-9 mb-4">
                            <h6 class="font-weight-bold mb-0">{{ t('withdraw_wire_bank_address') }}</h6>
                            <input class="bank-address" value="{{ old('bank_address') }}" name="bank_address"
                                   placeholder="{{ t('withdraw_wire_bank_address') }}">
                            @error('bank_address')
                            <div class="error text-danger">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>


                    <div class="row mt-4">
                        <span
                            class="d-none no-providers-text text-danger ml-3 mt-3">{{ t('withdraw_wire_no_provider') }} </span>
                        <span class="limit-fail-text text-danger ml-3 mt-3"></span>
                    </div>
                </div>

                <div class="tab container ml-0">
                    <div class="mb-3 row">
                        <a id="prevBtn" onclick="nextPrev(-1)">
                            <i class="fa fa-arrow-left" aria-hidden="true"></i>
                        </a>
                        <h5 class="d-inline-block ml-4">{{ t('withdraw_wire_step_four') }}</h5>
                    </div>
                    <div id="form_providers" class="row"></div>
                </div>

                <div class="tab" id="createTab">
                    <a id="prevBtn" onclick="nextPrev(-1)">
                        <i class="fa fa-arrow-left" aria-hidden="true"></i>
                    </a>

                    <h5 class="d-inline-block ml-4">{{ t('wire_transfer_step_five') }}</h5>
                    <div class="row pl-3 pr-3">
                        <div class="col-md-8" style="max-width: 500px;">
                            <div class="row py-2 px-0 mt-5 wallet-border-pink p-3">
                                <div class="col-md-5 text-left">
                                    <h6 class="font-weight-bold">{{ t('wire_transfer_time') }}</h6>
                                    <p class="time-to-found">{{ t('ui_instantly') }}</p>
                                    <h6 class="font-weight-bold">{{ t('wire_transfer_withdrawal_fee') }}</h6>
                                    <p class="withdraw-fee"></p>
                                    <h6 class="font-weight-bold">{{ t('wire_transfer_blockchain_fee') }}</h6>
                                    <p class="blockchain-fee"
                                       data-count="{{ \App\Enums\OperationOperationType::BLOCKCHAIN_FEE_COUNT_WITHDRAW_WIRE }}"></p>
                                </div>
                                <div class="col-md-7 text-left">
                                    <h6 class="font-weight-bold">{{ t('wire_transfer_transaction_limit') }}</h6>
                                    <p class="transaction-limit">{{ $limits->transaction_amount_max ?? '-' }}</p>
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
                <span class="step"></span>
                <span class="step"></span>
            </div>
        </div>
    </div>


    <div id="providerContainer" class="col-md-12 m-5" style="display: none">
        <div class="row">
            <div class="col-md-12 mt-4 pt-2">
                <label class="component ml-0 p-0">
                    <input hidden class="account_type" value="{{ \App\Enums\AccountType::TYPE_WIRE_SWIFT }}"/>

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
                                            <h6 class="m-0 bank-details-rows" >{{ t('wire_transfer_beneficiary_address') }}</h6>
                                        </div>
                                        <div class="col-md-7"><small class="d-block beneficiary_address"></small></div>
                                    </div>
                                    <div class="row mt-2 mt-md-0">
                                        <div class="col-md-5 iban_eur_text bank-details-rows"><h6 class="m-0">IBAN EUR</h6></div>
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
                                                class="m-0 bank-details-rows" >{{ t('ui_cabinet_correspondent_bank') }}</h6>
                                        </div>
                                        <div class="col-md-7"><small class="d-block correspondent_bank"></small></div>
                                    </div>
                                    <div class="row mt-2 mt-md-0 swift-details" hidden>
                                        <div class="col-md-5"><h6 class="m-0 bank-details-rows" >{{ t('ui_cabinet_correspondent_bank_swift') }}</h6>
                                        </div>
                                        <div class="col-md-7"><small class="d-block correspondent_bank_swift"></small>
                                        </div>
                                    </div>
                                    <div class="row mt-2 mt-md-0 swift-details" hidden>
                                        <div class="col-md-5"><h6 class="m-0 bank-details-rows">{{ t('ui_cabinet_intermediary_bank') }}</h6>
                                        </div>
                                        <div class="col-md-7"><small class="d-block intermediary_bank"></small></div>
                                    </div>
                                    <div class="row mt-2 mt-md-0 swift-details" hidden>
                                        <div class="col-md-5"><h6 class="m-0 bank-details-rows">{{ t('ui_cabinet_intermediary_bank_swift') }}</h6>
                                        </div>
                                        <div class="col-md-7"><small class="d-block intermediary_bank_swift"></small>
                                        </div>
                                    </div>

                                    <div class="purpose-transfer-row row d-none reference-number mt-2 mt-md-0">
                                        <div class="col-md-5"><h6 class="m-0 d-none reference-number bank-details-rows">{{ t('wire_transfer_purpose_of_transfer') }}</h6>
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
    <script src="/js/cabinet/withdrawWire.js"></script>
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
                ask2fa.attachToFormSubmit('#withdraw_wire_form');
            });
        </script>
    @endif

@endsection

