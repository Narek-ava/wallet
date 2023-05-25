$(document).ready(function () {
    $.ajaxSetup({
        headers: {
            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
        }
    });


    $('.amount').on('change', function () {
        if ($('#fiat_currency').val()) {
            let amountCrypto = $('.amount').val();
            let from = $('.coin').val();
            let to = $('#fiat_currency').find(':selected').text().trim();
            getExpectedAmountToFiatOperation(from, to, amountCrypto)
        }
    })

    $('.amountFiat').on('change', function () {
        if ($('#fiat_currency').val()) {
            let amountFiat = $('.amountFiat').val();
            let from = $('#fiat_currency').find(':selected').text().trim();
            let to = $('.coin').val();
            getExpectedAmountToFiatOperation(from, to, amountFiat, false)
        }

    })

    $('#fiat_currency').on('change', function () {
        let selectedFiat = $(this).find(':selected');
        if ($(this).val() !== '') {
            $('.display_fiat_balance').text(selectedFiat.data('balance'));
            $('.display_fiat_balance').parent().show()
        } else {
            $('.display_fiat_balance').parent().hide()
        }

        let amount = $('.amount').val();
        let cryptoToFiat = true;
        if (!amount) {
            amount = $('.amountFiat').val();
            cryptoToFiat = false;
        }
        let from = selectedFiat.text().trim();
        let to = $('.coin').val();
        getExpectedAmountToFiatOperation(from, to, amount, cryptoToFiat)
    })

    function getExpectedAmountToFiatOperation(fromCurrency, toCurrency, changeAmount, cryptoToFiat = true) {

        if (fromCurrency !== '' && toCurrency !== '' && changeAmount > 0) {
            let from = fromCurrency;
            let to = toCurrency;
            let amount = changeAmount;

            if (!cryptoToFiat) {
                from = toCurrency;
                to = fromCurrency;
                amount = 1;
            }

            $.ajax({
                url: API + 'get-rate-crypto-fiat',
                type: 'post',
                data: {
                    'from': from,
                    'to': to,
                    'amount': amount
                },
                success: (data) => {
                    if (cryptoToFiat) {
                        $('.amountFiat').val(data);
                    } else {
                        $('.amount').val(Math.round((changeAmount/data + Number.EPSILON)*100) / 100)
                    }
                }
            })
        }
    }


});


var currentTab = 0; // Current tab is set to be the first tab (0)
showTab(currentTab); // Display the current tab

function showTab(n) {
    // This function will display the specified tab of the form...
    var x = document.getElementsByClassName("tab");
    x[n].style.display = "block";
    //... and fix the Previous/Next buttons:
    if (n == 0) {
        document.getElementById("prevBtn").style.display = "none";
    } else {
        document.getElementById("prevBtn").style.display = "inline";
    }
    if (n == (x.length - 1)) {
        document.getElementById("nextBtn").innerHTML = "Buy crypto";
    } else {
        document.getElementById("nextBtn").innerHTML = "Next";

    }
    //... and run a function that will display the correct step indicator:
    fixStepIndicator(n)
    if (n == 1) {
        // Put the currency, which was selected in the first step
        $('#bank_currency').empty();
        let currency = $('#fiat_currency').find(':selected').text().trim();
        $('#bank_currency').append('<option value="' + currency + '" class="">' + currency + '</option>');

        // AJax for getting the existing bank accounts
        $.ajaxSetup({
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            }
        });

        $.ajax({
            type: "POST",
            url: API + 'get-limits',
            data: {
                c_profile_id: $('.c_profile_id').val(),
                toCurrency: $('.coin').val(),
                amount: $('.amount').val(),
                fromCurrency: $('#fiat_currency').find(':selected').text().trim(),
                wireType: $('#wireType').val(),
                operationType: $('#operationType').val(),
            },
            success: function (response) {
                let count = $('.blockchain-fee').data('count');
                $('.available-limit').text(response.availableAmountForMonth);
                $('.topup-fee').text(((response.commissions &&  response.commissions.percent_commission != null) ? response.commissions.percent_commission : 0) + '%');
                $('.blockchain-fee').text((response.blockChainFee ?? 0) * count + ' ' + (response.blockChainFeeCurrency ?? ''));
                $('.transaction-limit').text(response.transactionLimit ?? '-');
                if(response.exchangeCommissions) {
                    $('.exchange-fee').text((response.exchangeCommissions.percent_commission ?? 0) + '%');
                }


                if (response.message == 'failed') {
                    var string = window.location.href;
                    var result = string.split("/").pop();
                    window.location.href = '/cabinet/wallets/' + result + '?message=1';
                } else {
                    var message = [];
                    var amount = $('.amount').val();
                    var amountInFiat = $('.expected-amount').val();

                    if (parseFloat(amountInFiat) < parseFloat(response.limits.transaction_amount_min)
                        || (response.limits.transaction_count_daily_max && response.transactionsPerDay >= response.limits.transaction_count_daily_max - 1)
                        || (response.limits.transaction_count_monthly_max && response.transactionsPerMonth >= response.limits.transaction_count_monthly_max - 1)) {
                        nextPrev(-2);

                        if (amountInFiat < parseFloat(response.limits.transaction_amount_min)) {
                            message.push("Minimum amount is " + response.limits.transaction_amount_min + ' ' + response.commissions.currency);
                        } else if (response.limits.transaction_count_daily_max && (response.transactionsPerDay >= response.limits.transaction_count_daily_max - 1)) {
                            message.push("Daily limit is over");
                        } else if (response.limits.transaction_count_monthly_max && response.transactionsPerMonth >= response.limits.transaction_count_monthly_max - 1) {
                            message.push("Monthly limit is over");
                        }
                    }

                    $('.limit-fail-text').text(message);

                    if (response.limits.monthly_amount_max < response.transactionsAmountPerMonth || response.limits.monthly_amount_max < amountInFiat) {
                        $('.complianceLevel').removeClass('d-none').addClass('d-block');
                    }
                    $('.available-limit').text(response.availableAmountForMonth);

                }
            }
        });
    }


}

let createButton = false;

$(document).ready(function () {
    $('#nextBtn').on('click', function (e) {
        e.preventDefault();
        if ($(this).hasClass('createButton')) {
            $('#nextBtn').attr('disabled', true);
        }
        nextPrev(1);
    })
});

function nextPrev(n) {
    // This function will figure out which tab to display

    var x = document.getElementsByClassName("tab");
    // Exit the function if any field in the current tab is invalid:


    if (n == 1 && !validateForm()) return false;

    // Hide the current tab:
    // Increase or decrease the current tab by 1:
    currentTab = currentTab + n;
    // if you have reached the end of the form...
    if (currentTab == x.length) {
        // ... the form gets submitted:
        if (!$('select[name=currency]').val()) {
            return false;
        }

        $('#fiat_form').trigger('submit');
        createButton = true;
        return false;
    }
    // Otherwise, display the correct tab:
    showTab(currentTab);
    if (currentTab == 1) {
        $('#nextBtn').addClass('createButton');
    }
}

function validateForm() {
    // This function deals with validation of the form fields
    var x, y, i, valid = true;
    x = document.getElementsByClassName("tab");
    y = x[currentTab].getElementsByTagName("input");
    r = x[currentTab].getElementsByTagName("select");
    // A loop that checks every input field in the current tab:
    for (i = 0; i < y.length; i++) {
        // If a field is empty...
        if (y[i].value == "") {
            // add an "invalid" class to the field:
            y[i].className += " invalid";
            // and set the current valid status to false
            valid = false;
        }
    }

    for (i = 0; i < r.length; i++) {
        // If a field is empty...
        if (r[i].value == "") {
            // add an "invalid" class to the field:
            r[i].className += " invalid";
            // and set the current valid status to false
            valid = false;
        } else {
            r[i].classList.remove("invalid");
        }
    }

    // If the valid status is true, mark the step as finished and valid:
    if (valid) {
        document.getElementsByClassName("step")[currentTab].className += " finish";
    }
    console.log('validate', valid)
    return valid; // return the valid status
}

function fixStepIndicator(n) {
    // This function removes the "active" class of all steps...
    var i, x = document.getElementsByClassName("step");
    for (i = 0; i < x.length; i++) {
        x[i].className = x[i].className.replace(" active", "");
    }
    //... and adds the "active" class on the current step:
    x[n].className += " active";
}

function removeError(selector) {
    $('.' + selector).removeClass('invalid');
    $(`.${selector}-error`).remove()
    $('.no-providers-text').addClass('d-none');
}

function showRefNum() {
    $('.reference-number').removeClass('d-none').addClass('d-block')
}
