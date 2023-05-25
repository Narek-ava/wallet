var PaymentFormSms = {

    showResend: function (toShow = false) {
        if (toShow) {
            PaymentFormSms.$resend.removeClass('d-none');
            PaymentFormSms.$resend.show();
        } else {
            PaymentFormSms.$resend.hide();
        }
    },
    showVerify: function (toShow = false) {
        if (toShow) {
            PaymentFormSms.$verify.removeClass('d-none');
            PaymentFormSms.$verify.show();
        } else {
            PaymentFormSms.$verify.hide();
        }
    },

    changeVerifyText: function ($text) {
        PaymentFormSms.$verify.text($text);
    },

    showErrors: function (errors) {
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

    init: function (afterValidate) {
        PaymentFormSms.callMethod = afterValidate;

        this.$modal = $('#modal-payment-sms');
        this.$form = this.$modal.find('form');
        this.$resend = this.$form.find('button[name=resend]');
        this.$verify = this.$form.find('button[name=verify]');
        this.$paymentFormAttemptId = this.$form.find('input.paymentFormAttemptId');

        this.$verify.on('click', function () {
            PaymentFormSms.verify();
        });
        this.$resend.on('click', function () {
            PaymentFormSms.resend();
        });

    },

    showModal: function () {
        console.log(this.$modal.modal())
        this.$modal.modal().show();
        this.$paymentFormAttemptId.val(localStorage.getItem('paymentFormAttemptId'));
    },

    callMethod: function (){},

    verify: function () {
        PaymentFormSms.showErrors();
        let verifyUrl =  this.$verify.data('verify-url');
        if(!validateCode()) {
            let error = { error: 'Invalid code format'}
            PaymentFormSms.showErrors(Object.values(error));
            return false;
        }

        $.ajax({
            url: verifyUrl,
            type: 'post',
            data: this.$form.serialize() + '&action=validate_phone_code'
        }).done(function (jqXHR) {
            PaymentFormSms.callMethod();
        }).fail(function (jqXHR) {
            var responseData = jqXHR.responseJSON;
            PaymentFormSms.showErrors(Object.values(responseData.error));
            PaymentFormSms.showResend(responseData.allow_resend);
            if(!responseData.allow_verify) {
                PaymentFormSms.showVerify();
            } else {
                PaymentFormSms.changeVerifyText('Try again');
            }

            if(!responseData.allow_resend && !responseData.allow_verify){
               setTimeout(function (){ window.location.reload();}, 5000);
            }
        });


    },

    resend: function () {
        PaymentFormSms.showErrors();
        PaymentFormSms.showResend();
        PaymentFormSms.changeVerifyText('Verify');
        PaymentFormSms.showVerify(true);
        PaymentFormSms.$form.find('input[name=code]').val(null);

        let phoneNumberInput = $('#input-phone-no-part');
        let phoneNumberPart = phoneNumberInput.val();
        let phoneCCPart = $('select[name="phone_cc_part"]').val();
        let resendUrl = phoneNumberInput.data('verify-url');

        $.ajax({
            url: resendUrl,
            type: 'post',
            data: {
                phone_cc_part: phoneCCPart,
                phone_no_part: phoneNumberPart,
                action: 'validate_phone',
                paymentFormAttemptId: localStorage.getItem('paymentFormAttemptId')
            }
        }).done(function (jqXHR) {
            console.log('resend done', jqXHR) //d
        }).fail(function (jqXHR) {
            var response = jqXHR.responseJSON;
            console.log('resend fail', response, jqXHR)
            PaymentFormSms.showErrors(response.errors);
            PaymentFormSms.showResend(response.allow_resend);
        });
    }

};

function nextStep() {
    step++;
    $('#modal-payment-sms').modal().hide();
    $('.modal-backdrop.fade.show').hide();
    changeFormByStep()
}

function changeFormByStep() {
    $('.step').removeClass('d-block').addClass('d-none')
    let className = '.step-' + step;
    $(className).removeClass('d-none').addClass('d-block')
}

function validateCode() {
    var verifyCode = $('.verifyCode').val();

    if(!(/^([0-9]{1,6})$/.test(verifyCode))) {
        return false;
    }
     return true;
}

export default PaymentFormSms;
