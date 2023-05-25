<div class="modal" id="bankDetails" role="dialog">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content modal-content-centered" style="border:none;border-radius: 5px;">
            <div class="modal-header" style="background: var(--main-color);border-top-left-radius: 5px;border-top-right-radius: 5px;">
                <h4 class="modal-title text-center">{{ t('ui_cabinet_menu_bank_details') }}</h4>
            </div>
            <div class="modal-body">
                <div class="row">
                    <div class="col-md-6">
                        <p><span class="bold">{{ t('ui_name') }}: </span>{{ $exchangeRequest->fromAccount->name ?? '-' }}</p>
                    </div>
                    <div class="col-md-6">
                        <p><span class="bold">{{ t('type') }}: </span>{{ \App\Enums\WireType::NAMES[$exchangeRequest->fromAccount->type] }}</p>
                    </div>
                    <div class="col-md-6">
                        <p><span class="bold">{{ t('withdrawal_currency') }}: </span>{{ \App\Enums\Currency::FIAT_CURRENCY_NAMES[$exchangeRequest->fromAccount->currency] }}</p>
                    </div>
                    <div class="col-md-6">
                        <p><span class="bold">{{ t('withdrawal_crypto_amount') }}: </span>{{ $exchangeRequest->fromAccount->amount }}</p>
                    </div>
                    <div class="col-md-6">
                        <p><span class="bold">{{ t('ui_cabinet_bank_details_iban') }}: </span>{{ $exchangeRequest->fromAccount->IBAN }}</p>
                    </div>
                    <div class="col-md-6">
                        <p><span class="bold">{{ t('ui_cabinet_bank_details_swift') }}: </span>{{ $exchangeRequest->fromAccount->SWIFT }}</p>
                    </div>
                    <div class="col-md-6">
                        <p><span class="bold">{{ t('ui_card_number') }}: </span>{{ $exchangeRequest->fromAccount->card_number ?? '-' }}</p>
                    </div>
                    <div class="col-md-6">
                        <p><span class="bold">{{ t('send_crypto_crypto_wallet') }}: </span>{{ $exchangeRequest->fromAccount->crypto_wallet ?? '-' }}</p>
                    </div>
                    <div class="col-md-6">
                        <p><span class="bold">{{ t('ui_cprofile_country') }}: </span>{{ \App\Models\Country::getCountryNameByCode($exchangeRequest->fromAccount->country) ?? '-'  }}</p>
                    </div>
                    <div class="col-md-6">
                        <p><span class="bold">{{ t('ui_holder') }}: </span>{{ $exchangeRequest->fromAccount->holder }}</p>
                    </div>
                    <div class="col-md-6">
                        <p><span class="bold">{{ t('ui_number') }}: </span>{{ $exchangeRequest->fromAccount->number }}</p>
                    </div>
                    <div class="col-md-6">
                        <p><span class="bold">{{ t('ui_cabinet_bank_details_bank_name') }}: </span>{{ $exchangeRequest->fromAccount->bank_name }}</p>
                    </div>
                    <div class="col-md-6">
                        <p><span class="bold">{{ t('ui_cabinet_bank_details_bank_address') }}: </span>{{ $exchangeRequest->fromAccount->bank_address }}</p>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-lg btn-primary themeBtn" data-dismiss="modal">{{ t('ui_compliance_retry_modal_close_button') }}</button>
            </div>
        </div>
    </div>
</div>
