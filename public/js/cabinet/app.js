var init = function () {

    SmsRegister.init();

};

var SmsRegister = {

    captchaError: true,
    captchaInit: true,

    showResend: function (toShow = false) {
        if (toShow) {
            SmsRegister.$resend.removeClass('d-none');
            SmsRegister.$resend.show();
        } else {
            SmsRegister.$resend.hide();
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
        this.$modal = $('#modal-register-sms');
        this.$form = this.$modal.find('form');
        this.$resend = this.$form.find('button[name=resend]');
        this.$resendWithCaptcha = this.$form.find('button[name=resendWithCaptcha]');
        this.$captcha = this.$form.find('.captcha');
        this.$captchaBlock = this.$form.find('.captchaBlock');
        this.$mainForm = this.$form.find('.mainForm');
        this.$verify = this.$form.find('button[name=verify]');

        this.$verify.on('click', function () {
            SmsRegister.verify();
        });
        this.$resend.on('click', () => {
            $('.error-text').html(null).hide()
            SmsRegister.getCaptchaBLock();
        });

        this.$resendWithCaptcha.on('click', function () {
            SmsRegister.resend();
        });

        if (smsRegisterToShow) {
            this.$modal.modal('show');
        }
    },

    getCaptchaBLock: function () {
        SmsRegister.$captcha.removeClass('d-none').show();
        SmsRegister.$captchaBlock.removeClass('d-none').show();
        SmsRegister.$mainForm.hide();
    },

    hideCaptchaBlock: function () {
        SmsRegister.$captchaBlock.hide();
        SmsRegister.$captcha.hide();
        SmsRegister.$mainForm.show();
    },

    verify: function () {
        SmsRegister.showErrors();
        if(SmsRegister.captchaInit) {
            SmsRegister.geetest();
            SmsRegister.captchaInit = false;
        }

        $.ajax({
            url: API + 'register-confirms-sms',
            type: 'post',
            data: this.$form.serialize()
        }).done(function (jqXHR) {
            if(jqXHR.success && jqXHR.redirect) {
                location.href = jqXHR.redirect;
            }
            SmsRegister.showResend();
        }).fail(function (jqXHR) {
            var responseData = jqXHR.responseJSON;
            if(responseData.redirect) {
                location.href = responseData.redirect;
            }
            SmsRegister.showErrors(responseData.errors);
            SmsRegister.showResend(responseData.allow_resend);
        });


    },

    resend: function () {
        console.log('captchaError', SmsRegister.captchaError);
        if(SmsRegister.captchaError) {
            return false;
        }
        $('#resend-code').html('');
        SmsRegister.geetest();
        SmsRegister.captchaError = true;
        SmsRegister.showErrors();
        SmsRegister.$form.find('input[name=code]').val(null);

        $.ajax({
            url: API + 'register-resend-sms',
            type: 'post',
            data: this.$form.serialize()
        }).done(function (jqXHR) {
            SmsRegister.hideCaptchaBlock()
        }).fail(function (jqXHR) {
            var response = jqXHR.responseJSON;
            if (response.errors && response.errors.hasOwnProperty('captcha')) {
                SmsRegister.getCaptchaBLock()
                let errorsHtml = '<p class="error-text">' + response.errors.captcha + '</p>';
                $('.error-text-list').html(errorsHtml).show();
            } else {
                SmsRegister.hideCaptchaBlock();
                SmsRegister.showResend(response.allow_resend);
                SmsRegister.showErrors(response.errors);
            }

        });


    },

    geetest: function () {
        var geetestCode = function(url) {
            var handlerEmbed = function(captchaObj) {
                $('button[name=resendWithCaptcha]').click(function(e) {
                    var validate = captchaObj.getValidate();
                    if (!validate) {
                        $('.captcha-fail').show();
                        e.preventDefault();
                        SmsRegister.captchaError = true;
                        console.log('validate', SmsRegister.captchaError)
                    }
                });

                captchaObj.appendTo("#resend-code");
                captchaObj.onReady(function () {
                    $("#wait-resend-code")[0].className = "hide";
                    $('.captcha-fail').hide();
                });
                captchaObj.onSuccess(function () {
                    SmsRegister.captchaError = false;
                    $("#wait-resend-code")[0].className = "hide";
                    $('.captcha-fail, .error-text').hide();
                    var validate = captchaObj.getValidate();
                    $('#modal-register-sms').find('input[name=geetest_challenge]').val(validate.geetest_challenge);
                    $('#modal-register-sms').find('input[name=geetest_validate]').val(validate.geetest_validate);
                    $('#modal-register-sms').find('input[name=geetest_seccode]').val(validate.geetest_seccode);
                });

            };
            $.ajax({
                url: url + "?t=" + (new Date()).getTime(),
                type: "get",
                dataType: "json",
                success: function(data) {
                    $('.captcha-fail').hide();
                    initGeetest({
                        gt: data.gt,
                        challenge: data.challenge,
                        width: '100%',
                        product: 'float',
                        offline: !data.success,
                        new_captcha: data.new_captcha,
                        lang: 'en',
                        http: (geetestProtocol ?? 'http') + '://'
                    }, handlerEmbed);
                }
            });
        };
        geetestCode('/geetest');
        return geetestCode;
    }

};

$(function () {

    if (twoFAToShow) {
        $('#modal-2fa-operation-confirm').modal('show');
    }

    SmsRegister.init();

    $('.select-phone-cc').select2({
        placeholder: "Please select a country",
        data: countries,
        id: "value",
        dropdownCssClass: 'country_code_dropdown',
        templateSelection: function (data) {
            if(!data.id) {
                return $('<span>' + data.text + '</span>');
            }
            var $result = $(
                '<span><img src="' + baseFlagUrl + data.iso_code + '.png" class="img-flag">&nbsp' + data.country_code + '</span>'
            );
            return $result;
        },
        templateResult: function (data) {
            if (!data.id) {
                return;
            }
            var $result = $(
                '<div class="row">' +
                '<div class="col-1">' + '<img src="' + baseFlagUrl + data.iso_code + '.png" class="img-flag">' + '</div>' +
                '<div class="col-6 col-sm-8">' + data.text + '</div>' +
                '<div class="col-2">' + data.country_code + '</div>' +
                '</div>'
            );
            return $result;
        }
    });
});
