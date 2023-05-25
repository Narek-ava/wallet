<div class="modal fade login-popup rounded-0" id="modal-register-sms" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered" role="document" style="max-width: 500px;">
        <div class="modal-content modal-content-centered">
            <div class="modal-header">
                <h5 class="modal-title">{{ t('ui_sms_code') }}</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">×</span>
                </button>
            </div>
            <form>
                @csrf
                <input type="hidden" name="geetest_challenge">
                <input type="hidden" name="geetest_validate">
                <input type="hidden" name="geetest_seccode">
                <div class="modal-body">
                    <div class="row mainForm">
                        <div class="col-md-12">
                            {!! t('ui_sms_code_message') !!}
                        </div>
                    </div>
                    <div class="row captchaBlock d-none">
                        <div class="col-md-12">
                            {!! t('ui_sms_code_message_captcha') !!}
                        </div>
                    </div>
                    <div class="row mainForm">
                        <div class="col-md-12">
                            <div class="form-label-group">
                                <input name="code" type="text" class="form-control" required autofocus style="max-width: 200px;">
                            </div>
                        </div>
                    </div>
                    <div class="row">
                        <div class="d-none form-label-group captcha">
                            <p class="captcha-fail" style="display: none">{{ t('error_bad_captcha') }}</p>
                            <div id="resend-code"></div>
                            <p id="wait-resend-code" class="show">Loading Captcha...</p>
                        </div>
                    </div>

                    <div class="row">
                        <div class="error-text-list col-md-12">
                        </div>
                    </div>
                </div>

                <div class="modal-footer">
                    <button name="verify" class="btn btn-lg btn-primary themeBtn register-buttons mainForm" type="button">
                        {{ t('ui_2fa_verify_button') }}
                    </button>&nbsp;
                    <button name="resend" class="d-none btn btn-lg btn-primary themeBtn register-buttons mainForm" type="button">
                        {{ t('ui_send_again') }}
                    </button>
                    <button name="resendWithCaptcha" class="btn btn-lg btn-primary themeBtn register-buttons captchaBlock d-none" type="button">
                        {{ t('ui_resend') }}
                    </button>&nbsp;
                </div>
            </form>
        </div>
    </div>
</div>
