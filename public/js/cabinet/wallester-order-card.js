$(document).ready(function () {
    $.ajaxSetup({
        headers: {
            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
        }
    });

    maskedCardNumber()
    issueBtn()



    $('#encryptDetails').submit( function (e) {

        e.preventDefault()
        if (ask2fa.isConfirmed) {
            showCardEncryptedDetails()
        }
    })


    $('.select-card-type').on('click', function () {
        let orderBtn = $('#orderBtn');
        orderBtn.attr('disabled', false)

        $('.select-card-type').removeClass('active')
        $(this).addClass('active')
        orderBtn.attr('data-new-card-order-url', $(this).data('order-card-url'))

        $('.orderSteps').attr('hidden', true)
        if ($(this).attr('id') === 'virtual') {
            $('.orderVirtualSteps').attr('hidden', false)
        } else {
            $('.orderPlasticSteps').attr('hidden', false)
        }

        if (!$(this).data('amount')) {
            orderBtn.attr('disabled', true)
        }
    })

    $('#orderBtn').on('click', function () {
        window.location.href = $(this).data('new-card-order-url')
    })

    $('.activateBtn').on('click', function () {
        let id = $(this).data('wallester-account-detail-id');
        $('.confirmModalId').val(id)
    })

    $('.wallester-details-btn').on('click', function () {
        let id = $(this).data('wallester-account-detail-id');

        if (id) {
            window.location.href = $(this).data('details-url') + '/' + id;
        }
    })

    $('#modal-2fa-confirm-wallester').on('hidden.bs.modal', function () {
        $('.credit-card-icon').attr('disabled', false)
    })

    $('body').delegate( '.credit-card-icon', 'click', function (e) {
        $(this).attr('disabled', true)
        if ($(this).find('i').hasClass('fa-eye')) {
            e.preventDefault();
            $(this).find('i').removeClass('fa-eye').addClass('fa-eye-slash')
            maskedCardNumber()
            $(this).attr('disabled', false)
        } else {
            ask2fa.attachToFormSubmit('#encryptDetails');
            $('#twoFAError').addClass('d-none')
            $('input[name="2fa-confirm-code"]').val('')

            $(this).closest('form').submit()
        }
    })

    $('#updateLimitsBtn').on('click', function () {
        let formId = "#card-limit-update-form";
        $('#card-limits-wallester').modal('hide')
        if (typeof isBO !== 'undefined') {
            $(this).closest('form').submit();
            return false;
        }
        ask2fa.attachToFormSubmit(formId);
        $('#twoFAError').addClass('d-none')
        $('input[name="2fa-confirm-code"]').val('')
        $(formId).submit()
    })

    $('#securityModalBtn').on('click', function () {
        let formId = '#updateSecurityDetails'
        $('#securityModal').modal('hide')
        if (typeof isBO !== 'undefined') {
            $(this).closest('form').submit();
            return false;
        }
        ask2fa.attachToFormSubmit(formId);
        $('#twoFAError').addClass('d-none')
        $('input[name="2fa-confirm-code"]').val('')

        $(formId).submit()
    })

    $('.limitsInputValue').on('change', function () {
        if ($(this).attr('max') < $(this).val()) {
            $(this).val($(this).attr('max'))
        }
    })

    $('.remindCardPinOrCvv').on('click', function () {

        if (typeof isBO !== 'undefined') {
            $(this).closest('form').submit();
            return false;
        }
        ask2fa.attachToFormSubmit( '#' + $(this).attr('id') + 'Form');

        $('#twoFAError').addClass('d-none')
        $('input[name="2fa-confirm-code"]').val('')

        $(this).closest('form').submit()
    })

    $('#remindPinForm').submit( function (e) {
        e.preventDefault()
        $('.pinError').addClass('d-none').removeClass('d-block')
        if (ask2fa.isConfirmed) {
            let url = $(this).attr('action')
            $.ajax({
                type: "POST",
                url: url,
                data: {},
                success: (response) => {
                    $('#showPin').html('<h5 class="text-right">' + response.pin + '</h5>').addClass('d-block').removeClass('d-none')
                    $('#modal-2fa-operation-confirm').modal('hide');
                },
                error: (error) => {
                    $('.pinError').text(error.responseJSON.error).addClass('d-block').removeClass('d-none')
                    $('#modal-2fa-operation-confirm').modal('hide');
                }
            });
        }
    })


    $('#remind3dsPassword').on('click', function () {
        $('#securityModal').modal('hide')
        if (typeof isBO !== 'undefined') {
            $(this).closest('form').submit();
            return false;
        }
        let formId = '#getEncrypted3dsPassword';
        ask2fa.attachToFormSubmit(formId);

        $(formId).submit()
    })

    $('#blockWallesterCardFormBtn').on('click', function (e) {
        e.preventDefault()
        $('#blockCard').modal('hide')

        if (typeof isBO !== 'undefined') {
            $(this).closest('form').submit();
            return false;
        }

        ask2fa.attachToFormSubmit('#blockWallesterCardForm');
        $('#twoFAError').addClass('d-none')
        $('input[name="2fa-confirm-code"]').val('')

        $(this).closest('form').submit()
    })

    $('#remindCVVForm').submit( function (e) {
        e.preventDefault()
        $('.pinError').addClass('d-none').removeClass('d-block')

        let btn = $('#remindCVV');
        if (ask2fa.isConfirmed || typeof isBO !== 'undefined') {
            let url = $(this).attr('action')
            $.ajax({
                type: "POST",
                url: url,
                data: {},
                success: (response) => {
                    $('#showCvv').html('<h5 class="text-right">' + response.cvv + '</h5>').addClass('d-block').removeClass('d-none')
                    $('#modal-2fa-operation-confirm').modal('hide');
                },
                error: (error) => {
                    $('.cvvError').text(error.responseJSON.error ?? error.responseJSON.error.message).addClass('d-block').removeClass('d-none')
                    $('#modal-2fa-operation-confirm').modal('hide');
                }
            });
        }
    })

    $('#getEncrypted3dsPassword').submit( function (e) {
        e.preventDefault()
        if (ask2fa.isConfirmed) {
            let url = $(this).attr('action')
            $.ajax({
                type: "POST",
                url: url,
                data: {},
                success: (response) => {
                    $('#wallester3dsPasswordInput').attr('type', 'text').val(response.password)
                    $('#wallester3dsPasswordInputConfirm').attr('type', 'text').val(response.password)
                    $('#modal-2fa-operation-confirm').modal('hide');
                    $('#securityModal').modal('show')
                },
            });
        }
    });


    $('#prevPageBtn').on('click', function () {
        let url = $(this).data('save-limit-form-data-url')
        $.ajax({
            type: "POST",
            url: url,
            data: $('#wallesterCardOrderLimits').serialize(),
            success: () => {
                window.location.href = $(this).data('prev-page-url');
            },
        });
    });

    $('.confirmWalletCardOrderByCrypto').on('click', function () {
        $('.error-summary').removeClass('d-block').addClass('d-none')
        let url = $(this).data('crypto-payment-wallet-chosen')
        $.ajax({
            type: "POST",
            url: url,
            data: {
                'id': $('input[name="id"]').val(),
                'fromWallet': $('#cryptoWallet').val()
            },
            success: (response) => {
                $(this).attr('hidden', true)
                $('#walletDropdown').attr('style', 'display: none !important');
                let summary = $('.summary-crypto-payment');
                summary.find('.withdraw-fee').text(response.withdrawFee)
                summary.find('.blockchain-fee').text(response.blockchainFee)
                summary.find('.trx-limit').text(response.trxLimit)
                summary.find('.available-limit').text(response.availableLimit)
                summary.find('.card-amount-euro').text(response.cardAmountInEuro)
                summary.find('.card-amount-crypto').text(response.cardAmountCrypto)
                summary.removeClass('d-none').addClass('d-block')
            },
            error: (response) => {
                $('.error-summary').text(response.responseJSON.errors.fromWallet).removeClass('d-none').addClass('d-block')
            }
        });
    })

    $('#prevPageCryptoPayment').on('click', function () {
        $('.confirmWalletCardOrderByCrypto').attr('hidden', false)
        $('#walletDropdown').attr('style', 'display: block !important');
        $('.summary-crypto-payment').removeClass('d-block').addClass('d-none')
    })

    $('#prevPageBtnDelivery').on('click', function () {
        let url = $(this).data('save-delivery-form-data-url')
        $.ajax({
            type: "POST",
            url: url,
            data: $('#wallesterCardOrderDelivery').serialize(),
            success: () => {
                window.location.href = $(this).data('prev-page-url');
            },
        });
    });

    $('.choosePaymentMethod').on('click', function () {
        let paymentForm = $('#paymentMethod');
        paymentForm.find('input[name="type"]').val($(this).data('card-type'))
        paymentForm.find('input[name="id"]').val($(this).data('wallester-account-detail-id'))
    })

    $('#changeDefaultDeliveryAddress').on('click', function () {
        $('.wallesterInputs').prop('readonly', false).removeClass('font-weight-light')
    })
})


function maskedCardNumber() {
    let cardNumberSection = $('.credit-card-number-details');
    let lastFourDigits = cardNumberSection.data('last-four-digits');
    let htmlCode = '<div class="credit-card-number-hide-section">****</div>' +
        '<div class="credit-card-number-hide-section">****</div>' +
        '<div class="credit-card-number-hide-section">****</div>' +
        '<div class="credit-card-number-section">' + lastFourDigits + '</div>'
    cardNumberSection.html(htmlCode)
    $('.credit-card-date').text('**/**')
    $('.credit-card-cvv').text('CVV')
}

function showCardEncryptedDetails() {

    let url =  $('.credit-card-icon').data('show-card-encrypted-details-url')
    $.ajax({
        type: "POST",
        url: url,
        data: {
            "card_id": $('input[name="id"]').val(),
        },
        success: (response) => {

            ask2fa.encryptionIsConfirmed = true;
            $('#modal-2fa-operation-confirm').modal('hide');

            $('.credit-card-date').text(response.expiryDate)
            $('.credit-card-cvv').text(response.cvv)
            $('.credit-card-number').html(
                '<div class="credit-card-number-section">' + response.cardNumber.substr(0, 4) + '</div>' +
                '<div class="credit-card-number-section">' + response.cardNumber.substr(4, 4) + '</div>' +
                '<div class="credit-card-number-section">' + response.cardNumber.substr(8, 4) + '</div>' +
                '<div class="credit-card-number-section">' + response.cardNumber.substr(12, 4) + '</div>'
            )
            $('.fa-eye-slash').removeClass('fa-eye-slash').addClass('fa-eye')
            $('.credit-card-icon').attr('disabled', false)
        },
        error: (error) => {

            let errorHTML;
            for (const [key, message] of Object.entries(error.responseJSON)) {
                errorHTML = '<p class="error-text">' + message + '</p>'
            }
            $('.error-text-list').html(errorHTML)

        }
    });
}


function updateCardLimits() {
    let form = $('#card-limit-update-form');
    let url = form.attr('action')

    $.ajax({
        type: "PATCH",
        url: url,
        data: form.serialize(),
        success: (response) => {
            $('#modal-2fa-operation-confirm').modal('hide');
        },
        error: (error) => {

            let errorHTML;
            for (const [key, message] of Object.entries(error.responseJSON)) {
                errorHTML = '<p class="error-text">' + message + '</p>'
            }
            $('.error-text-list').html(errorHTML)

        }
    });
}


function issueBtn() {
    let wallesterIssueButton = $('.wallester-issue-btn')
    let creditCard = $('.credit-card')
    wallesterIssueButton.css({'margin-left': creditCard.width() - wallesterIssueButton.width()})
    wallesterIssueButton.css({'opacity': 1})
}
