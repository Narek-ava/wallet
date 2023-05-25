$(document).ready(function () {
    if ($('.payment-type-swift').hasClass('disabled')) {
        $('.payment-type-swift').parent().attr("title", "Переводы в SWIFT в данный момент не доступны.");
    }
    if ($('.payment-type-sepa').hasClass('disabled')) {
        $('.payment-type-sepa').parent().attr("title", "Переводы в SEPA в данный момент не доступны.");
    }

    $('[data-toggle="tooltip"]').tooltip();
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
        let currency = $('.currency').val();
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
                fromCurrency: $('.currency').val(),
                wireType: $('#wireType').val(),
                operationType: $('#operationType').val(),
            },
            success: function (response) {
                if (response.isCardProviderStatusSuspended) {
                    $('#nextBtn').addClass('disabled')
                    $('.provider-status-suspended').removeClass('d-none');
                    $('.provider-status-suspended').addClass('d-block');
                }

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
        if (!$('#agreeTerms').prop('checked')) {
            $('.terms-fail-message').removeClass('d-none');
            $('.terms-fail-message').addClass('d-block');
            currentTab = currentTab - n;
            return false;
        }

        if (!$('select[name=currency]').val()) {
            return false;
        }

        $('#card_form').trigger('submit');
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
    let from = $('.currency').val();
    let to = $('.coin').val();
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
            data: { from, to, amount: 1 },
            success: function (data) {
                $('#summery').prop('hidden', false)
                amountTo.val((amount / data).toFixed(8));
                expectedRate.text(data + ' ' + from);
                amountTo.removeClass("invalid");
            },
        })
    } else {
        $('#summery').prop('hidden', true)
    }

}


function displayBankTemplate() {
    $('.country').empty();
    $('#bank_currency').empty();

    if ($('#bank_template').val() == 0) {
        $('.country').val('');
        $('#bank_currency').val('');
        $('.iban').val('');
        $('.swift').val('');
        $('.account-holder').val('');
        $('.account-number').val('');
        $('.bank-name').val('');
        $('.bank-address').val('');
        $('.template-name').val('Name');
        $('.bank-template-name').removeClass('d-none');

        $.ajaxSetup({
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            }
        });

        $.ajax({
            type: "POST",
            url: API + 'get-available-countries',
            data: {
                "currency": $('.currency').val(),
                "accountType": $("input[type='radio'][name='type']:checked").val(),
            },
            success: function (response) {
                let object = response.availableCountries;
                for (const property in object) {
                    $('.country').append('<option value="' + property + '" class="">' + object[property] + '</option>');
                }

                for (let i = 0; i < response.currencies.length; i++) {
                    $('#bank_currency').append('<option value="' + response.currencies[i] + '" class="">' + response.currencies[i] + '</option>');
                }
            }
        });
    } else {
        $('#bank_currency').empty();

        $.ajaxSetup({
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            }
        });

        $.ajax({
            type: "POST",
            url: API + 'get-bank-template',
            data: {
                "account_id": $('#bank_template').val(),
            },
            success: function (response) {
                $('.country').append('<option selected value="' + response.account.country + '">' + response.country + '</option>');
                $('#bank_currency').append('<option selected value="' + response.account.currency + '">' + response.account.currency + '</option>');
                $('.iban').val(response.wireAccountDetail.iban ?? '-');
                $('.swift').val(response.wireAccountDetail.swift ?? '-');
                $('.account-holder').val(response.wireAccountDetail.account_beneficiary ?? '-');
                $('.account-number').val(response.wireAccountDetail.account_number ?? '-');
                $('.bank-name').val(response.wireAccountDetail.bank_name ?? '-');
                $('.bank-address').val(response.wireAccountDetail.bank_address ?? '-');
                $('.bank-template-name').addClass('d-none');
            }
        });

    }
}
