<!-- Modal -->
<div class="modal fade" id="success" role="dialog">
    <div class="modal-dialog modal-dialog-centered" style="max-width: 500px;">

        <!-- Modal content-->
        <div class="modal-content modal-content-centered">
            <div class="modal-header">
                <h5 class="modal-title">{{ t('ui_success') }}</h5>
                <button type="button" class="close closeSuccessModal" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">×</span>
                </button>
            </div>
            <div class="modal-body">
                <p id="successText">{{ t('ui_successfuly_saved') }}</p>
             </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-lg btn-primary themeBtn closeSuccessModal" data-dismiss="modal">{{ t('operation_request_close_button') }}</button>
            </div>
        </div>

    </div>
</div>
