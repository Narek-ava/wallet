var init = function () {
    EmailRegister.init();
};

var EmailRegister = {

    showResend: function (toShow = false) {
        if (toShow) {
            EmailRegister.$resend.removeClass('d-none');
            EmailRegister.$resend.show();
        } else {
            EmailRegister.$resend.hide();
        }
    },

    showVerify: function (toShow = false) {
        if (toShow) {
            EmailRegister.$verify.show();
        } else {
            EmailRegister.$verify.hide();
        }
    },

    showErrors: function (errors) {
        // @todo CodeDup showErrors
        var $list = $('.error-text-list');
        if (Array.isArray(errors)) {
            var errorsHtml = '';
            $.each(errors, function (key, value) {
                errorsHtml += '<p class="error-text">' + value + '</p>';
            });
            $list.html(errorsHtml);
        } else {
            $list.html(null);
        }
    },

    init: function () {
        this.$modal = $('#modal-register-email');
        this.$form = this.$modal.find('form');
        this.$resend = this.$form.find('button[name=resend]');
        this.$mainForm = this.$form.find('.mainForm');
        this.$verify = this.$form.find('button[name=verify]');

        this.$verify.on('click', function () {
            EmailRegister.verify();
        });

        this.$resend.on('click', () => {
            EmailRegister.resend();
            $('.error-text').html(null).hide()
        });

        if (emailRegisterToShow) {
            this.$modal.modal('show');
        }
    },

    verify: function () {
        EmailRegister.showErrors();

        $.ajax({
            url: API + 'register-confirms-email',
            type: 'post',
            data: this.$form.serialize()
        }).done(function (jqXHR) {
            $('#modal-register-email').modal('hide');
            $('#modal-register-sms').modal('show');
            if(jqXHR.success && jqXHR.redirect) {
                // location.href = jqXHR.redirect;
            }
            EmailRegister.showResend();
        }).fail(function (jqXHR) {
            var responseData = jqXHR.responseJSON;
            if(responseData.redirect) {
                location.href = responseData.redirect;
            }
            var allow_resend = responseData.allow_resend ?? false;
            EmailRegister.showErrors(responseData.errors);
            EmailRegister.showResend(allow_resend);
            EmailRegister.showVerify(!responseData.hasOwnProperty('allow_resend'));
        });


    },

    resend: function () {
        EmailRegister.showErrors();
        EmailRegister.$form.find('input[name=code]').val(null);
        EmailRegister.showResend();
        EmailRegister.showVerify(true);
        $.ajax({
            url: API + 'register-resend-email',
            type: 'post',
            data: this.$form.serialize()
        }).done(function (jqXHR) {
        }).fail(function (jqXHR) {
            var response = jqXHR.responseJSON;
            if (response.errors && response.errors.hasOwnProperty('captcha')) {
                let errorsHtml = '<p class="error-text">' + response.errors.captcha + '</p>';
                $('.error-text-list').html(errorsHtml).show();
            } else {
                EmailRegister.showResend(response.allow_resend);
                EmailRegister.showErrors(response.errors);
            }

        });


    }

};

$(function () {
    EmailRegister.init();
});
