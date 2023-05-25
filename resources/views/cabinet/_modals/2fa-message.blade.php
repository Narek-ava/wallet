<div class="modal fade login-popup rounded-0" id="modal-2fa-message" tabindex="-1" role="dialog" aria-labelledby="exampleModalCenterTitle" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered" role="document" style="max-width: 500px;">
        <div class="modal-content modal-content-centered">
            <div class="modal-header">
                <h5 class="modal-title">{{ t('ui_2fa_message_header') }}</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">×</span>
                </button>
            </div>
            <div class="modal-body">
                <div class="row">
                    <div class="col-md-12">
                        <p id="2fa-message-text"></p>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button data-dismiss="modal" class="btn btn-lg btn-primary themeBtn register-buttons" type="button">
                    {{ 'OK' }}
                </button>
            </div>
        </div>
    </div>
</div>
