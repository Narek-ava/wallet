class AskTwoFALogin extends AskTwoFA {

    constructor() {
        super()
        this.confirmUrl = '2fa-operation-confirm';
        this.initUrl = '2fa-login-init';
    }

    attachToFormSubmit(formId) {
        $('#modal-2fa-operation-confirm').modal('show');
        this.callback = function () {
            return window.location.reload();
        };
    }
}
