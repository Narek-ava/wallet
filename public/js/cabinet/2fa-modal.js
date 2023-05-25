class AskTwoFA {

    constructor() {
        this.isConfirmed = false;
        this.formId = '#twoFaConfirmForm';
        this.form = $(this.formId);
        this.confirmUrl = '2fa-operation-confirm';
        this.initUrl = '2fa-operation-init';
        this.initSubmitEvent();
    }

    initSubmitEvent() {
        let url = this.confirmUrl;
        $('body').on('submit', this.formId, (e) => {
            $('#twoFAError').addClass('d-none')
            e.preventDefault();
            $.ajax({
                url: API + url,
                method: 'POST',
                data: this.form.serialize(),
                dataType: 'json',
                success: (data) => {
                    if (data.isValid) {
                        this.isConfirmed = true;
                        this.callback()
                    } else {
                        this.showError();
                    }
                },
                error: () => {
                    this.showError();
                }
            });
        })
    }

    callback() {
        console.log('callback is not defined, callback should be set by method ask2faConfirm')
    }

    ask2faConfirm(callback) {
        let url = this.initUrl;
        $.ajax({
            url: API + url,
            method: 'POST',
            dataType: 'json',
            success: (data) => {
            },
        });
        $('#modal-2fa-operation-confirm').modal('show');
        this.callback = callback;
    }

    showError() {
        $('#twoFAError').removeClass('d-none')
    }

    attachToFormSubmit(formId) {
        let $this = this;
        $(document).on('submit', formId, function (e) {
            if (!$this.isConfirmed) {
                e.preventDefault();
                let form = $(this);
                $this.ask2faConfirm(function () {
                    form.submit();
                })
            }
        });
    }


}
