@extends('cabinet.layouts.cabinet')
@section('title', t('ui_cards'))

@section('styles')
    <link href="{{ config('cratos.urls.theme') }}css/password_check.css?v={{ time() }}" rel="stylesheet">
@endsection

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
                <div class="m-2 pl-2 pt-1 pt-sm-0 cursor-pointer" id="prevPageBtn" data-save-limit-form-data-url="{{ route('wallester.card.order.save.limits') }}" data-prev-page-url="{{ $prevPageUrl }}">
                    <a style="color: black">
                        <i class="fa fa-arrow-left" aria-hidden="true"></i>
                    </a>
                </div>
                <div class="col m-2 pl-2 pt-1 pt-sm-0" style="max-width: 230px;">
                    <h4 class="d-inline-block mb-0">{{ t('card_settings') }}</h4>
                </div>
                @if($cardType == \App\Enums\WallesterCardTypes::TYPE_PLASTIC)
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
    <div class="mt-5 pl-3">
        <form method="post" id="wallesterCardOrderLimits" action="{{ route('order.card') }}" class="col-10">
            @csrf
            <div id="security" class="mt-5">
                <input type="hidden" value="{{ $cardType }}" name="type">
                <h2 class="wallesterOrderBlocks">{{ t('security') }}</h2>
                <div class="mt-4 d-flex align-content-between wallesterOrderSecurityBlock">
                    @if($cardType == \App\Enums\WallesterCardTypes::TYPE_PLASTIC)
                        <p class=" activeLink">
                            {{ t('cards_conditions_contactless_purchases') }}
                            <select class="mt-3 col-10" name="contactless_purchases"
                                    style="min-width: 100px; padding-right: 20px;">
                                @foreach(\App\Models\WallesterAccountDetail::SECURITY_YES_OR_NO as $value => $name)
                                    <option value="{{ $value }}" @if(isset($currentOrderData['contactless_purchases']) && $currentOrderData['contactless_purchases'] == $value) selected @endif>{{ $name }}</option>
                                @endforeach
                            </select>
                        </p>
                        @error('contactless_purchases')
                        <div class="error text-danger">{{ $message }}</div>
                        @enderror
                        <p class="activeLink">
                            {{ t('cards_conditions_atm_withdrawals') }}
                            <select class="mt-3 col-10" name="atm_withdrawals"
                                    style="min-width: 100px; padding-right: 20px;">
                                @foreach(\App\Models\WallesterAccountDetail::SECURITY_YES_OR_NO as $value => $name)
                                    <option value="{{ $value }}" @if(isset($currentOrderData['atm_withdrawals']) && $currentOrderData['atm_withdrawals'] == $value) selected @endif>{{ $name }}</option>
                                @endforeach
                            </select>
                        </p>
                        @error('atm_withdrawals')
                        <div class="error text-danger">{{ $message }}</div>
                        @enderror
                    @endif
                    <p class="activeLink">
                        {{ t('cards_conditions_purchases') }}
                        <select class="mt-3 col-10" name="internet_purchases"
                                style="min-width: 100px; padding-right: 20px;">
                            @foreach(\App\Models\WallesterAccountDetail::SECURITY_YES_OR_NO as $value => $name)
                                <option value="{{ $value }}" @if(isset($currentOrderData['internet_purchases']) && $currentOrderData['internet_purchases'] == $value) selected @endif>{{ $name }}</option>
                            @endforeach
                        </select>
                    </p>
                    @error('internet_purchases')
                    <div class="error text-danger">{{ $message }}</div>
                    @enderror
                    <p class="activeLink">
                        {{ t('cards_conditions_overall_limits_enabled') }}
                        <select class="mt-3 col-10" name="overall_limits_enabled"
                                style="min-width: 100px; padding-right: 20px;">
                            @foreach(\App\Models\WallesterAccountDetail::SECURITY_YES_OR_NO as $value => $name)
                                <option value="{{ $value }}" @if(isset($currentOrderData['overall_limits_enabled']) && $currentOrderData['overall_limits_enabled'] == $value) selected @endif>{{ $name }}</option>
                            @endforeach
                        </select>
                    </p>
                    @error('overall_limits_enabled')
                    <div class="error text-danger">{{ $message }}</div>
                    @enderror
                </div>
            </div>
            <div class="col-2 mt-5 p-0">
                <hr>
            </div>
            <div id="password3DS" class="mt-5">
                <h2 class="wallesterOrderBlocks">{{ t('wallester_card_3ds_password') }}</h2>
                {!! t('wallester_card_3ds_password_description') !!}

                <div class="mt-2 row wallester-order-password">
                    <div class="col-md-3">
                        <label class="activeLink" for="wallester_order_password">{{ t('wallester_card_3ds_password_input') }}
                            <span id="popover-password-top-hide" class="hide pull-right block-help"><i class="fa fa-info-circle text-danger" aria-hidden="true"></i> {{ t('enter_strong_password') }}</span></label>
                        <input maxlength="16" id="password" class="wallesterInputPassword" type="password"  id="wallester_order_password" name="password" value="{{ $currentOrderData['password'] ?? null }}" />
                        <span class="show-pass" data-id="password" data-state="0">
                            <i class="fa fa-eye fa-eye-slash" onclick="slashEye(this)" aria-hidden="true"></i>
                        </span>
                        <div class="progress" style="left: 20px; position: absolute;bottom: 32px;min-width: 70%;opacity: 0">
                            <div id="password-strength" class="progress-bar" role="progressbar" aria-valuenow="40" aria-valuemin="0" aria-valuemax="100" style="width:0%">
                            </div>
                        </div>
                        <div id="popover-password">

                            <p style="font-size: 10px">Password Strength: <span id="result"> </span></p>

                        </div>
                    </div>
                    <div class="col-md-3">
                        <label class="activeLink" for="wallester_order_password_confirm">{{ t('wallester_card_3ds_confirm_password_input') }}

                        </label>
                        <input maxlength="16" id="confirm-password" class="wallesterInputPassword" id="wallester_order_password_confirm" type="password" value="{{ $currentOrderData['password_confirmation'] ?? null }}" name="password_confirmation"/>
                        <span class="show-pass confirm-password"  data-id="confirm-password" data-state="0">
                            <i class="fa fa-eye fa-eye-slash" aria-hidden="true" onclick="slashEye(this)" aria-hidden="true"></i>
                        </span>
                        <div style="display: flex">
                            <span id="popover-cpassword" class="hide block-help"><i class="fa fa-info-circle text-danger" aria-hidden="true"></i>{{ t('password_not_match') }}</span>
                        </div>

                    </div>
                </div>
                @error('password')
                <div class="error text-danger">{{ $message }}</div>
                @enderror
            </div>
            <div class="col-2 mt-5 p-0">
                <hr>
            </div>

            <div id="transactionLimits" class="mt-5">
                <h2 class="wallesterOrderBlocks text-left">{{ t('wallester_transaction_limits') }}</h2>

                <div class="mt-4 d-flex align-content-between p-0 wallesterOrderLimitsBlock">
                    <div class="p-0">
                        <p class="activeLink font-15 mb-0">
                            {{ t('wallester_card_order_transaction_limits') }}
                            <input value="{{ $currentOrderData['limits']['transaction_purchase'] ?? ($limits['default_card_transaction_purchase_limit'] ?? '') }}"
                                   max="{{ $limits['max_card_transaction_purchase_limit'] }}"
                                   class="wallesterInputs w-50 limitsInputValue" name="limits[transaction_purchase]">
                            <br><small>(Max: {{ $limits['max_card_transaction_purchase_limit'] }})</small>

                        @error('limits.transaction_purchase')
                        <div class="error text-danger text-left">{{ $message }}</div>
                        @enderror
                        </p>
                    </div>
                    <div>
                        <p class="activeLink font-15 mb-0">
                            {{ t('wallester_card_order_transaction_withdrawal_limits') }}
                            <input class="wallesterInputs w-50 limitsInputValue" type="number"
                                   value="{{ $currentOrderData['limits']['transaction_withdrawal'] ?? ($limits['default_card_transaction_withdrawal_limit'] ?? '') }}"
                                   max="{{ $limits['max_card_transaction_withdrawal_limit'] }}"
                                   name="limits[transaction_withdrawal]">
                            <br><small>(Max: {{ $limits['max_card_transaction_withdrawal_limit'] }})</small>

                        @error('limits.transaction_withdrawal')
                        <div class="error text-danger text-left">{{ $message }}</div>
                        @enderror
                        </p>
                    </div>
                    <div>
                        <p class="activeLink font-15 mb-0">
                            {{ t('wallester_card_order_transaction_internet_limits') }}
                            <input class="wallesterInputs w-50 limitsInputValue"
                                   value="{{ $currentOrderData['limits']['transaction_withdrawal'] ?? ($limits['default_card_transaction_internet_purchase_limit'] ?? '') }}"
                                   max="{{ $limits['max_card_transaction_internet_purchase_limit'] }}"
                                   name="limits[transaction_internet_purchase]">
                            <br><small>(Max: {{ $limits['max_card_transaction_internet_purchase_limit'] }})</small>
                        @error('limits.transaction_internet_purchase')
                        <div class="error text-danger text-left">{{ $message }}</div>
                        @enderror
                        </p>
                    </div>
                    <div>
                        <p class="activeLink font-15 mb-0">
                            {{ t('wallester_card_order_transaction_contacless_limits') }}
                            <input class="wallesterInputs w-50 limitsInputValue"
                                   value="{{ $currentOrderData['limits']['transaction_contactless_purchase'] ?? ($limits['default_card_transaction_contactless_purchase_limit'] ?? '') }}"
                                   max="{{ $limits['max_card_transaction_contactless_purchase_limit'] }}"
                                   name="limits[transaction_contactless_purchase]">
                            <br><small>(Max: {{ $limits['max_card_transaction_contactless_purchase_limit'] }})</small>

                        @error('limits.transaction_contactless_purchase')
                        <div class="error text-danger text-left">{{ $message }}</div>
                        @enderror
                        </p>
                    </div>
                </div>
            </div>

            <div id="cardLimits" class="mt-5">
                <h2 class="wallesterOrderBlocks text-left">{{ t('wallester_daily_limits') }}</h2>
                <div class="mt-4 d-flex align-content-between p-0 wallesterOrderLimitsBlock">
                    <div class="p-0">
                        <p class="activeLink font-15 mb-0">
                            {{ t('wallester_card_order_daily_limits') }}
                            <input value="{{  $currentOrderData['limits']['daily_purchase'] ?? ($limits['default_card_daily_purchase_limit'] ?? '') }}"
                                   max="{{ $currentOrderData['limits']['transaction_contactless_purchase'] ?? $limits['max_card_daily_purchase_limit'] }}"
                                   class="wallesterInputs w-50 limitsInputValue" name="limits[daily_purchase]">
                            <br><small>(Max: {{ $currentOrderData['limits']['transaction_contactless_purchase'] ?? $limits['max_card_daily_purchase_limit'] }})</small>

                        @error('limits.daily_purchase')
                        <div class="error text-danger text-left">{{ $message }}</div>
                        @enderror
                        </p>
                    </div>
                    <div>
                        <p class="activeLink font-15 mb-0">
                            {{ t('wallester_card_order_withdrawal_limits') }}
                            <input class="wallesterInputs w-50 limitsInputValue" type="number"
                                   value="{{ $currentOrderData['limits']['max_card_daily_withdrawal_limit'] ?? ($limits['default_card_daily_withdrawal_limit'] ?? '') }}"
                                   max="{{ $limits['max_card_daily_withdrawal_limit'] }}"
                                   name="limits[daily_withdrawal]">
                            <br><small>(Max: {{ $limits['max_card_daily_withdrawal_limit'] }})</small>

                        @error('limits.daily_withdrawal')
                        <div class="error text-danger text-left">{{ $message }}</div>
                        @enderror
                        </p>
                    </div>
                    <div>
                        <p class="activeLink font-15 mb-0">
                            {{ t('wallester_card_order_internet_limits') }} <br>
                            <input class="wallesterInputs w-50 limitsInputValue"
                                   value="{{ $currentOrderData['limits']['daily_internet_purchase'] ?? ($limits['default_card_daily_internet_purchase_limit'] ?? '') }}"
                                   max="{{ $limits['max_card_daily_internet_purchase_limit'] }}"
                                   name="limits[daily_internet_purchase]">
                            <br><small>(Max: {{ $limits['max_card_daily_internet_purchase_limit'] }})</small>

                        @error('limits.daily_internet_purchase')
                        <div class="error text-danger text-left">{{ $message }}</div>
                        @enderror
                        </p>
                    </div>
                    <div>
                        <p class="activeLink font-15 mb-0">
                            {{ t('wallester_card_order_contacless_limits') }}
                            <input class="wallesterInputs w-50 limitsInputValue"
                                   value="{{ $currentOrderData['limits']['daily_contactless_purchase'] ?? ($limits['default_card_daily_contactless_purchase_limit'] ?? '') }}"
                                   max="{{ $limits['max_card_daily_contactless_purchase_limit'] }}"
                                   name="limits[daily_contactless_purchase]">
                            <br><small>(Max: {{ $limits['max_card_daily_contactless_purchase_limit'] }})</small>

                        @error('limits.daily_contactless_limit')
                        <div class="error text-danger text-left">{{ $message }}</div>
                        @enderror
                        </p>
                    </div>
                </div>
            </div>
            <div id="weeklyLimits" class="mt-5">
                <h2 class="wallesterOrderBlocks text-left">{{ t('wallester_weekly_limits') }}</h2>
                <div class="mt-4 d-flex align-content-between p-0 wallesterOrderLimitsBlock">
                    <div class="p-0">
                        <p class="activeLink font-15 mb-0">
                            {{ t('wallester_card_order_weekly_limits') }}
                            <input class="wallesterInputs w-50 limitsInputValue"
                                   value="{{ $currentOrderData['limits']['weekly_purchase'] ?? ($limits['default_card_weekly_purchase_limit'] ?? '') }}"
                                   max="{{ $limits['max_card_weekly_purchase_limit'] }}"
                                   name="limits[weekly_purchase]">
                            <br><small>(Max: {{ $limits['max_card_weekly_purchase_limit'] }})</small>

                        </p>
                        @error('limits.weekly_purchase')
                        <div class="error text-danger text-left">{{ $message }}</div>
                        @enderror
                    </div>
                    <div>
                        <p class="activeLink font-15 mb-0">
                            {{ t('wallester_card_order_weekly_withdrawal_limits') }}
                            <input class="wallesterInputs w-50 limitsInputValue"
                                   value="{{ $currentOrderData['limits']['weekly_withdrawal'] ?? ($limits['default_card_weekly_withdrawal_limit'] ?? '') }}"
                                   max="{{ $limits['max_card_weekly_withdrawal_limit'] }}"
                                   name="limits[weekly_withdrawal]">
                            <br><small>(Max: {{ $limits['max_card_weekly_withdrawal_limit'] }})</small>

                        </p>
                        @error('limits.weekly_withdrawal')
                        <div class="error text-danger text-left">{{ $message }}</div>
                        @enderror
                    </div>
                    <div>
                        <p class="activeLink font-15 mb-0">
                            {{ t('wallester_card_order_weekly_internet_limits') }}
                            <input class="wallesterInputs w-50 limitsInputValue"
                                   value="{{ $currentOrderData['limits']['weekly_internet_purchase'] ?? ($limits['default_card_weekly_internet_purchase_limit'] ?? '') }}"
                                   max="{{ $limits['max_card_weekly_internet_purchase_limit'] }}"
                                   name="limits[weekly_internet_purchase]">
                            <br><small>(Max: {{ $limits['max_card_weekly_internet_purchase_limit'] }})</small>

                        </p>
                        @error('limits.weekly_internet_purchase')
                        <div class="error text-danger text-left">{{ $message }}</div>
                        @enderror
                    </div>
                    <div>
                        <p class="activeLink font-15 mb-0">
                            {{ t('wallester_card_order_weekly_contacless_limits') }}
                            <input class="wallesterInputs w-50 limitsInputValue"
                                   value="{{ $currentOrderData['limits']['weekly_contactless_purchase'] ?? ($limits['default_card_weekly_contactless_purchase_limit'] ?? '') }}"
                                   max="{{ $limits['max_card_weekly_contactless_purchase_limit'] }}"
                                   name="limits[weekly_contactless_purchase]">
                            <br><small>(Max: {{ $limits['max_card_weekly_contactless_purchase_limit'] }})</small>

                        </p>
                        @error('limits.weekly_contactless_purchase')
                        <div class="error text-danger text-left">{{ $message }}</div>
                        @enderror
                    </div>
                </div>
            </div>
            <div id="monthlyLimits" class="mt-5">
                <h2 class="wallesterOrderBlocks text-left">{{ t('wallester_monthly_limits') }}</h2>
                <div class="mt-4 d-flex align-content-between p-0 wallesterOrderLimitsBlock">
                    <div class="p-0">
                        <p class="activeLink font-15 mb-0">
                            {{ t('wallester_card_order_monthly_limits') }}
                            <input class="wallesterInputs w-50 limitsInputValue"
                                   value="{{ $currentOrderData['limits']['monthly_purchase'] ?? ($limits['default_card_monthly_purchase_limit'] ?? '') }}"
                                   max="{{ $limits['max_card_monthly_purchase_limit'] }}"
                                   name="limits[monthly_purchase]">
                            <br><small>(Max: {{ $limits['max_card_monthly_purchase_limit'] }})</small>

                        </p>
                        @error('limits.monthly_purchase')
                        <div class="error text-danger text-left">{{ $message }}</div>
                        @enderror
                    </div>
                    <div>
                        <p class="activeLink font-15 mb-0">
                            {{ t('wallester_card_order_monthly_withdrawal_limits') }}
                            <input class="wallesterInputs w-50 limitsInputValue"
                                   value="{{ $currentOrderData['limits']['monthly_withdrawal'] ?? ($limits['default_card_monthly_withdrawal_limit'] ?? '') }}"
                                   max="{{ $limits['max_card_monthly_withdrawal_limit'] }}"
                                   name="limits[monthly_withdrawal]">
                            <br><small>(Max: {{ $limits['max_card_monthly_withdrawal_limit'] }})</small>

                        </p>
                        @error('limits.monthly_withdrawal')
                        <div class="error text-danger text-left">{{ $message }}</div>
                        @enderror
                    </div>
                    <div>
                        <p class="activeLink font-15 mb-0">
                            {{ t('wallester_card_order_monthly_internet_limits') }}
                            <input class="wallesterInputs w-50 limitsInputValue"
                                   value="{{ $currentOrderData['limits']['monthly_internet_purchase'] ?? ($limits['default_card_monthly_internet_purchase_limit'] ?? '') }}"
                                   max="{{ $limits['max_card_monthly_internet_purchase_limit'] }}"
                                   name="limits[monthly_internet_purchase]">
                            <br><small>(Max: {{ $limits['max_card_monthly_internet_purchase_limit'] }})</small>

                        </p>
                        @error('limits.monthly_internet_purchase')
                        <div class="error text-danger text-left">{{ $message }}</div>
                        @enderror
                    </div>
                    <div>
                        <p class="activeLink font-15 mb-0">
                            {{ t('wallester_card_order_monthly_contacless_limits') }}
                            <input class="wallesterInputs w-50 limitsInputValue"
                                   value="{{ $currentOrderData['limits']['monthly_contactless_purchase'] ?? ($limits['default_card_monthly_contactless_purchase_limit'] ?? '') }}"
                                   max="{{ $limits['max_card_monthly_contactless_purchase_limit'] }}"
                                   name="limits[monthly_contactless_purchase]">
                            <br><small>(Max: {{ $limits['max_card_monthly_contactless_purchase_limit'] }})</small>

                        </p>
                        @error('limits.monthly_contactless_purchase')
                        <div class="error text-danger text-left">{{ $message }}</div>
                        @enderror
                    </div>
                </div>
            </div>


            <div class="mt-5 ml-1">
                <button class="btn btn-lg btn-primary themeBtn register-buttons mb-4 mb-md-0 mr-3"
                        type="submit"> {{ t('save') }} </button>
            </div>
        </form>
    </div>
@endsection
@section('scripts')
    <script src="/js/cabinet/wallester-order-card.js"></script>
    <script src="/js/password_check.js"></script>

@endsection
