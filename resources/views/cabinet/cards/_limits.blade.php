<div class="modal fade login-popup rounded-0" id="card-limits-wallester" tabindex="-1" role="dialog"
     aria-labelledby="exampleModalCenterTitle" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered" role="document">
        <div class="modal-content modal-content-centered" style="width: unset">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">×</span>
                </button>
                <div class="w-100 d-flex flex-column ml-3" style="margin-top: 20px; text-align: start">
                    <h2 class="wallesterOrderBlocks">{{ t('wallester_card_limits') }}</h2>
                    {{ t('manage_your_limits') }}
                </div>
            </div>
            <form method="POST" id="card-limit-update-form" action="{{ route('wallester.update.card.limits', ['id' => $card->id]) }}">
                @csrf
                @method('PATCH')
                <div class="modal-body">
                    <div class="row">
                        <div id="transactionLimits" class="mt-5">
                            <h2 class="wallesterOrderBlocks text-left ml-3">{{ t('wallester_transaction_limits') }}</h2>
                            <div class="mt-4 col-12 d-flex align-items-start">
                                <div class="p-0">
                                    <p class="activeLink font-15 mb-0">
                                        {{ t('wallester_card_order_transaction_limits') }}
                                        <input value="{{ $limits['transaction_purchase'] ?? '' }}"
                                               max="{{ $defaultLimits['max_card_transaction_purchase_limit'] }}"
                                               class="wallesterInputs limitsInputValue" name="limits[transaction_purchase]">
                                        <small>(Max: {{ $defaultLimits['max_card_transaction_purchase_limit'] }})</small>
                                    @error('limits.transaction_purchase')
                                    <div class="error text-danger text-left">{{ $message }}</div>
                                    @enderror
                                    </p>
                                </div>
                                <div>
                                    <p class="activeLink font-15 mb-0">
                                        {{ t('wallester_card_order_transaction_withdrawal_limits') }}
                                        <input class="wallesterInputs limitsInputValue" type="number"
                                               value="{{ $limits['transaction_withdrawal'] ?? '' }}"
                                               max="{{ $defaultLimits['max_card_transaction_withdrawal_limit'] }}"
                                               name="limits[transaction_withdrawal]">
                                        <small>(Max: {{ $defaultLimits['max_card_transaction_withdrawal_limit'] }})</small>

                                    @error('limits.transaction_withdrawal')
                                    <div class="error text-danger text-left">{{ $message }}</div>
                                    @enderror
                                    </p>
                                </div>
                                <div>
                                    <p class="activeLink font-15 mb-0">
                                        {{ t('wallester_card_order_transaction_internet_limits') }}
                                        <input class="wallesterInputs limitsInputValue"
                                               value="{{ $limits['transaction_internet_purchase'] ?? '' }}"
                                               max="{{ $defaultLimits['max_card_transaction_internet_purchase_limit'] }}"
                                               name="limits[transaction_internet_purchase]">
                                        <small>(Max: {{ $defaultLimits['max_card_transaction_internet_purchase_limit'] }})</small>

                                    @error('limits.transaction_internet_purchase')
                                    <div class="error text-danger text-left">{{ $message }}</div>
                                    @enderror
                                    </p>
                                </div>
                                <div>
                                    <p class="activeLink font-15 mb-0">
                                        {{ t('wallester_card_order_transaction_contacless_limits') }}
                                        <input class="wallesterInputs limitsInputValue"
                                               value="{{ $limits['transaction_contactless_purchase'] ?? '' }}"
                                               max="{{ $defaultLimits['max_card_transaction_contactless_purchase_limit'] }}"
                                               name="limits[transaction_contactless_purchase]">
                                        <small>(Max: {{ $defaultLimits['max_card_transaction_contactless_purchase_limit'] }})</small>

                                    @error('limits.transaction_contactless_purchase')
                                    <div class="error text-danger text-left">{{ $message }}</div>
                                    @enderror
                                    </p>
                                </div>
                            </div>
                        </div>

                        <div id="cardLimits" class="mt-5">
                            <h2 class="wallesterOrderBlocks text-left ml-3">{{ t('wallester_daily_limits') }}</h2>
                            <div class="mt-4 col-12 d-flex align-items-start">
                                <div class="p-0">
                                    <p class="activeLink font-15 mb-0">
                                        {{ t('wallester_card_order_daily_limits') }}
                                        <input value="{{ $limits['daily_purchase']['total'] ?? '' }}"
                                               max="{{ $defaultLimits['max_card_daily_purchase_limit'] }}"
                                               class="wallesterInputs limitsInputValue" name="limits[daily_purchase]">
                                        <small>(Max: {{ $currentOrderData['limits']['transaction_contactless_purchase'] ?? $defaultLimits['max_card_daily_purchase_limit'] }})</small>

                                    @error('limits.daily_purchase')
                                    <div class="error text-danger text-left">{{ $message }}</div>
                                    @enderror
                                    </p>
                                </div>
                                <div>
                                    <p class="activeLink font-15 mb-0">
                                        {{ t('wallester_card_order_withdrawal_limits') }}
                                        <input class="wallesterInputs limitsInputValue" type="number"
                                               value="{{ $limits['daily_withdrawal']['total'] ?? '' }}"
                                               max="{{ $defaultLimits['max_card_daily_withdrawal_limit'] }}"
                                               name="limits[daily_withdrawal]">
                                        <small>(Max: {{ $defaultLimits['max_card_daily_withdrawal_limit'] }})</small>
                                    @error('limits.daily_withdrawal')
                                    <div class="error text-danger text-left">{{ $message }}</div>
                                    @enderror
                                    </p>
                                </div>
                                <div>
                                    <p class="activeLink font-15 mb-0">
                                        {{ t('wallester_card_order_internet_limits') }} <br>
                                        <input class="wallesterInputs limitsInputValue"
                                               value="{{ $limits['daily_internet_purchase']['total'] ?? '' }}"
                                               max="{{ $defaultLimits['max_card_daily_internet_purchase_limit'] }}"
                                               name="limits[daily_internet_purchase]">
                                        <small>(Max: {{ $defaultLimits['max_card_daily_internet_purchase_limit'] }})</small>

                                    @error('limits.daily_internet_purchase')
                                    <div class="error text-danger text-left">{{ $message }}</div>
                                    @enderror
                                    </p>
                                </div>
                                <div>
                                    <p class="activeLink font-15 mb-0">
                                        {{ t('wallester_card_order_contacless_limits') }}
                                        <input class="wallesterInputs limitsInputValue"
                                               value="{{ $limits['daily_contactless_purchase']['total'] ?? '' }}"
                                               max="{{ $defaultLimits['max_card_daily_contactless_purchase_limit'] }}"
                                               name="limits[daily_contactless_purchase]">
                                        <small>(Max: {{ $defaultLimits['max_card_daily_contactless_purchase_limit'] }})</small>

                                    @error('limits.daily_contactless_limit')
                                    <div class="error text-danger text-left">{{ $message }}</div>
                                    @enderror
                                    </p>
                                </div>
                            </div>
                        </div>

                        <div id="weeklyLimits" class="mt-5">
                            <h2 class="wallesterOrderBlocks text-left ml-3">{{ t('wallester_weekly_limits') }}</h2>
                            <div class="mt-4 col-12 d-flex align-items-start">
                                <div class="p-0">
                                    <p class="activeLink font-15 mb-0">
                                        {{ t('wallester_card_order_weekly_limits') }}
                                        <input class="wallesterInputs limitsInputValue"
                                               value="{{ $limits['weekly_purchase']['total'] ?? '' }}"
                                               max="{{ $defaultLimits['max_card_weekly_purchase_limit'] }}"
                                               name="limits[weekly_purchase]">
                                        <small>(Max: {{ $defaultLimits['max_card_weekly_purchase_limit'] }})</small>

                                    </p>
                                    @error('limits.weekly_purchase')
                                    <div class="error text-danger text-left">{{ $message }}</div>
                                    @enderror
                                </div>
                                <div>
                                    <p class="activeLink font-15 mb-0">
                                        {{ t('wallester_card_order_weekly_withdrawal_limits') }}
                                        <input class="wallesterInputs limitsInputValue"
                                               value="{{ $limits['weekly_withdrawal']['total'] ?? '' }}"
                                               max="{{ $defaultLimits['max_card_weekly_withdrawal_limit'] }}"
                                               name="limits[weekly_withdrawal]">
                                        <small>(Max: {{ $defaultLimits['max_card_weekly_withdrawal_limit'] }})</small>

                                    </p>
                                    @error('limits.weekly_withdrawal')
                                    <div class="error text-danger text-left">{{ $message }}</div>
                                    @enderror
                                </div>
                                <div>
                                    <p class="activeLink font-15 mb-0">
                                        {{ t('wallester_card_order_weekly_internet_limits') }}
                                        <input class="wallesterInputs limitsInputValue"
                                               value="{{ $limits['weekly_internet_purchase']['total'] ?? '' }}"
                                               max="{{ $defaultLimits['max_card_weekly_internet_purchase_limit'] }}"
                                               name="limits[weekly_internet_purchase]">
                                        <small>(Max: {{ $defaultLimits['max_card_weekly_internet_purchase_limit'] }})</small>

                                    </p>
                                    @error('limits.weekly_internet_purchase')
                                    <div class="error text-danger text-left">{{ $message }}</div>
                                    @enderror
                                </div>
                                <div>
                                    <p class="activeLink font-15 mb-0">
                                        {{ t('wallester_card_order_weekly_contacless_limits') }}
                                        <input class="wallesterInputs limitsInputValue"
                                               value="{{ $limits['weekly_contactless_purchase']['total'] ?? '' }}"
                                               max="{{ $defaultLimits['max_card_weekly_contactless_purchase_limit'] }}"
                                               name="limits[weekly_contactless_purchase]">
                                        <small>(Max: {{ $defaultLimits['max_card_weekly_contactless_purchase_limit'] }})</small>

                                    </p>
                                    @error('limits.weekly_contactless_purchase')
                                    <div class="error text-danger text-left">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                        </div>

                        <div id="monthlyLimits" class="mt-5">
                            <h2 class="wallesterOrderBlocks text-left ml-3">{{ t('wallester_monthly_limits') }}</h2>
                            <div class="mt-4 col-12 d-flex align-items-start">
                                <div class="p-0">
                                    <p class="activeLink font-15 mb-0">
                                        {{ t('wallester_card_order_monthly_limits') }}
                                        <input class="wallesterInputs limitsInputValue"
                                               value="{{ $limits['monthly_purchase']['total'] ?? '' }}"
                                               max="{{ $defaultLimits['max_card_monthly_purchase_limit'] }}"
                                               name="limits[monthly_purchase]">
                                        <small>(Max: {{ $defaultLimits['max_card_monthly_purchase_limit'] }})</small>

                                    </p>
                                    @error('limits.monthly_purchase')
                                    <div class="error text-danger text-left">{{ $message }}</div>
                                    @enderror
                                </div>
                                <div>
                                    <p class="activeLink font-15 mb-0">
                                        {{ t('wallester_card_order_monthly_withdrawal_limits') }}
                                        <input class="wallesterInputs limitsInputValue"
                                               value="{{ $limits['monthly_withdrawal']['total'] ?? '' }}"
                                               max="{{ $defaultLimits['max_card_monthly_withdrawal_limit'] }}"
                                               name="limits[monthly_withdrawal]">
                                        <small>(Max: {{ $defaultLimits['max_card_monthly_withdrawal_limit'] }})</small>

                                    </p>
                                    @error('limits.monthly_withdrawal')
                                    <div class="error text-danger text-left">{{ $message }}</div>
                                    @enderror
                                </div>
                                <div>
                                    <p class="activeLink font-15 mb-0">
                                        {{ t('wallester_card_order_monthly_internet_limits') }}
                                        <input class="wallesterInputs limitsInputValue"
                                               value="{{ $limits['monthly_internet_purchase']['total'] ?? '' }}"
                                               max="{{ $defaultLimits['max_card_monthly_internet_purchase_limit'] }}"
                                               name="limits[monthly_internet_purchase]">
                                        <small>(Max: {{ $defaultLimits['max_card_monthly_internet_purchase_limit'] }})</small>

                                    </p>
                                    @error('limits.monthly_internet_purchase')
                                    <div class="error text-danger text-left">{{ $message }}</div>
                                    @enderror
                                </div>
                                <div>
                                    <p class="activeLink font-15 mb-0">
                                        {{ t('wallester_card_order_monthly_contacless_limits') }}
                                        <input class="wallesterInputs limitsInputValue"
                                               value="{{ $limits['monthly_contactless_purchase']['total'] ?? '' }}"
                                               max="{{ $defaultLimits['max_card_monthly_contactless_purchase_limit'] }}"
                                               name="limits[monthly_contactless_purchase]">
                                        <small>(Max: {{ $defaultLimits['max_card_monthly_contactless_purchase_limit'] }})</small>

                                    </p>
                                    @error('limits.monthly_contactless_purchase')
                                    <div class="error text-danger text-left">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer mb-5">
                    <div class="w-100 ml-3 d-flex justify-content-start">
                        <button id="updateLimitsBtn" class="btn btn-lg btn-primary themeBtn register-buttons" type="button">
                            {{ t('save') }}
                        </button>
                    </div>
                </div>
            </form>
        </div>
    </div>
</div>

