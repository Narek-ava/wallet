<div class="modal fade login-popup rounded-0" id="modal-2fa-operation-confirm" tabindex="-1" role="dialog" aria-labelledby="exampleModalCenterTitle" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered" role="document" style="max-width: 500px;">
        <div class="modal-content modal-content-centered">
            <div class="modal-header">
                <h5 class="modal-title">{{ t('ui_confirmation') }}</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">×</span>
                </button>
            </div>
            <form id="twoFaConfirmForm" method="POST">
                @csrf

                <div class="modal-body">
                    <div class="row">

                        <div class="col-md-12">
                            <h6 id="2fa-confirm-header" class="mb-4">
                                {{ isset($two_fa_type) && $two_fa_type == \App\Enums\TwoFAType::EMAIL ? t('ui_2fa_confirm_email_header') : t('ui_2fa_confirm_google_header') }}
                            </h6>
                            <div>
                                {!! t('ui_2fa_confirm_text') !!}
                            </div>
                            <div class="form-label-group">
                                <label for="2fa-confirm-code">
                                    {{ isset($two_fa_type) && $two_fa_type == \App\Enums\TwoFAType::EMAIL ? t('ui_2fa_confirm_email_label') : t('ui_2fa_confirm_google_label') }}
                                </label>
                                <input name="2fa-confirm-code" type="text" id="2fa-confirm-code" class="form-control" placeholder="{{ \C\TWO_FA_CODE_PLACEHOLDER }}" required  style="max-width: 200px;">
                            </div>

                            <div class="error-text-list">
                                <p id="twoFAError" class="error-text d-none">{{ t('error_2fa_wrong_code') }}</p>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button id="2fa-confirm-button" class="btn btn-lg btn-primary themeBtn register-buttons" type="submit">
                        {{ t('ui_2fa_confirm_button') }}
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

@section('scripts')
    <script src="/js/cabinet/2fa-modal.js"></script>
    @parent
@endsection
