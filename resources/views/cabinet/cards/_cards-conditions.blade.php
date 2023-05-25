<div class="modal fade login-popup rounded-0" id="cardsConditions" tabindex="-1" role="dialog"
     aria-labelledby="exampleModalCenterTitle" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered" role="document">
        <div class="modal-content modal-content-centered">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">×</span>
                </button>
                <div class="w-100 d-flex flex-column ml-3" style="margin-top: 20px; text-align: start">
                    <h2 class="wallesterOrderBlocks">{{ t('cards_conditions') }}</h2>
                    {{ t('cards_conditions_description') }}
                </div>
            </div>
            <div class="modal-body">
                <div class="mt-2">
                    <h2 class="wallesterOrderBlocks text-left">{{ t('cards_conditions_overview') }}</h2>
                    <div class="mt-4 d-flex wallester-conditions-parent">
                        <div class="wallester-card-condition-blocks   p-0">
                            <label class="activeLink font-15 mb-0" for="currencyCondition"> {{ t('currency') }}</label>
                            <input id="currencyCondition" readonly value="{{ \App\Enums\Currency::CURRENCY_EUR }}"
                                   class="wallesterInputs mt-3 ml-0 mr-0">
                        </div>
                        <div class="wallester-card-condition-blocks">
                            <label class="activeLink font-15 mb-0"
                                   for="durationCondition">{{ t('cards_conditions_duration') }}</label>
                            <input id="durationCondition" readonly value="12month"
                                   class="wallesterInputs mt-3 ml-0 mr-0">
                        </div>
                        <div class="wallester-card-condition-blocks">
                            <label class="activeLink font-15 mb-0"
                                   for="deliveryTimeCondition">{{ t('cards_conditions_delivery_time') }}</label>
                            <input id="deliveryTimeCondition" readonly value="Instant"
                                   class="wallesterInputs mt-3 ml-0 mr-0">
                        </div>
                        <div class="wallester-card-condition-blocks">
                            <label class="activeLink font-15 mb-0"
                                   for="fundingLimitsCondition">{{ t('cards_conditions_funding_limits') }}</label>
                            <input id="fundingLimitsCondition" readonly value="None"
                                   class="wallesterInputs mt-3 ml-0 mr-0">
                        </div>
                    </div>
                </div>
                <div class="mt-5">
                    <h2 class="wallesterOrderBlocks text-left">{{ t('cards_conditions_transactions') }}</h2>
                    <div class="mt-4 d-flex wallester-conditions-parent">
                        <div class="wallester-card-condition-blocks p-0">
                            <label class="activeLink font-15 mb-0" for="cardTransactionCondition"> {{ t('cards_conditions_purchases') }}</label>
                            <input id="cardTransactionCondition" readonly value="Free"
                                   class="wallesterInputs mt-3 ml-0 mr-0">
                        </div>
                        <div class="wallester-card-condition-blocks">
                            <label class="activeLink font-15 mb-0"
                                   for="atmTransactionCondition">{{ t('cards_conditions_atm_transactions') }}</label>
                            <input id="atmTransactionCondition" readonly value="Free"
                                   class="wallesterInputs mt-3 ml-0 mr-0">
                        </div>
                        <div class="wallester-card-condition-blocks">
                            <label class="activeLink font-15 mb-0"
                                   for="contactlessPurchasesCondition">{{ t('cards_conditions_contactless_purchases') }}</label>
                            <input id="contactlessPurchasesCondition" readonly value="Free"
                                   class="wallesterInputs mt-3 ml-0 mr-0">
                        </div>
                        <div class="wallester-card-condition-blocks">
                            <label class="activeLink font-15 mb-0"
                                   for="cardToCardTransferCondition">{{ t('cards_conditions_card_to_card_transfers') }}</label>
                            <input id="cardToCardTransferCondition" readonly value="Free"
                                   class="wallesterInputs mt-3 ml-0 mr-0">
                        </div>
                    </div>
                </div>
                <div class="mt-5">
                    <h2 class="wallesterOrderBlocks text-left">{{ t('cards_conditions_fees') }}</h2>
                    <div class="mt-4 d-flex wallester-conditions-parent">
                        <div class="wallester-card-condition-blocks p-0">
                            <label class="activeLink font-15 mb-0" for="cardTransactionCondition"> {{ t('cards_conditions_issuing_fee') }}</label>
                            <input id="issuingFeeCondition" readonly value="Free"
                                   class="wallesterInputs mt-3 ml-0 mr-0">
                        </div>
                        <div class="wallester-card-condition-blocks">
                            <label class="activeLink font-15 mb-0"
                                   for="atmTransactionCondition">{{ t('cards_conditions_loading_fee') }}</label>
                            <input id="loadingFeeCondition" readonly value="Free"
                                   class="wallesterInputs mt-3 ml-0 mr-0">
                        </div>
                        <div class="wallester-card-condition-blocks">
                            <label class="activeLink font-15 mb-0"
                                   for="contactlessPurchasesConditionFee">{{ t('cards_conditions_contactless_purchases') }}</label>
                            <input id="contactlessPurchasesConditionFee" readonly value="Free"
                                   class="wallesterInputs mt-3 ml-0 mr-0">
                        </div>
                        <div class="wallester-card-condition-blocks">
                            <label class="activeLink font-15 mb-0"
                                   for="cardToCardTransferConditionFee">{{ t('cards_conditions_card_to_card_transfers') }}</label>
                            <input id="cardToCardTransferConditionFee" readonly value="Free"
                                   class="wallesterInputs mt-3 ml-0 mr-0">
                        </div>
                    </div>
                </div>
                <br>
            </div>
        </div>
    </div>
</div>

