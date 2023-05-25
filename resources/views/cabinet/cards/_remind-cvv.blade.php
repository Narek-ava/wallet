<!-- Modal -->
<div class="modal fade" id="remindPinOrCVV" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered" style="max-width: 500px;">
        <!-- Modal content-->
        <div class="modal-content modal-content-centered">
            <div class="modal-header justify-content-center">
                <div class="d-flex flex-column">
                    <h3 class="modal-title text-center">{{ t('pin') }}</h3>
                    <p>{{ t('remind_pin_or_cvv') }}</p>
                </div>
                <button type="button" class="close closeSuccessModal" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">×</span>
                </button>
            </div>
            <div class="modal-body text-left">
                <div class="d-flex flex-column">
                    @if($card->card_type == \App\Enums\WallesterCardTypes::TYPE_PLASTIC)
                        <form action="{{ route('wallester.get.pin.code', ['id' => $card->id]) }}" id="remindPinForm" class="mb-5">
                            <div class="d-flex flex-column" style="max-height:40px">
                                <h4 class="text-center">{{ t('pin') }}</h4>
                                <button type="button" id="remindPin"
                                        class="btn btn-sm ml-3 btn-primary themeBtn remindCardPinOrCvv">{{ t('remind') }}</button>
                                <div class="wallester-show-pin-cvv d-none w-100" id="showPin"></div>
                            </div>
                            <p class="text-danger text-left pinError" style="line-height: inherit; display: none"></p>
                        </form>
                    @endif
                    <form action="{{ route('wallester.get.cvv.code', ['id' => $card->id]) }}" id="remindCVVForm" class="mt-3">
                        <div class="d-flex mt-3 flex-column" style="height: fit-content">
                            <h4 class="text-center">{{ t('cvv2') }}</h4>
                            <button type="button" id="remindCVV" class="btn btn-sm ml-3 btn-primary themeBtn remindCardPinOrCvv">{{ t('remind') }}</button>
                            <div class="wallester-show-pin-cvv w-100 d-none" id="showCvv"></div>
                        </div>
                        <p class="text-danger text-left cvvError" style="line-height: inherit; display: none"></p>
                    </form>
                </div>
            </div>
            <div class="modal-footer">
            </div>
        </div>

    </div>
</div>
