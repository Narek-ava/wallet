<!-- Modal -->
<div class="modal fade" id="success" role="dialog">
    <div class="modal-dialog modal-dialog-centered" style="max-width: 500px;">
        <!-- Modal content-->
        <div class="modal-content modal-content-centered">
            <div class="modal-header">
                <h5 class="modal-title">{{ t('card_order') }}</h5>
                <button type="button" class="close closeSuccessModal" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">×</span>
                </button>
            </div>
            <div class="modal-body text-success">
                <p id="successText">{{ t('card_order_operation_success') }}</p>
            </div>
        </div>
    </div>
</div>
