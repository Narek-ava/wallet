class AskTwoFAWallester extends AskTwoFA {

    constructor() {
        super()
        this.encryptionFormEventIsDefined = false;
        this.limitUpdateEventIsDefined = false;
        this.remindPinFormIsDefined = false;
        this.remindCVVFormIsDefined = false;
        this.securityFormIsDefined = false;
        this.remind3dsPasswordIsDefined = false;
        this.blockCardIsDefined = false;
    }

    attachToFormSubmit(formId,disabled) {
        let $this = this;
        if (disabled === 2 )
        {
            this.disabled = false
        }
        if (disabled  === 0 )
        {
            this.disabled = true
        }




        if ((formId === '#encryptDetails' && !this.encryptionFormEventIsDefined)
            || (formId === '#card-limit-update-form' && !this.limitUpdateEventIsDefined)
            || (formId === '#remindCVVForm' && !this.remindCVVFormIsDefined)
            || (formId === '#getEncrypted3dsPassword' && !this.remind3dsPasswordIsDefined)
            || (formId === '#remindPinForm' && !this.remindPinFormIsDefined)
            || (formId === '#blockWallesterCardForm' && !this.blockCardIsDefined)
            || (formId === '#updateSecurityDetails' && !this.securityFormIsDefined)) {

            if (formId === '#encryptDetails') {
                this.encryptionFormEventIsDefined = true;
                this.block = true
            } else if(formId === '#card-limit-update-form') {
                this.limitUpdateEventIsDefined = true;
                this.block = true
            } else if (formId === '#remindPinForm') {
                this.remindPinFormIsDefined = true;
                this.block = true
            } else if (formId === '#remindCVVFormIsDefined') {
                this.remindCVVFormIsDefined = true;
                this.block = true
            }else if (formId === '#updateSecurityDetails') {
                this.securityFormIsDefined = true;
                this.block = true
            }else if (formId === '#getEncrypted3dsPassword') {
                this.remind3dsPasswordIsDefined = true;
                this.block = true
            }else if (formId === '#blockWallesterCardForm') {
                this.blockCardIsDefined = true;
                this.block = false;
            }
            if (this.disabled && this.block){

                $this.isConfirmed = true
            }else{
                $this.isConfirmed = false
            }
            $(document).on('submit', formId,disabled, function (e) {
                if (this.disabled){
                    $this.isConfirmed = true
                }
                if ($this.isConfirmed ){
                    this.block = true
                }
                if (!$this.isConfirmed || !this.block) {
                    e.preventDefault();
                    let form = $(this);
                    $this.ask2faConfirm(function () {
                        form.submit();
                    })
                }

            });
        }

    }

    ask2faConfirm(callback) {
        $.ajax({
            url: API + '2fa-operation-init/' + true,
            method: 'POST',
            dataType: 'json',
            success: (data) => {
            },
        });
        $('#modal-2fa-operation-confirm').modal('show');
        this.callback = callback;
    }

    initSubmitEvent() {
        $('body').on('submit', this.formId, (e) => {
            $('#twoFAError').addClass('d-none')
            e.preventDefault();
            $.ajax({
                url: API + '2fa-operation-confirm',
                method: 'POST',
                data: this.form.serialize()  + "&generateAnyway=true",
                dataType: 'json',
                success: (data) => {
                    if (data.isValid) {
                        this.isConfirmed = true;
                        this.block = true
                        this.callback()
                    } else {
                        this.showError();
                        this.block = true

                    }
                },
                error: () => {
                    this.showError();
                }
            });
        })
    }
}
