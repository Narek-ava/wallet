@extends('cabinet.layouts.cabinet')
@section('title', t('ui_cards'))

@section('content')
    <div class="row mb-4">
        <div class="col-md-12">
            <div class="row mb-5">
                <div class="col-md-12">
                    <h2 class="mb-3 large-heading-section page-title">{{ t('total_balance') }}</h2>
                    <div class="row">
                        <div class="col-md-5 d-flex justify-content-between">
                            <div class="balance">
                                {{ t('ui_webatach_request') }}
                            </div>
                        </div>
                        @include('cabinet.partials.notification', ['notify' => getNotification(\Illuminate\Support\Facades\Auth::id()), 'admin' => false])
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="row mt-5 mb-5">
        <div class="col-md-12">
            <div class="d-flex flex-row justify-content-start">
                <div class="m-2 pl-2 pt-1 pt-sm-0 cursor-pointer">
                    <a href="{{ $prevPageUrl }}" style="color: black">
                        <i class="fa fa-arrow-left" aria-hidden="true"></i>
                    </a>
                </div>
                <div class="col m-2 pl-2 pt-1 pt-sm-0" style="max-width: 300px;">
                    <h4 class="d-inline-block mb-0">{{ t('card_summary') }}</h4>
                </div>
                @if($currentOrderData['type'] == \App\Enums\WallesterCardTypes::TYPE_PLASTIC)
                    <div class="col mt-1 pl-0 pl-sm-2 orderPlasticSteps orderSteps">
                        @include('cabinet.cards.order-steps.order-plastic-steps')
                    </div>
                @else
                    <div class="col mt-1 pl-0 pl-sm-2 orderVirtualSteps orderSteps">
                        @include('cabinet.cards.order-steps.order-virtual-steps')
                    </div>
                @endif
            </div>
        </div>
    </div>




    <div id="summary">
        <div class="row pl-3 pr-3">
            <div class="col-md-7" style="max-width: 500px;">
                <div class="row py-2 px-0 mt-3 wallet-border-pink p-3">
                    <div class="col-md-12">

                        <input type="hidden" name="type" value="{{ $currentOrderData['type'] }}">
                        <h6 class="font-weight-bold mb-0 themeColorRed">{{ t('wallester_card_summary') }}</h6>

                        <div class="row mt-3 d-flex align-content-around">
                            <div class="div col-6 d-flex flex-column">
                                <h6 class="font-weight-bold mb-0">{{ t('wallester_card_type') }}</h6>
                                <p class="topup-fee mb-2">{{ \App\Enums\WallesterCardTypes::getName($currentOrderData['type']) }}</p>
                            </div>
                            <div class="div col-6 d-flex flex-column">
                                <h6 class="font-weight-bold mb-0">{{ t('wallester_embossed_name') }}</h6>
                                <p class="blockchain-fee mb-2">
                                    {{ getCProfile()->getFullName() }}
                                </p>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-12 mt-3">
                        <h6 class="font-weight-bold mb-0 themeColorRed">{{ t('wallester_card_security') }}</h6>
                        <div class="row mt-3 d-flex align-content-around">
                            @if($currentOrderData['type'] == \App\Enums\WallesterCardTypes::TYPE_PLASTIC)
                                <div class="div col-6 d-flex flex-column">
                                    <h6 class="font-weight-bold mb-0">{{ t('cards_conditions_contactless_purchases') }}</h6>
                                    <p class="topup-fee mb-2">{{ \App\Models\WallesterAccountDetail::SECURITY_YES_OR_NO[$currentOrderData['contactless_purchases']] ?? '-' }}</p>
                                </div>
                                <div class="div col-6 d-flex flex-column">
                                    <h6 class="font-weight-bold mb-0">{{ t('cards_conditions_atm_withdrawals') }}</h6>
                                    <p class="blockchain-fee mb-2">{{ \App\Models\WallesterAccountDetail::SECURITY_YES_OR_NO[$currentOrderData['atm_withdrawals']] ?? '-' }}</p>
                                </div>
                            @endif
                            <div class="div col-6 d-flex flex-column">
                                <h6 class="font-weight-bold mb-0">{{ t('cards_conditions_purchases') }}</h6>
                                <p class="topup-fee mb-2">{{ \App\Models\WallesterAccountDetail::SECURITY_YES_OR_NO[$currentOrderData['internet_purchases']] ?? '-' }}</p>
                            </div>
                            <div class="div col-6 d-flex flex-column">
                                <h6 class="font-weight-bold mb-0">{{ t('wallester_card_overall_limits') }}</h6>
                                <p class="blockchain-fee mb-2">{{ \App\Models\WallesterAccountDetail::SECURITY_YES_OR_NO[$currentOrderData['overall_limits_enabled']] ?? '-' }}</p>
                            </div>
                            @if($currentOrderData['type'] == \App\Enums\WallesterCardTypes::TYPE_VIRTUAL)
                                <div class="div col-6 d-flex flex-column">
                                    <h6 class="font-weight-bold mb-0">{{ t('issuing') }}</h6>
                                    <p class="topup-fee mb-2">{{ t('instantly') }}</p>
                                </div>
                                <div class="div col-6 d-flex flex-column">
                                    <h6 class="font-weight-bold mb-0">{{ t('card_issuing_fee') }}</h6>
                                    <p class="blockchain-fee mb-2">{{ isset($issuingFee) ? generalMoneyFormat($issuingFee, \App\Enums\Currency::CURRENCY_EUR) . ' ' . \App\Enums\Currency::CURRENCY_EUR : '-' }}</p>
                                </div>
                            @endif
                        </div>
                    </div>
                    @if($currentOrderData['type'] == \App\Enums\WallesterCardTypes::TYPE_PLASTIC)
                        <div class="col-md-12 mt-3">
                            <h6 class="font-weight-bold mb-0 themeColorRed">{{ t('card_delivery_address') }}</h6>
                            <div class="row mt-3 d-flex align-content-around">
                                <div class="div col-6 d-flex flex-column">
                                    <h6 class="font-weight-bold mb-0">{{ t('wallester_card_order_first_name') }}</h6>
                                    <p class="topup-fee mb-2">{{ $currentOrderData['delivery']['first_name'] ?? '-' }}</p>
                                </div>
                                <div class="div col-6 d-flex flex-column">
                                    <h6 class="font-weight-bold mb-0">{{ t('wallester_card_order_last_name') }}</h6>
                                    <p class="blockchain-fee mb-2">{{ $currentOrderData['delivery']['last_name'] ?? '-' }}</p>
                                </div>
                                <div class="div col-6 d-flex flex-column">
                                    <h6 class="font-weight-bold mb-0">{{ t('wallester_card_postal_code') }}</h6>
                                    <p class="topup-fee mb-2">{{ $currentOrderData['delivery']['postal_code'] ?? '-' }}</p>
                                </div>
                                <div class="div col-6 d-flex flex-column">
                                    <h6 class="font-weight-bold mb-0">{{ t('ui_cprofile_city') }}</h6>
                                    <p class="blockchain-fee mb-2">{{ $currentOrderData['delivery']['city'] ?? '-' }}</p>
                                </div>
                                <div class="div col-6 d-flex flex-column">
                                    <h6 class="font-weight-bold mb-0">{{ t('wallester_card_order_address1') }}</h6>
                                    <p class="topup-fee mb-2">{{ $currentOrderData['delivery']['address1'] ?? '-' }}</p>
                                </div>
                                <div class="div col-6 d-flex flex-column">
                                    <h6 class="font-weight-bold mb-0">{{ t('wallester_card_order_address2') }}</h6>
                                    <p class="blockchain-fee mb-2">{{ $currentOrderData['delivery']['address2'] ?? '-' }}</p>
                                </div>
                                <div class="div col-6 d-flex flex-column">
                                    <h6 class="font-weight-bold mb-0">{{ t('ui_country_code') }}</h6>
                                    <p class="blockchain-fee mb-2">{{ $currentOrderData['delivery']['country_code'] ?? '-' }}</p>
                                </div>
                                <div class="div col-6 d-flex flex-column">
                                    <h6 class="font-weight-bold mb-0"></h6>
                                    <p class="blockchain-fee mb-2"></p>
                                </div>
                                <div class="div col-6 d-flex flex-column">
                                    <h6 class="font-weight-bold mb-0">{{ t('cards_conditions_delivery_time') }}</h6>
                                    <p class="blockchain-fee mb-2">5-6 days</p>
                                </div>
                                <div class="div col-6 d-flex flex-column">
                                    <h6 class="font-weight-bold mb-0">{{ t('cards_conditions_issuing_fee') }}</h6>
                                    <p class="blockchain-fee mb-2">{{ isset($issuingFee) ? generalMoneyFormat($issuingFee, \App\Enums\Currency::CURRENCY_EUR) . ' ' . \App\Enums\Currency::CURRENCY_EUR : '-' }}</p>
                                </div>
                            </div>
                        </div>
                    @endif
                    <div class="col-md-12 mt-3">
                        <p>
                           <span class="show-pass"  data-id="confirm-password" data-state="0" data-target="#cardsConditions" data-toggle="modal">
                            <i class="fa fa-eye-slash" aria-hidden="true" onclick="slashEye(this)" aria-hidden="true"></i>
                        </span> see full card conditions

                        </p>
                        @include('cabinet.cards._cards-conditions')
                    </div>
                </div>
            </div>
        </div>
        <br><br>

        <div class="d-flex align-items-center pl-2">
            <input id="agreeTerms" type="checkbox" name="agreeTerms[]" required class="agreeTermsCheckbox"
                   style="width: max-content; margin-right: 15px; transform: scale(2)"/>
            <label for="agreeTerms" class="w-100" style="max-width: 575px; margin-bottom: unset">
                {!! t('wallester_card_order_terms_and_conditions_agreement', ['link' => config('cratos.wallester.terms_and_conditions')]) !!}
            </label>
        </div>
        <div class="d-flex align-items-center pl-2 mt-3">
            <input id="agreeAcknowledge" type="checkbox" name="agreeTerms[]" required class="agreeTermsCheckbox"
                   style="width: max-content; margin-right: 15px; transform: scale(2)"/>
            <label for="agreeAcknowledge" class="w-100" style="max-width: 575px; margin-bottom: unset">
                {{ t('wallester_card_order_agree_acknowledge') }}
            </label>
        </div>
        <small
            class="terms-fail-message text-danger mt-2 mb-2 d-none">{{ t('terms_and_agreements_checkbox_not_checked') }}</small>
        <small
            class="provider-status-suspended text-danger mt-2 mb-2 d-none">{{ t('provider_status_is_suspended')  }}</small>
        <div class="buttons mt-5">
            <button class="btn btn-primary themeBtn btn-lg btnWhiteSpace loader ml-1"
                    data-sent="false"
                    disabled data-toggle="modal"
                    type="button" data-target="#paymentMethod" id="confirmBtn">Proceed
                to card order
            </button>
            @include('cabinet.cards._payment-method', ['cardType' => $currentOrderData['type']])
            <p class="p-0">
                <img src="{{ asset('/cratos.theme/images/' . 'payment_logos.png') }}" width="20%" class="m-0" alt="">
            </p>
        </div>

    </div>

@endsection

@section('scripts')
    <script>
        $('.agreeTermsCheckbox').on('change', function () {
            var checkedNum = $('input[name="agreeTerms[]"]:checked').length;
            if (checkedNum === 2) {
                $('#confirmBtn').attr('disabled', false)
            } else {
                $('#confirmBtn').attr('disabled', true)
            }
        })

        $('#confirmBtn').on('click', function () {
            if ($(this).attr('data-sent') === 'false') {
                $.ajaxSetup({
                    headers: {
                        'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                    }
                });
                $.ajax({
                    type: "POST",
                    url: "{{ route('wallester.confirm.card.order.summary') }}",
                    data: {
                        "type": $('input[name="type"]').val()
                    },
                    success: (response) => {
                        $(this).attr('data-sent', 'true')
                    },
                    error: (error) => {
                    }
                });
            }

        })

    </script>
@endsection
