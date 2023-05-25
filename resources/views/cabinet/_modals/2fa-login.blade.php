<div class="modal fade login-popup rounded-0" id="modal-2fa-login" tabindex="-1" role="dialog" aria-labelledby="exampleModalCenterTitle" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered" role="document" style="max-width: 500px;">
        <div class="modal-content modal-content-centered">
            <div class="modal-header">
                <h5 class="modal-title">{{ t('ui_2fa_header') }}</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">×</span>
                </button>
            </div>
            <form method="POST" action="{{ route('cabinet.2fa-login.post') }}">
                <div class="modal-body">
                    @csrf

                    <div class="row">
                        <div class="col-md-12 mb-2">
                            <div class="form-label-group">
                                <label for="2fa-login-code">{{ t('ui_2fa_code_label') }}</label>
                                <input name="code" type="text" id="2fa-login-code" class="form-control" required style="max-width: 200px;">

                            </div>
                            <div class="error-text-list">
                                @foreach($errors->all() as $message)
                                <p class="error-text">{{ $message }}</p>
                                @endforeach
                            </div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button class="btn btn-lg btn-primary themeBtn register-buttons" type="submit">
                        {{ t('ui_2fa_verify_button') }}
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
