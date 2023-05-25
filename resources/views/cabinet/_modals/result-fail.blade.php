<!-- Modal -->
<div class="modal fade" id="modal-result-fail" role="dialog">
    <div class="modal-dialog modal-dialog-centered" style="max-width: 500px;">
        <!-- Modal content-->
        <div class="modal-content modal-content-centered">
            <div class="modal-header">
                <h5 class="modal-title">{{ t('enum_log_level_error') }}</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">×</span>
                </button>
            </div>
            <div class="modal-body">
                <p id="modal-result-text">= Fail 1 =</p>
             </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-lg btn-primary themeBtn" data-dismiss="modal">{{ t('ui_compliance_retry_modal_close_button') }}</button>
            </div>
        </div>

    </div>
</div>
