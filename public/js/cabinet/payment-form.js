import PaymentFormSms from "./payment-form-sms.js";

$(document).ready(function () {
    PaymentFormSms.init(afterVerify);

        let amount = $('#amount');
        let maxPaymentAmount = parseInt(amount.data('max-amount-eur'));
        let minPaymentAmount = parseInt(amount.data('min-amount-eur'));
        let maxAmountUrl = amount.data('get-max-amount-url');
        let minAmountUrl = amount.data('get-min-amount-url');
        let getRateCryptoUrl = amount.data('get-rate-crypto-url');
        let phoneNumberPart = $("#input-phone-no-part");
        let emailPart = $('#paymentFormEmail');
        let emailCodeInput = $('.codeInputPaymentForm');
        var complianceStatus = null;
        let allowSubmit = false;
        let form = $('#merchantPaymentForm');
        let userVerified = false;
    try {

        var complianceStart = localStorage.getItem('complianceStart');
        $('.main-form').attr('hidden', false)
        $('.storage-error').attr('hidden', true)
    } catch (e) {
        $('.main-form').attr('hidden', true)
        $('.storage-error').attr('hidden', false)
    }
        var cProfileId = null;



        $('body').on('change', '#cryptoCurrency, #amount, #fiatCurrency', function () {
            getExpectedAmount();
        })

        hideErrorText()

    function getExpectedAmount() {
        $.ajaxSetup({
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            }
        });

        let amountContainer = $('#amount')

        $.ajax({
            url: minAmountUrl,
            type: 'post',
            data: {
                fromCurrency: $('#fiatCurrency').val()
            },
            success: (data) => {
                minPaymentAmount = data.minAmount;
                let amount = $('#amount').val();
                if (parseFloat(amount) < minPaymentAmount) {
                    amountContainer.val(0);
                    amountContainer.data('old', 0);
                }
                amountContainer.attr('min', minPaymentAmount)
                fillExpectedAmount()
            },
        })


    }

    function fillExpectedAmount()
    {
        let from = $('#fiatCurrency').val();
        let to = $('#cryptoCurrency').val();
        let amountContainer = $('#amount');
        let amount = amountContainer.val();

        if (from == 'EUR') {
            maxPaymentAmount = parseInt(amountContainer.attr('max'));
        } else {
            $.ajax({
                url: maxAmountUrl,
                type: 'post',
                data: {from, amount: maxPaymentAmount},
                success: (data) => {
                    let responseAmount = parseInt(maxPaymentAmount);
                    maxPaymentAmount = ( responseAmount / data) * responseAmount;
                },
            })

        }

        let expectedAmountClass = $('.expectedAmount');
        if (amount == 0 && amount !== '') {
            expectedAmountClass.text('Buy ' + 0);
            $('#expectedAmount').val(0);
        } else if(amount == '') {
            expectedAmountClass.text('Buy');
            $('#expectedAmount').val('');
        } else if (from && to && amount) {

            $.ajax({
                url: getRateCryptoUrl,
                type: 'post',
                data: {from, to, amount: 1 },
                success: function (data) {
                    $('#expectedAmount').val((amount / data).toFixed(8));
                    $('.expectedAmount').text('Buy ' + (amount / data).toFixed(8) + ' ' + to);
                },
            })
        }
    }

    setInterval( getExpectedAmount(), 20000);


    $('.verifyPhoneBtn').on('click', function () {
        hideErrorText()
        $(this).attr('disabled', true)
        let phoneNumberInput = $('#input-phone-no-part');
        let phoneNumberPart = phoneNumberInput.val();
        let phoneCCPart = $('select[name="phone_cc_part"]').val();
        let url = phoneNumberInput.data('verify-url');
        let paymentFormAttemptId = localStorage.getItem('paymentFormAttemptId');
        const self = this;

        $.ajax({
            url: url,
            method: 'POST',
            data: {
                phone_cc_part: phoneCCPart,
                phone_no_part: phoneNumberPart,
                action: 'validate_phone',
                paymentFormAttemptId: paymentFormAttemptId
            },
            dataType: 'json',
            success: (data) => {
                if (data.isUserRegistered) {
                    step = 'login';
                    changeFormByStep();
                } else {
                    PaymentFormSms.showModal();
                    $(self).attr('disabled', false)
                }
            },
            error: (errors) => {
                $(this).attr('disabled', false)
                for (const [key, message] of Object.entries(errors.responseJSON.error)) {
                    let errorDiv = $('#error_' + key)
                    errorDiv.text(message)
                    errorDiv.show()
                }
            }
        });
    })

    $('.login').on('click', function () {
        hideErrorText()
        $(this).attr('disabled', true)
        let password = $('#paymentFormUserPassword').val()
        if (!password) {
            $(this).attr('disabled', false)
            let passwordErrorBlock = $('[data-error-target=paymentFormUserPassword]');
            let message = passwordErrorBlock.data('empty-password-message');
            passwordErrorBlock.text(message).show()
            return false;
        }
        let loginUrl = $(this).data('login-user-url');
        $.ajax({
            url: loginUrl,
            type: 'post',
            data: {
                paymentFormAttemptId: getPaymentFormAttemptId(),
                paymentFormUserPassword: password,
                action: 'login_user'
            },
            success: (data) => {
                $(this).attr('disabled', false)

                step = data.step
                changeFormByStep()
                if (step == 1 && data.userVerified) {
                    userVerified = true;
                    prepareFormForLastSubmit()
                }

                if (data.timerSeconds) {
                    showResendTimer(data.timerSeconds)
                }

                if (step == 5) {
                    localStorage.setItem('complianceStart', true)
                    openComplianceForm(data)
                }
            },
            error: (errors) => {
                let errorBlock = $('[data-error-target=paymentFormUserPassword]');
                $(this).attr('disabled', false)
                errorBlock.text(errors.responseJSON.error)
                errorBlock.show()
            }
        })
    })

    $('.expectedAmount').on('click', function () {
        let amount = parseFloat($('#amount').val());
        if (!amount) {
            return false;
        }
        $(this).attr('disabled', true)

        if ($(this).attr('id') == 'paymentFormSubmitBtn') {
            form.trigger('customSubmit');
        } else {
            if (step == 1) {
                if(!$('#cryptoCurrency').val() || !$('#fiatCurrency').val()) {
                    $(this).attr('disabled', false)
                    return false;
                }
                userFirstLastNameSetStep()
            } else {
                nextStep()
            }
        }
    })

    function saveInitialData() {
        hideErrorText()
        let saveInitialDataUrl = $('.step-1-btn').data('save-initial-data-url')
        let amount = $('input[name="paymentFormAmount"]').val();
        let currency = $('select[name="currency"]').val();
        let cryptoCurrency = $('select[name="cryptoCurrency"]').val();
        let paymentFormId = $('#paymentFormId').val();
        let wallet_address = $('input[name="paymentFormToWalletAddress"]').val();
        let first_name = $('input[name="first_name"]').val();
        let last_name = $('input[name="last_name"]').val();

        $.ajax({
            url: saveInitialDataUrl,
            type: 'post',
            data: {
                paymentFormAmount: amount,
                currency: currency,
                cryptoCurrency: cryptoCurrency,
                paymentFormId: paymentFormId,
                wallet_address: wallet_address,
                action: 'save_initial_form',
                first_name: first_name,
                last_name: last_name,
            },
            success: (data) => {
                localStorage.setItem('paymentFormAttemptId', data.paymentFormAttemptId)
            },
            error: (data) => {
                step = 1;
                changeFormByStep();
                $('p[data-error-target="paymentFormAmount"]').text(data.responseJSON.error).show()
            }
        })
    }

    let paymentFormToWalletAddress = $('#paymentFormToWalletAddress');
    paymentFormToWalletAddress.keydown(function (e) {
        $('p[data-error-target="paymentFormToWalletAddress"]').hide();
    });
    paymentFormToWalletAddress.keyup(function (e) {
        $('p[data-error-target="paymentFormToWalletAddress"]').hide();
    });


    if(complianceStart) {
        startCompliance()

    } else if (cProfileId) {
        step = 6;
        changeFormByStep();
    }

    function startCompliance() {
        step = 5;
        changeFormByStep();
        let getComplianceDataUrl = $('.complianceUrl').data('get-compliance-data-url')
        $.ajax({
            url: getComplianceDataUrl,
            type: 'post',
            data: {
                paymentFormAttemptId: localStorage.getItem('paymentFormAttemptId'),
                action: 'get_compliance_data'
            },
            success: function (data) {
                fillFormData(data.currentFormAttempt)
                getExpectedAmount();
                if (data.kyc) {
                    openComplianceForm(data)
                } else {
                    prepareFormForLastSubmit()
                }
            },
        })
    }

    function fillFormData(currentAttempt) {
        $('input[name="paymentFormAmount"]').val(currentAttempt.amount)
        $('select[name="currency"]').val(currentAttempt.from_currency)
        $('select[name="cryptoCurrency"]').val(currentAttempt.to_currency)
        $('input[name="paymentFormToWalletAddress"]').val(currentAttempt.wallet_address)
    }

    function checkComplianceStatus() {
        var url = $('#checkComplianceStatus').data('verify-compliance-status-url');
        $.ajax({
            url: url,
            method: 'POST',
            data: {
                paymentFormAttemptId: getPaymentFormAttemptId(),
                action: 'get_compliance_data'
            },
            dataType: 'json',
            success: (data) => {
                switch (data['step']) {
                    case 5:
                        break;
                    case 6:
                        nextStep();
                        clearInterval(complianceStatus);
                        localStorage.removeItem('complianceStart');
                        complianceStart = null;
                        prepareFormForLastSubmit();
                        break;
                }
            },
            error: () => {

            }
        });
    }

    form.on('customSubmit', function () {
        hideErrorText()

        let sendData = {};
        let url = '';
        let isClientOutsideForm = $(this).data('is-client-outside-form');
        let paymentFormSubmitBtn = $('#paymentFormSubmitBtn');
        paymentFormSubmitBtn.removeClass('d-block').addClass('d-none')
        $(this).append('<input hidden name="paymentFormAttemptId" value="' + localStorage.getItem('paymentFormAttemptId') + '">');
        if (isClientOutsideForm) {
            if (form.data('is-client-outside-form') && !$('#paymentFormToWalletAddress').val()) {
                $('p[data-error-target="paymentFormToWalletAddress"]').show().text('Wallet address is incorrect');
                paymentFormSubmitBtn.removeClass('d-none').addClass('d-block').attr('disabled', false)
                // $(this).attr('disabled', false)
                return false;
            }
            step = 6;
            changeFormByStep()
            let walletAddress = $('#paymentFormToWalletAddress');
            url = walletAddress.data('verify-wallet-url');
            sendData = {
                wallet_address: walletAddress.val(),
                currency: $('#cryptoCurrency').val(),
                paymentFormAttemptId: getPaymentFormAttemptId(),
                verify_wallet_address: true
            };
            $.ajax({
                url: url,
                method: 'POST',
                data: sendData,
                dataType: 'json',
                success: () => {
                    try {
                        allowSubmit = true;
                        $(this).submit()
                    } catch (e) {
                        console.log(e)
                    }
                },
                error: () => {
                    nextStep()
                    $('.wallet-verification-error').removeClass('d-none').addClass('d-block')
                }
            });

        } else {
            allowSubmit = true;
            $(this).submit()
        }
    });

    form.on('submit', function (e) {
        e.preventDefault()
        if (allowSubmit) {
            $(this).unbind('submit');
            $(this).submit();
            return true;
        }
        return false;
    })

    $('#finishOperation').on('click', function () {
        $(this).attr('hidden', true);
        $('.end-operation').addClass('d-block').removeClass('d-none')
        $('.pay-step').addClass('d-none').removeClass('d-block')
    })

    function nextStep() {
        step++;
        changeFormByStep()
    }

    $('a.back-step').click(function () {
        $('#paymentFormSubmitBtn').removeAttr('id');
        step--;

        if (step == 4) {
            step--;
        }

        if (step == 1) {
            step = 9;
        }

        if (step == 8) {
            step = 1;
        }

        changeFormByStep()
    })
    $('a.back-step-login').click(function () {
        $('#paymentFormSubmitBtn').removeAttr('id');
        step = 2;
        changeFormByStep()
    })

    $('a.back-to-submit-step').click(function () {
        prepareFormForLastSubmit()
    })

    function changeFormByStep() {
        $('.step').removeClass('d-block').addClass('d-none')
        let className = '.step-' + step;
        $(className).removeClass('d-none').addClass('d-block')

        let btnClassName = className + '-btn';
        $(btnClassName).attr('disabled', false);

        if (step === 5) {
            $('.br-hide-step-5').removeClass('d-block').addClass('d-none')
            $(className).parents().find('.login-form-outer-merchant-payment').addClass('largeForm')
        } else {
            $('.br-hide-step-5').removeClass('d-none').addClass('d-block')
            $(className).parents().find('.login-form-outer-merchant-payment').removeClass('largeForm')
        }
    }

    phoneNumberPart.keydown(function (e) {
        validatePhone()
    });
    phoneNumberPart.keyup(function (e) {
        validatePhone()
    });

    emailPart.on('change', function () {
        validateEmail()
    })

    amount.keydown(function () {
        // Save old value.
        if (!$(this).val() || (parseFloat($(this).val()) <= maxPaymentAmount && parseFloat($(this).val()) >= 0))
            $(this).data("old", $(this).val());
    });

    amount.keyup(function () {
        // Check correct, else revert back to old value.
        if (!$(this).val() || (parseFloat($(this).val()) <= maxPaymentAmount && parseFloat($(this).val()) >= 0))
            ;
        else
            $(this).val($(this).data("old"));
    });

    function validatePhone() {
        var inputPhoneCcPart = $('[name="phone_cc_part"]').val();
        var inputPhoneNoPart = $('#input-phone-no-part').val();
        let verifyPhoneBtn = $('.verifyPhoneBtn');

        hideErrorText();

        if(!(/^([0-9]{1,5})$/.test(inputPhoneCcPart))) {
            verifyPhoneBtn.attr("disabled", true);
            return false;
        }
        if(!(/^([0-9]{6,12})$/.test(inputPhoneNoPart))) {
            let errorMessage = $('input[name="phone_no_part"]').data('phone-number-error');
            $('#phone_no_part').show().text(errorMessage);
            verifyPhoneBtn.attr("disabled", true);
            return false;
        }

        verifyPhoneBtn.removeAttr("disabled");
    }

    function validateEmail() {
        let emailInput = $('#paymentFormEmail');
        let email = emailInput.val();
        let errorText = emailInput.data('error-text');
        let verifyEmailBtn = $('.verifyEmail');
        let emailError = $('#emailError');

        emailError.hide()


        if(!(/^\w+([\.-]?\w+)*@\w+([\.-]?\w+)*(\.\w{2,25})+$/.test(email))) {
            emailError.show().text(errorText);
            verifyEmailBtn.attr("disabled", true);
            return false;
        }

        verifyEmailBtn.removeAttr("disabled");
    }


    function hideErrorText() {
        $('.error-text').hide();
    }

    $('.verifyEmail').on('click', function () {
        $(this).attr('disabled', true)
        // $('.error-text').hide();
        let emailInput = $('#paymentFormEmail');
        let email = emailInput.val();
        let verifyEmailUrl = emailInput.data('verify-email-url');
        $('.resendButtonContainer').hide();
        const self = this;

        $.ajax({
            url: verifyEmailUrl,
            method: 'POST',
            data: {
                email: email,
                action: 'validate_email',
                paymentFormAttemptId: getPaymentFormAttemptId()
            },
            dataType: 'json',
            success: (data) => {
                $(self).attr('disabled', false)

                step = data.step
                changeFormByStep()
                if (step == 1 && data.userVerified) {
                    prepareFormForLastSubmit();
                }

                if (data.timerSeconds) {
                    showResendTimer(data.timerSeconds)
                }

                if (step == 5 && data.kyc) {
                    openComplianceForm(data)
                }

            },
            error: (error) => {
                $(this).attr('disabled', false)
                let emailErrorContainer = $('#emailError');
                emailErrorContainer.show();
                emailErrorContainer.html(error.responseJSON.error)
            }
        });

        function showResendTimer(seconds)
        {
            let resendContainer = $('.resendContainer');
            let resendTimer = resendContainer.find('.resendTimer');
            let timerDisplayed = false;
            let interval = setInterval(function () {
                seconds--;
                resendTimer.text(seconds)
                if (!timerDisplayed) {
                    displayTimer()
                    timerDisplayed = true;
                }
                if (seconds <= 0) {
                    clearInterval(interval)
                    displayResendButton();
                }
            }, 1000)
        }
        function displayResendButton()
        {
            $('.resendButtonContainer').show();
            $('.resendContainer').find('.resendTimerContainer').hide();
        }

        function displayTimer()
        {
            $('.resendButtonContainer').hide();
            $('.resendContainer').find('.resendTimerContainer').show();
        }
    })



    emailCodeInput.on('input keypress', function(event) {
        hideErrorText()

        if (event.type == "keypress" && (event.shiftKey || event.which <= 47 || event.which >= 58)) {
            return false;
        }

        if ($(this).val() > 9) {
            let val = $(this).val();
            val = parseInt(String(val).charAt(0));
            $(this).val(val)
        } else {
            if (!$(event.currentTarget).hasClass('lastInput')) {
                $(event.currentTarget).next('input').focus();
            }
        }
        let code = $("input[name='paymentFormEmailCode[]']").map(function(){
            return $(this).val();
        }).get().join('');
        let verifyEmailCodeUrl = $('#paymentFormEmailCode').data('verify-email-code-url');
        let email =  $('#paymentFormEmail').val();
        let paymentFormId =  $('#paymentFormId').val();

        if (code.length == 6) {
            emailCodeInput.attr('disabled', true)
            $.ajax({
                url: verifyEmailCodeUrl,
                method: 'POST',
                data: {
                    code: code,
                    email: email,
                    action: 'validate_email_code',
                    paymentFormId: paymentFormId,
                    paymentFormAttemptId: getPaymentFormAttemptId()
                },
                dataType: 'json',
                success: (data) => {
                    nextStep();
                },
                error: (error) => {
                    let emailCodeErrorContainer = $('#emailCodeError');
                    emailCodeErrorContainer.show();
                    emailCodeErrorContainer.html(error.responseJSON.error)
                    if(error.responseJSON.attempts) {
                        $('.resendButtonContainer').hide();
                        $('.resendContainer').hide();
                        return false;
                    }
                    emailCodeInput.attr('disabled', false)
                    emailCodeInput.val('')
                }
            });
        }

    })


    function afterVerify() {
        if( !$('#paymentFormEmail').val() ){
            return false;
        }
        $.ajax({
            url: $('#verifyUrl').val(),
            method: 'POST',
            data: {
                action: 'validate_form',
                paymentFormId: $('#paymentFormId').val(),
                paymentFormAttemptId: getPaymentFormAttemptId()
            },
            dataType: 'json',
            success: (data) => {
                $('#modal-payment-sms').modal().hide();
                $('.modal-backdrop.fade.show').hide();
                $('body').removeClass('modal-open')
                $('input[name="code"]').val('')
                if (!data.kyc) {
                    prepareFormForLastSubmit();
                    return true;
                }
                nextStep();
                openComplianceForm(data)
                cProfileId = data.cProfileId;
                localStorage.setItem('complianceStart', true)
            },
            error: (error) => {
            }
        });
    }

    function openComplianceForm(data) {
        $('body').attr('class', '');
        switch (data['complianceProvider']) {
            case 'sum_sub':
                openSumSubForm(data)
               break;
        }
        setTimeout(function () {
            $('iframe').attr('scrolling', 'on')
        }, 500)
        complianceStatus = setInterval(checkComplianceStatus, 10000);
        paymentFormReset(true)
    }

    function openSumSubForm(data) {
         launchWebSdk(
            data['sumSubApiUrl'],
            data['sumSubNextLevelName'],
            data['token'],
            data['email'],
            data['phone'],
            null,
            data['contextId']) //TODO add translation messages
    }

    $('.paymentFormResetBtn button').click(function () {
        step = 1;
        changeFormByStep();
        paymentFormReset(false)
        clearInterval(complianceStatus);
        localStorage.removeItem('paymentFormAttemptId');
        localStorage.removeItem('complianceStart');
        location.reload();
    });


    emailCodeInput.bind('paste', function (e) {
        let pastedData = e.originalEvent.clipboardData.getData('text');
        let charArray = pastedData.split('');
        if (charArray.length == 6) {
            $('.codeInputPaymentForm').each(function (index) {
                $(this).val(parseInt(charArray[index]));
            })
        }
    })

    function paymentFormReset(show = false) {
        if (show) {
            $('.paymentFormResetBtn').removeClass('d-none').addClass('d-block')
        } else {
            $('.paymentFormResetBtn').removeClass('d-block').addClass('d-none')
        }
    }

    function getPaymentFormAttemptId() {
        return localStorage.getItem('paymentFormAttemptId');
    }

    function userFirstLastNameSetStep()
    {
        step = 9;
        changeFormByStep()
    }

    $('.next-step').click(function () {
        if(!validateUserFirstLastName()) {
            return false;
        }
        saveInitialData()
        step = 1;
        nextStep();
    })

    function prepareFormForLastSubmit() {
        let isClientOutsideForm = form.data('is-client-outside-form');
        step = 1;
        changeFormByStep()
        paymentFormReset()
        $('.firstStepBtn').removeClass('d-block').addClass('d-none');

        let paymentFormBtn = $('.paymentFormBtn');
        paymentFormBtn.removeClass('d-none').addClass('d-block');
        paymentFormBtn.find('button')
            .attr('id', 'paymentFormSubmitBtn')
            .removeClass('d-none')
            .addClass('d-block')
            .attr('disabled', false);

        if (isClientOutsideForm) {
            $('.paymentFormToWalletAddressContainer').attr('hidden', false)
        }
    }

    function validateUserFirstLastName()
    {
        $('input[name=first_name]').val($('input[name=first_name]').val().trim());
        $('input[name=last_name]').val($('input[name=last_name]').val().trim());

        let first_name = $('input[name=first_name]').val();
        let last_name = $('input[name=last_name]').val();

        $('#first_name_error, #last_name_error').hide();

        if (!(/^[a-zA-Z]{3,16}$/g.test(first_name))) {
            $('#first_name_error').show().text('Invalid first name');
            return false;
        }
        if (!(/^[a-zA-Z]{3,20}$/g.test(last_name))) {
            $('#last_name_error').show().text('Invalid last name');
            return false;
        }
        return true;
    }

})
