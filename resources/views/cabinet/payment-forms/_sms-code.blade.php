<div class="modal fade login-popup rounded-0" id="modal-payment-sms" tabindex="-1" role="dialog" aria-hidden="true">
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
                <input type="hidden" class="paymentFormAttemptId" name="paymentFormAttemptId">
                <div class="modal-body">
                    <div class="row">
                        <div class="col-md-12">
                            {!! t('ui_sms_code_message') !!}
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-12">
                            <div class="form-label-group">
                                <input name="code" type="text" class="form-control text-center verifyCode" maxlength="6" required style="max-width: 200px;">
                            </div>
                        </div>
                    </div>
                    <div class="row">
                        <div class="error-text-list col-md-12">
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button name="verify" data-verify-url="{{ route('verify.sms.code.payment.form') }}" class="btn btn-lg btn-primary themeBtn register-buttons" type="button">
                        {{ t('ui_2fa_verify_button') }}
                    </button>&nbsp;
                    <button name="resend" class="d-none btn btn-lg btn-primary themeBtn register-buttons" type="button">
                        {{ t('ui_send_again') }}
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
