<div class="modal fade login-popup rounded-0" id="cardTopUp" tabindex="-1" role="dialog"
     aria-labelledby="exampleModalCenterTitle" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered" role="document" style="max-width: 700px;">
        <div class="modal-content modal-content-centered">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">×</span>
                </button>
                <div class="w-100 d-flex flex-column justify-center" >
                    <div class="d-flex flex-row justify-content-center">

                        <h2 style="line-height: inherit" class="wallesterOrderBlocks">{{ t('cards_details_top_up') }}</h2>
                        <button id="allDetailsTopUp" class="btn btn-light ml-1" style="line-height: initial"
                                onclick="copyText(this)">
                            <i class="fa fa-copy" aria-hidden="true"></i>
                        </button>
                    </div>
                    <textarea id="textallDetailsTopUp" style="position:absolute;left:-10000px;top:-10000px" >{{ $topUpDetailsToCopy }}</textarea>
                    {{ t('cards_details_description') }}

                </div>
            </div>
            <div class="modal-body">

                <div class="w-100 d-flex flex-column justify-center">
                    <div class="wallester-top-up-details">
                        <div class="d-flex flex-row justify-content-center">

                            <h6 style="line-height: inherit">{{ t('recipient') }}</h6>
                            <button id="recipient" class="btn btn-light ml-1" style="line-height: initial"
                                    onclick="copyText(this)">
                                <i class="fa fa-copy" aria-hidden="true"></i>
                            </button>
                        </div>
                        <p>{{ $card->account->cProfile->getFullName() }}</p>
                        <input id="textrecipient"
                               class="w-75" style="position:absolute;left:-10000px;top:-10000px" type="text"
                               value="{{ $card->account->cProfile->getFullName() }}">
                    </div>
                    <div class="wallester-top-up-details">
                        <h6>{{ t('passport_id') }}</h6>
                        <p>{{ $card->account->cProfile->passport ?? '-' }}</p>
                    </div>
                    <div class="wallester-top-up-details">
                        <h6>{{ t('resident_address') }}</h6>
                        <p>{{ $card->account->cProfile->address ?? '-' }}</p>
                    </div>
                    <div class="wallester-top-up-details">
                        <div class="d-flex flex-row justify-content-center">
                            <h6 style="line-height: inherit">{{ t('topup_iban') }}</h6>
                            <button id="iban" class="btn btn-light ml-1" style="line-height: initial"
                                    onclick="copyText(this)">
                                <i class="fa fa-copy" aria-hidden="true"></i>
                            </button>
                        </div>

                        <p>{{ $bankTemplate->IBAN ?? '-' }}</p>

                        <input id="textiban"
                               class="w-75" style="position:absolute;left:-10000px;top:-10000px" type="text"
                               value="{{ $bankTemplate->IBAN ?? '-' }}">
                    </div>
                    <div class="wallester-top-up-details">
                        <h6>{{ t('swift_bic') }}</h6>
                        <p>{{ $bankTemplate->SWIFT ?? '-' }}</p>
                    </div>
                    <div class="wallester-top-up-details">
                        <h6>{{ t('wallester_bank_name') }}</h6>
                        <p>{{ $bankTemplate->bank_name ?? '-' }}</p>
                    </div>
                    <div class="wallester-top-up-details">
                        <h6>{{ t('wallester_bank_address') }}</h6>
                        <p>{{ $bankTemplate->bank_address ?? '-' }}</p>
                    </div>
                    <div class="wallester-top-up-details">
                        <h6>{{ t('purpose_of_payment') }}</h6>
                        <p>{{ t('purpose_of_payment_description') }}</p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

