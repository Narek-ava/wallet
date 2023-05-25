$(document).ready(function () {
    $.ajaxSetup({
        headers: {
            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
        }
    });


    $('.amount').on('change', function () {
        let amountCrypto = $('.amount').val();
        let from = $('.coin').val();
        let to = $('.currency').find(':selected').text().trim();
        getExpectedAmountToFiatOperation(from, to, amountCrypto)
    })

    $('.amountFiat').on('change', function () {
        let amountFiat = $('.amountFiat').val();
        let from = $('.currency').find(':selected').text().trim();
        let to = $('.coin').val();
        getExpectedAmountToFiatOperation(from, to, amountFiat, false)
    })

    $('.currency').on('change', function () {
        let amount = $('.amount').val();
        let cryptoToFiat = true;
        if (!amount) {
            amount = $('.amountFiat').val();
            cryptoToFiat = false;
        }
        let from = $('.currency').find(':selected').text().trim();
        let to = $('.coin').val();
        getExpectedAmountToFiatOperation(from, to, amount, cryptoToFiat);
    })


});

function getExpectedAmountToFiatOperation(fromCurrency, toCurrency, changeAmount, cryptoToFiat = true) {
    if (fromCurrency && toCurrency && changeAmount) {
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

var currentTab = 0; // Current tab is set to be the first tab (0)
showTab(currentTab); // Display the current tab

function showTab(n) {
    // This function will display the specified tab of the form...
    let x = document.getElementsByClassName("tab");
    x[n].style.display = "block";
    //... and fix the Previous/Next buttons:
    if (n == 0) {
        document.getElementById("prevBtn").style.display = "none";
    } else {
        document.getElementById("prevBtn").style.display = "inline";
    }
    if (n == (x.length - 1)) {
        getLimits();
        document.getElementById("nextBtn").innerHTML = "Create";
    } else {
        document.getElementById("nextBtn").innerHTML = "Next";

    }
    //... and run a function that will display the correct step indicator:
    fixStepIndicator(n)
}

let createButton = false;

function nextPrev(n) {
    // This function will figure out which tab to display
    let x = document.getElementsByClassName("tab");
    // Exit the function if any field in the current tab is invalid:
    if (n == 1 && !validateForm()) return false;
    // Hide the current tab:
    x[currentTab].style.display = "none";
    // Increase or decrease the current tab by 1:
    currentTab = currentTab + n;
    // if you have reached the end of the form...
    if (currentTab >= x.length) {
        // ... the form gets submitted:


        $('#withdraw_to_fiat_form').trigger('submit');
        //document.getElementById("withdraw_wire_form").submit();
        createButton = true;
        return false;
    }
    // Otherwise, display the correct tab:
    showTab(currentTab);
    if (currentTab == 3) {
        $('#nextBtn').addClass('createButton');
    }

    if ($('.validationError').hasClass('d-block')) {
        $('.validationError').removeClass('d-block');
        $('.validationError').addClass('d-none')
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

function removeError($class) {
    $('.' + $class).removeClass('invalid');
    $('.no-providers-text').addClass('d-none');
}

function showRefNum() {
    $('.reference-number').removeClass('d-none').addClass('d-block')
}

function getExpectedAmount() {
    let amountTo = $('.expected-amount');
    let amount = $('.amount').val();
    let from = $('.coin').val();
    let to = $('.currency').val();
    let expectedRate = $('.expected-rate');

    if (from && to && amount) {
        $.ajaxSetup({
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            }
        });

        $.ajax({
            url: API + 'get-rate-crypto-fiat',
            type: 'post',
            data: { from, to, amount },
            success: function (data) {
                amountTo.val(data);
                expectedRate.text(Math.round(data/amount) + ' ' + to);
                amountTo.removeClass("invalid");
            }
        })
    }

}

function getLimits() {
    $.ajax({
        type: "POST",
        url: 'get-limits',
        data: {
            c_profile_id: $('.c_profile_id').val(),
            currency: $('.currency').find(':selected').text().trim(),
            amount: $('.amount').val(),
        },
        success: function (response) {
            $('.available-limit').text(response.availableAmountForMonth);
            $('.withdraw-fee').text(((response.commissions && response.commissions.percent_commission) ? response.commissions.percent_commission : 0) + '%');

            $('.transaction-limit').text(response.transactionLimit ?? '-');
            // $('.exchange-fee').text((response.exchangeCommissions.percent_commission ?? 0) + '%');


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

