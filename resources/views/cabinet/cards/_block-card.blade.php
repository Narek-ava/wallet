<!-- Modal -->
<div class="modal fade" id="blockCard" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered" style="max-width: 500px;">
        <!-- Modal content-->
        <div class="modal-content modal-content-centered">
            <div class="modal-header ">
                <h5 class="modal-title">{{ $card->status === \App\Enums\WallesterCardStatuses::STATUS_BLOCKED ? t('card_unblocking_header') : t('card_blocking_header') }}</h5>
                <button type="button" class="close closeSuccessModal" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">×</span>
                </button>
            </div>
            <div class="modal-body m-0 p-0">
                <p id="successText">{{ $card->status === \App\Enums\WallesterCardStatuses::STATUS_BLOCKED ? t('card_unblocking_body') : t('card_blocking_body') }}</p>
            </div>
            <div class="modal-footer">
                <div class="d-flex flex-column">
                    <form id="blockWallesterCardForm" action="{{ $blockUrl }}" method="post">
                        @csrf
                        <button type="submit" class="btn btn-lg btn-primary themeBtn" id="blockWallesterCardFormBtn">{{ t('yes') }}</button>
                    </form>
                    <button type="button" class="btn btn-lg btn-primary themeBtn closeSuccessModal mt-3" data-dismiss="modal" aria-label="Close">{{ t('no') }}</button>
                </div>
            </div>
        </div>
    </div>
</div>
