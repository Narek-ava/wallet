import PaymentFormSms from "./payment-form-sms.js";

$(document).ready(function () {
    PaymentFormSms.init();

    let amount = $('#amount');
    let amountInEuro = $('#amountInEuro');
    let minAmountText = 'Minimum ##amount## ##currency##';
    let minAmountTextEur = 'Minimum ##amount## EUR';
    let minAmountUrl = amount.data('get-min-amount-url');
    let changedAmountsUrl = amountInEuro.data('get-change-amounts-url');
    let minPaymentAmount = amount.attr('min');
    let minPaymentAmountInEuro = amountInEuro.attr('min');
    let step1Btn = $('.step-1-btn');
    let step2Btn = $('.step-2-btn');
    let body = $('body');
    localStorage.removeItem('paymentFormAttemptId')

    try {
        $('.main-form').attr('hidden', false)
        $('.storage-error').attr('hidden', true)
    } catch (e) {
        $('.main-form').attr('hidden', true)
        $('.storage-error').attr('hidden', false)
    }

    body.on('change', '#cryptoCurrency', function () {
        getMinAmount()
        amount.val(0)
        amountInEuro.val(0)
        validateAmount();
    })

    body.on('change', '#amount', function () {
        getAmounts($('#cryptoCurrency').val(), amount.val(), true);
        validateAmount();
    })

    body.on('change', '#amountInEuro', function () {
        getAmounts($('#cryptoCurrency').val(), amountInEuro.val(), false);
        validateAmount();
    })

    hideErrorText()

    function getMinAmount() {
        $.ajaxSetup({
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            }
        });
        let crypto = $('#cryptoCurrency').val()
        $.ajax({
            url: minAmountUrl,
            type: 'post',
            data: {
                cryptocurrency: crypto,
            },
            success: (data) => {
                minPaymentAmount = data.minAmount;
                minPaymentAmountInEuro = data.minAmountInEuro;
                amount.attr('min', minPaymentAmount)
                amountInEuro.attr('min', minPaymentAmountInEuro)

                let text = minAmountText.replace('##amount##', minPaymentAmount).replace('##currency##', crypto)
                $('#min-size').text(text)

                let textEuro = minAmountTextEur.replace('##amount##', minPaymentAmountInEuro)
                $('#min-size-euro').text(textEuro)
            },
        })
    }

   function getAmounts(cryptocurrency, payAmount, fromCryptoToFiat) {
        hideErrorText()
       $.ajaxSetup({
           headers: {
               'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
           }
       });
        $.ajax({
            url: changedAmountsUrl,
            type: 'post',
            data: {
                'amount': payAmount,
                'cryptocurrency': cryptocurrency,
                'fromCryptoToFiat': fromCryptoToFiat,
            },
            success: (data) => {
                amount.val(data.amount);
                amountInEuro.val(data.amountInEuro);
            },
            error: (error) => {
                for (const [key, message] of Object.entries(error.responseJSON.errors)) {
                    let errorSelector = $('p[data-error-target="' + key + '"]')
                    errorSelector.text(message)
                    errorSelector.show()
                }
                amountInEuro.val(0);
                amount.val(0);
                $('.step-1-btn').attr('disabled', true)
            },
        })
    }

    function validateAmount() {
        if (!amount.val() || (parseFloat(amount.val()) > minPaymentAmount && parseFloat(amount.val()) >= 0))
            amount.data("old", amount.val());
        if (!amountInEuro.val() || (parseFloat(amountInEuro.val()) > minPaymentAmountInEuro && parseFloat(amountInEuro.val()) >= 0))
            amountInEuro.data("old", amountInEuro.val());
        if (!amount.val() || (parseFloat(amount.val()) < minPaymentAmount || parseFloat(amount.val()) < 0))
            amount.val(amount.data("old"));
        minPaymentAmount = amount.attr('min');
        if (!amountInEuro.val() || (parseFloat(amountInEuro.val()) < minPaymentAmountInEuro || parseFloat(amountInEuro.val()) < 0))
            amountInEuro.val(amountInEuro.data("old"));
        minPaymentAmountInEuro = amountInEuro.attr('min');
        let amountToPay = amount.val();
        if (parseFloat(amountToPay) < minPaymentAmount) {
            amount.val(0);
            amountInEuro.val(0);
            amount.data('old', 0);
        }
        $('.step-1-btn').attr('disabled', false)
    }

    step1Btn.on('click', function () {
        let amountContainer = $('#amount');
        let amount = parseFloat(amountContainer.val());
        if (!amount) {
            $('p[data-error-target="paymentFormAmount"]').text('Amount is required').show()
            return false;
        }
        $(this).attr('disabled', true)

        if (step === 1 && !$('#cryptoCurrency').val()) {
            $('p[data-error-target="cryptoCurrency"]').text('Currency is required').show()
            $(this).attr('disabled', false)
            return false;
        }
        saveInitialData()
        nextStep()
    })

    step2Btn.on('click', function () {
        nextStep()
    })

    function saveInitialData() {
        hideErrorText()
        let saveInitialDataUrl = step2Btn.data('save-initial-data-url')
        let amount = $('input[name="paymentFormAmount"]').val();
        let cryptoCurrency = $('select[name="cryptoCurrency"]').val();
        let paymentFormId = $('#paymentFormId').val();
        let wallet_address = $('input[name="paymentFormToWalletAddress"]').val();
        let paymentFormAttemptId = getPaymentFormAttemptId();

        $.ajaxSetup({
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            }
        });
        $.ajax({
            url: saveInitialDataUrl,
            type: 'post',
            data: {
                paymentFormAmount: amount,
                currency: cryptoCurrency,
                cryptoCurrency: cryptoCurrency,
                paymentFormId: paymentFormId,
                paymentFormAttemptId: paymentFormAttemptId,
                wallet_address: wallet_address,
                action: 'save_initial_form',
            },
            success: (data) => {
                localStorage.setItem('paymentFormAttemptId', data.paymentFormAttemptId)
                $('.summary-amount').text(data.amount)
                $('.summary-fee').text(data.fee)
                $('.summary-details').text(data.details)
                $('.summary-total').text(data.total)
            },
            error: (data) => {
                step = 1;
                changeFormByStep();
                $('p[data-error-target="paymentFormAmount"]').text(data.responseJSON.errors.paymentFormAmount).show()
            }
        })
    }

    $('.savePayerData').on('click', function () {
        hideErrorText()
        $(this).attr('disabled', true)
        let phoneNumberInput = $('#input-phone-no-part');
        let phoneNumberPart = phoneNumberInput.val();
        let phoneCCPart = $('select[name="phone_cc_part"]').val();
        let firstName = $('#firstName').val();
        let lastName = $('#lastName').val();
        let email = $('#email').val();
        let url = $(this).data('save-payer-data-url');
        let paymentFormAttemptId = getPaymentFormAttemptId()

        $.ajax({
            url: url,
            method: 'POST',
            data: {
                phone_cc_part: phoneCCPart,
                phone_no_part: phoneNumberPart,
                first_name: firstName,
                last_name: lastName,
                email: email,
                paymentFormAttemptId: paymentFormAttemptId
            },
            dataType: 'json',
            success: (data) => {
                $(this).attr('disabled', false)
                $('.qrCode').attr('src', data.qr)
                let walletAddressInput = $('.walletAddress')
                walletAddressInput.val(data.address)
                walletAddressInput.attr('id', 'text' + data.cryptoCurrency)
                $('.walletAddressCopy').attr('id', data.cryptoCurrency)
                $('.amountAndCurrency').text(data.amount)
                $('.cryptocurrencyImage').html('<img width="40px" src="/cratos.theme/images/' + data.currencyImage + '" alt="">\n')
                nextStep()
            },
            error: (data) => {
                for (const [key, message] of Object.entries(data.responseJSON.errors)) {
                    let errorSelector = $('p[data-error-target="' + key + '"]')
                    errorSelector.text(message)
                    errorSelector.show()
                }
                console.log(data)
            }
        });
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
        step--;
        changeFormByStep()
    })

    function changeFormByStep() {
        $('.step').removeClass('d-block').addClass('d-none')
        let className = '.step-' + step;
        $(className).removeClass('d-none').addClass('d-block')

        let btnClassName = className + '-btn';
        $(btnClassName).attr('disabled', false);
    }


    function validatePayerData() {
        hideErrorText();
        let inputPhoneNoPart = $('#input-phone-no-part').val();
        let validPhone = false;
        if (inputPhoneNoPart) {
            let inputPhoneCcPart = $('[name="phone_cc_part"]').val();
            validPhone = validatePhone(inputPhoneCcPart, inputPhoneNoPart)
        }

        let validEmail = false;
        let email = $('#email').val();
        if (email) {
            validEmail = validateEmail(email)
        }

        let validName = false;
        let firstname = $('#firstName').val();
        if (firstname) {
            validName = validateName(firstname, 'firstName')
        }

        let lastname = $('#lastName').val();
        if (lastname) {
            validName = validName && validateName(lastname, 'lastName')
        }

        return validPhone && validEmail && validName;
    }

    function validatePhone(inputPhoneCcPart, inputPhoneNoPart) {
        let phoneNumberPartError = $('#phoneNoPartError');
        let phoneNumberPartErrorMessage = phoneNumberPartError.data('error-text');
        phoneNumberPartError.hide()
        if (!(/^([0-9]{1,5})$/.test(inputPhoneCcPart))) {
            phoneNumberPartError.text(phoneNumberPartErrorMessage).show()
            return false;
        } else if (!(/^([0-9]{6,12})$/.test(inputPhoneNoPart))) {
            phoneNumberPartError.text(phoneNumberPartErrorMessage).show()
            return false;
        }
        return true;
    }

    function validateEmail(email) {
        let emailError = $('#emailError');
        let emailErrorText = emailError.data('error-text');
        emailError.hide()
        if (!(/^\w+([\.-]?\w+)*@\w+([\.-]?\w+)*(\.\w{2,25})+$/.test(email))) {
            emailError.text(emailErrorText).show();
            return false;
        }
        return true;
    }

    function validateName(value, id) {

        let error =  $('#' + id + 'Error');
        let errorMessage = error.data('error-text');
        error.hide()
        if (!(/^[a-zA-Z]+((['. -][a-zA-Z ])?[a-zA-Z]*)*$/.test(value))) {
            error.text(errorMessage).show()
            return false;
        }
        return true;
    }

    $('#input-phone-no-part, #email, #firstName, #lastName').on('change', function () {
        let savePayerData = $('.savePayerData');

        if (validatePayerData()) {
            savePayerData.attr('disabled', false)
        } else {
            savePayerData.attr('disabled', true)
        }
    })

    function hideErrorText() {
        $('.error-text').hide();
    }

    function getPaymentFormAttemptId() {
        return localStorage.getItem('paymentFormAttemptId');
    }


})
