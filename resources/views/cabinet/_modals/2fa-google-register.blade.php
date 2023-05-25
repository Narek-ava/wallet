<div class="modal fade login-popup rounded-0" id="modal-2fa-google-register" tabindex="-1" role="dialog"
     aria-labelledby="exampleModalCenterTitle" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered" role="document">
        <div class="modal-content modal-content-centered col-md-12">
            <div class="modal-header">
                <h5 class="modal-title">{{ t('ui_2fa_google_register_header') }}</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">×</span>
                </button>
            </div>
            <div class="modal-body">

                <div class="row">
                    {!! t('ui_2fa_google_register_text_1') !!}
                </div>
                <div class="row">
                    <div class="col-md-12">
                        <div id="2fa-google-qr-image"></div>
                    </div>
                    <div class="col-md-12">
                        <h4>{{ t('ui_2fa_google_register_key_label') }}</h4>
                        <div id="2fa-google-secret" class="d-block font36bold">{{ t('enum_status_disabled') }}</div>
                        {{ t('ui_2fa_google_register_text_2') }}
                        <p><p>

                        <button id="2fa-google-regenerate-button" class="btn btn-lg btn-primary themeBtn"
                                type="button">
                            {{ t('ui_2fa_google_regenerate_button') }}
                        </button>
                    </div>
                </div>

                <div class="row">
                    {{ t('ui_2fa_google_register_text_3') }}
                    <p><p>
                </div>

                <div class="row">
                    <div class="col-md-12">
                        <div class="row">
                            <label for="2fa-google-enable-confirm-code">
                                {{ t('ui_2fa_confirm_google_label') }}
                            </label>
                        </div>
                        <div class="row">
                            <div class="col-md-4">
                                <input type="text" id="2fa-google-enable-confirm-code"
                                       class="form-control col-md-5" placeholder="676835" required>
                                <div class="error-text-list"></div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button id="2fa-google-enable-confirm-button" class="btn btn-lg btn-primary themeBtn"
                        type="button">
                    {{ t('ui_2fa_google_register_button') }}
                </button>
            </div>
        </div>
    </div>
</div>
